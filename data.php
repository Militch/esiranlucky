<?php
require 'vendor/autoload.php';
use Medoo\Medoo;
function hide_phone_number($phone){
    return substr_replace($phone,'****',3,4);
}
error_reporting(E_ALL ^ E_NOTICE); 
$start = $_GET['start'];
$end = $_GET['end'];
$start = isset($start)?$start:'0';
$end = isset($end)?$end:'0';

$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'esiranlucky',
    'server' => '188.188.188.254',
    'username' => 'root',
    'password' => 'ruixiao123'
]);

$data = $database->select("esiranlucky_member", [
    'nikename',
    'phone',
    'avatar_url',
    'joined_at',
    'created_at',
    'updated_at'
], [
    "joined_at[>=]" => $start,
    "joined_at[<]" => $end,
],[
    "ORDER" => ["joined_at" => "DESC"],
]);
for ($i=0; $i < count($data); $i++) {
    $data[$i]['phone'] = hide_phone_number($data[$i]['phone']);
}
$out = [
    "start" => $start,
    "end" => $end,
    "data" => $data,
];
header('Content-Type:application/json; charset=utf-8');
exit(json_encode($out,JSON_UNESCAPED_UNICODE));
?>