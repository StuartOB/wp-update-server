<?php
require __DIR__ . '/loader.php';
// $server = new Wpup_UpdateServer();
$server = new SecureUpdateServer();
$server->handleRequest();