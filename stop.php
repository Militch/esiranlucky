<?php 
function isStart(){
    return file_exists('start.lock');
}
function stop(){
    unlink("start.lock");
}
stop();
?>

