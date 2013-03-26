<?php

function get_cert_info($url) {
  $s = curl_init();
  curl_setopt($s, CURLOPT_URL, 'https://' . $url);
  curl_setopt($s, CURLOPT_PORT, 443);
  curl_setopt($s, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($s, CURLOPT_VERBOSE, true);
  curl_setopt($s, CURLOPT_CERTINFO, true);
  curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($s);
  $info = curl_getinfo($s);
  return $info;
}

function date_to_cal($time) {
    return date('Ymd', $time);
}

$urls = array();
foreach($_GET as $key => $value) {
  if (stristr(htmlspecialchars_decode($key), 'url') != FALSE) {
    array_push($urls, htmlspecialchars_decode($value));
  }
}

header("Content-Type: text/calendar"); 
header("Content-Disposition: inline; filename=sslcalendar.ics"); 
?>
BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:SSL Cert Expiration
X-WR-TIMEZONE:GMT
<?php foreach($urls as $i) {
  $info = get_cert_info($i);
  if (!(array_key_exists("certinfo", $info) && count($info["certinfo"]) > 0 && array_key_exists("Expire date", $info["certinfo"][0]))) continue;
  $start_date = strtotime(date('Y-m-d', strtotime($info["certinfo"][0]["Expire date"])));
  $end_date = strtotime("+1 day", $start_date);
?>
BEGIN:VEVENT
UID:<?php echo md5($i) . "\r\n"; ?>
SUMMARY:<?php echo $i . " Cert Expires\r\n"; ?>
DESCRIPTION:<?php echo $i . " SSL certificate expires today.\r\n"; ?>
LOCATION:<?php echo 'https://' . $i . "\r\n"; ?>
DTSTART;VALUE=DATE:<?php echo date_to_cal($start_date) . "\r\n"; ?>
DTEND;VALUE=DATE:<?php echo date_to_cal($end_date) . "\r\n"; ?>
X-GOOGLE-CALENDAR-CONTENT-TITLE:<?php echo $i . " Cert Expires\r\n"; ?>
END:VEVENT
<?php } ?>
END:VCALENDAR

