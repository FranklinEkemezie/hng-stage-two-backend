<?php
 
 /**
  * Sanitises and filters a string
  * @param mixed $input The input string
  * @return mixed The sanitised and filtered string
  */
function sanitiseAndFilterString(string $input): string {
  return filter_var(
    strip_tags( // strip any HTML and PHP tag
      htmlspecialchars( // convert special characters to HTML entities
        trim( //  trim to remove unnecessary whitespace
          $input
        ),
        ENT_QUOTES,
        'UTF-8'
      )
    ),
    FILTER_SANITIZE_STRING,
    FILTER_FLAG_NO_ENCODE_QUOTES
  );
}

/**
 * Sned a POST request using cURL
 * @param string $url URL to request
 * @param array $post POST values to send
 * @param array $options Options for cURL
 * @return string
 */
function curl_post($url, array $post = NULL, array $options = array()) {
  $defaults = array(
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_URL => $url,
    CURLOPT_FRESH_CONNECT => 1,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FORBID_REUSE => 1,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POSTFIELDS => http_build_query($post ?? [])
  );
  
  $ch = curl_init();
  curl_setopt_array($ch, ($options + $defaults));

  if(!$result = curl_exec($ch)) {
    trigger_error(curl_error($ch));
  }

  curl_close($ch);
  return $result;
}

/**
 * Send a GET requst using cURL
 * @param string $url to request
 * @param array $get values to send
 * @param array $options for cURL
 * @return string
 */
 function curl_get($url, array $get = NULL, array $options = array()) {    
  $defaults = array(
    CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get ?? []),
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_TIMEOUT => 10
  );

  $ch = curl_init();

  curl_setopt_array($ch, ($options + $defaults));

  if(!$result = curl_exec($ch)) {
    trigger_error(curl_error($ch));
  }

  curl_close($ch);

  return $result;
 }
 