<php_check_syntax
public static function makeICS($itemId = mull, $validData = []) {
        $icsFile = ICSDIR . 'SCI-event' . $itemId . '.ics';
        $app = Factory::getApplication();
        // PRESERVE DTSTAMP AND UID FROM ORIGINAL FILE
        if(file_exists( $icsFile )){
            $fh = fopen( $icsFile, "r") or die("Unable to read original ics file!");
            $oldContent = fread($fh,filesize( $icsFile ));
            fclose($fh);
            preg_match('/DTSTAMP:([0-9T]+)\r\n/', $oldContent, $matches);
            $dtStamp = $matches[1];
            preg_match('/UID:([A-Za-z0-9\-]+)\r\n/', $oldContent, $matches);
            $uid = $matches[1];
        }
        // EVENT HEADER
        $icsContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//www.sci.utah.edu//SCI Events Calendar 4.0//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\nBEGIN:VEVENT\r\n";
        // DTSTAMP
        $dateObj = date_create();
        if(isset($dtStamp)){ // Use original datestamp
            $icsContent .= "DTSTAMP:" . $dtStamp . "\r\n";
        } else { // Datestamp today
            $dtStamp = date_format( $dateObj, "Ymd") . 'T' . date_format( $dateObj, "Hi") . '00';
            $icsContent .= "DTSTAMP:" . $dtStamp . "\r\n";
        }
        
        // ADD EVENT CONTENT HERE
        $summary = EventsHelper::getTitle($validData['type'], $validData['searchgroups']) . ": " . $validData['presenter_name'] . " - " . $validData['title'];
        $summary = preg_replace( array( '/,/', '/;/', '/[\r\n]/' ), array( '\,', '\;', '\n' ), $summary );
        $icsContent .= "SUMMARY:" . wordwrap( $summary, 67, "\r\n\t", TRUE ) . "\r\n";
        // START DATE & TIME
        $dateObj = date_create( $validData['datetime'] );
        $dtStart = date_format( $dateObj, "Ymd") . 'T' . date_format( $dateObj, "Hi") . '00';
        $icsContent .= "DTSTART:" . $dtStart . "\r\n";
        // DURATION
        $duration = $validData['duration'];
        if(str_contains( $duration, 'day')){
          $duration = 'P' . str_replace( 'day', 'D', $duration);
        } else {
          $duration = str_replace( 'hr', 'H', $duration);
          $duration = str_replace( 'min', 'M', $duration);
          $duration = str_replace( ' ', '', $duration);
          $duration = 'PT' . $duration;
        }
        $icsContent .= "DURATION:" . $duration . "\r\n";
        // LOCATION
        $location = $validData['location'];
        $location = str_replace( "<br />", " ", $location );
        // Add WEB address info for all but these event types.
        if(preg_match('/(travel|external|deadline|conference|workshop)/i', $validData['type']) == false) { 
          $location = $location . " 72 Central Campus Dr, Salt Lake City, UT 84112";
        }
        $location = strip_tags( $location );
        $location = trim( $location, "\n\r\t\v\x00" );
        $location = preg_replace( array( '/,/', '/;/', '/[\r\n]/' ), array( '\,', '\;', '\n' ), $location );
        $icsContent .= "LOCATION:" . wordwrap($location, 66, "\r\n\t", TRUE) . "\r\n";
        // DESCRIPTION
        $description = $validData['description'];
        if($validData['additional_info'] != ''){
          $description .= "\n\nAdditional Info: " . $validData['additional_info'];
        }
        $description = preg_replace("/(<br \/>|<br>|<p>)/i", "\r\n", $description);
        $description = preg_replace("/[\r\n]+/", "\r\n\t", $description );
        $description = strip_tags( $description );
        $description = trim( $description, "\n\r\t\v\x00" );
        $description = preg_replace( array( '/,/', '/;/', '/[\r\n]/' ), array( '\,', '\;', '\n' ), $description );
        $icsContent .= "DESCRIPTION:" . wordwrap($description, 63, "\r\n\t", TRUE) . "\r\n";
        // UID
        if(!isset($uid)){
            $timestamp = $dateObj->getTimestamp();
            $randStr = bin2hex(random_bytes(8));
            $uid = $timestamp . "-" . $randStr;
        }
        $icsContent .= "UID:" . $uid . "\r\n";
        // EVENT FOOTER
        $icsContent .= "END:VEVENT\r\nEND:VCALENDAR\r\n";
        // DELETE OLD VERSION OF FILE IF EXISTS
        if(file_exists( $icsFile )){
          unlink( $icsFile );
        }
        // SAVE ICS FILE
        $fh = fopen( $icsFile, "w") or die("Unable to write ICS file!");
        fwrite($fh, $icsContent);
        fclose($fh);
        return true;
    }