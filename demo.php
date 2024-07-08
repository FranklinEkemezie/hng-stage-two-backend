<?php

// echo empty(null) ? "Yes, empty" : "No";

require_once "src/includes/functions.php";


// echo bin2hex(random_bytes(64));
// echo "\n";

// echo str_shuffle(
//   password_hash(
//     random_bytes(64),
//     PASSWORD_BCRYPT
//   )
// );

// echo "\n";


// $res = curl_get("localhost:8000/", ["me" => "you"]);

// var_dump($res);

echo sanitiseAndFilterString("<emeka/>k");

