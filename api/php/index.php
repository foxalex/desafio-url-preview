<?php

require_once('UrlPreview.php');

// Add Cors
cors();

// Header 
header('Content-Type: application/json; charset=UTF-8');

/** Testing Only POST Method */

// Only allow POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
  //throw new Exception('Only POST requests are allowed');
  handle_error('Only POST requests are allowed', 400);
}

// Make sure Content-Type is application/json 
$content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if (stripos($content_type, 'application/json') === false) {
  //throw new Exception('Content-Type must be application/json');
  handle_error('Content-Type must be application/json', 400);
}

// Read the input stream
$body = file_get_contents("php://input");
 
// Decode the JSON object
$object = json_decode($body, true);

// Url Preview Handle
$url = $object['url'];

$up = new UrlPreview();
$res = $up->get($object['url']);

die($res);

function handle_error($msg, $code = 500) {
  
  http_response_code($code);
  
  die(json_encode(array(
    'error' => true,
    'error_message' => $msg
  )));
  
}

// Function Cors
// https://stackoverflow.com/questions/8719276/cors-with-php-headers
function cors() {

    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            //header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
            header("Access-Control-Allow-Methods: POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

