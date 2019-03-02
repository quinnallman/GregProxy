<?php

$uri_prefix = '/proxy/';
$destination = substr($_SERVER['REQUEST_URI'], strlen($uri_prefix));

$ch = curl_init($destination);

if($_SERVER['REQUEST_METHOD'] === "POST") {
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$user_agent = "GregProxy/1.0";
if(isset($_SERVER['HTTP_USER_AGENT']))
  $user_agent = $_SERVER['HTTP_USER_AGENT'];

curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

// cleanly handle headers as shown here:
// https://stackoverflow.com/a/41135574
$response_headers = [];
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) use(&$response_headers) {
  $len = strlen($header);
  $header = explode(':', $header, 2);
  if(count($header) < 2)
    return $len;

  $name = trim($header[0]);
  $lower_name = strtolower($name);
  if(!array_key_exists($lower_name, $response_headers))
    $response_headers[$lower_name] = [["name" => $name, "value" => trim($header[1])]];
  else
    $response_headers[$lower_name][] = ["name" => $name, "value" => trim($header[1])];

  return $len;
});

$response = curl_exec($ch);
curl_close($ch);

if($response) {
  foreach($response_headers as $header_value) {
    $last_header_value = $header_value[count($header_value) - 1];
    header("${last_header_value["name"]}: ${last_header_value["value"]}");
  }

  echo $response;
}
