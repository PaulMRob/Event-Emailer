<?php

protected function generateUID($datetime) {
    
    $dtime = DateTime::createFromFormat("Y-m-d", $datetime);
    $timestamp = $dtime->getTimestamp();
    $randStr = bind2hex(random_bytes(8));
    $uid = $timestamp . $randStr;
    return $uid;
}

?>