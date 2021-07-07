<?php
ini_set('display_errors', 'on');
session_start();
DEFINE('MAIN_DIR', __DIR__ . '/..');
$next = null;

include 'include/functions.php';
include 'include/app.php';
$v = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/settings.json'), true)["core"]["version"];
echo $v;