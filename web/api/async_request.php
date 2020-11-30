<?php
include ('../config.php');
$parts=parse_url($redirectUri.'/api/bulkDeviceLoad.php');
$fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 30);
$out = "POST ".$parts['path']." HTTP/1.1\r\n";
$out.= "Host: ".$parts['host']."\r\n";
$out.= "Content-Type: application/json\r\n";
$post_string = json_encode( $_POST['data'] );
$out.= "Content-Length: ".strlen($post_string)."\r\n";
$out.= "Connection: Close\r\n\r\n";
$out.= $post_string;
fwrite($fp, $out);
fclose($fp);
?>
