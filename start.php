<?php 
function isStart(){
    return file_exists('start.lock');
}
function start(){
    $f = fopen("start.lock", "w") or false;
    if ($f){
        fclose($f);
    }
}
start();
?>
