<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

if(!isset($_GET['code'])){
    echo 'no code';
    exit();
}

$discord_code = $_GET['code'];


$payload = [
    'code'=>$discord_code,
    'client_id'=>'1050718311203229716',
    'client_secret'=>'pMlNwTDnYd5RcZ7U-9WssJlSC-ayFIDw',
    'grant_type'=>'authorization_code',
    'redirect_uri'=>'http://localhost/xampp/merge/app_m/process-oauth.php',
    'scope'=>'identify%20guilds',
];

print_r($payload);

$payload_string = http_build_query($payload);
$discord_token_url = "https://discordapp.com/api/oauth2/token";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $discord_token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$result = curl_exec($ch);

if(!$result){
    echo curl_error($ch);
}

$result = json_decode($result,true);
$access_token = $result['access_token'];

$discord_users_url = "https://discordapp.com/api/users/@me";
$header = array("Authorization: Bearer $access_token", "Content-Type: application/x-www-form-urlencoded");

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_URL, $discord_users_url);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$result = curl_exec($ch);
if(!$result) {
    echo curl_error($ch);
} else{
    $userData = json_decode($result,true);

    $user_data_from_db = getUserFromDatabase($pdo,$userData['id']);

    if(!$user_data_from_db){
        addUserToDatabase($pdo,$userData['id'],$userData['avatar'],$userData['username']);
    }


    $_SESSION['logged_in'] = true;
    $_SESSION['userData'] = [
    'discord_id'=>$userData['id'],
    'name'=>$userData['username'],
    'avatar'=>$userData['avatar'],
    ];
    /* $GLOBALS["please"] = $userData['id']; */
    header("location: test.php");
    exit();
}
