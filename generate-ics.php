<?php

function generateICS($event_name, $event_start, $event_end, $event_description, $ics_file_name) {
    $ics_content = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "PRODID:-//Your Organization//NONSGML Event//EN\r\n";
    $ics_content .= "BEGIN:VEVENT\r\n";
    $ics_content .= "SUMMARY:" . escapeString($event_name) . "\r\n";
    $ics_content .= "DTSTART:" . dateToCal($event_start) . "\r\n";
    $ics_content .= "DTEND:" . dateToCal($event_end) . "\r\n";
    $ics_content .= "DESCRIPTION:" . escapeString($event_description) . "\r\n";
    $ics_content .= "UID:" . uniqid() . "@yourdomain.com\r\n";
    $ics_content .= "STATUS:CONFIRMED\r\n";
    $ics_content .= "END:VEVENT\r\n";
    $ics_content .= "END:VCALENDAR\r\n";

    file_put_contents("/path_to_ics_files/$ics_file_name", $ics_content);
}

// format dates for ICS
function dateToCal($timestamp) {
    return date('Ymd\THis', strtotime($timestamp));
}

//  escape strings for ICS
function escapeString($string) {
    return preg_replace('/([\,;])/','\\\$1', $string);
}

// loop query result to generate ICS files
while($row = mysqli_fetch_assoc($qy_result)) {
    $event_id = $row['id'];
    $event_name = $row['name'];
    $event_start = $row['datetime'];
    $event_end = $row['end_time'];
    $event_description = $row['description'];
    
    // make unique ICS file name for each event
    $ics_file_name = "event_$event_id.ics";

    
    generateICS($event_name, $event_start, $event_end, $event_description, $ics_file_name);
}?>
