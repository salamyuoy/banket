<?php
$host = 'MySQL-8.4';
$user = 'root';
$pass = '';
$dbname = 'banket';

$con = mysqli_connect($host, $user, $pass, $dbname);

if (!$con) {
    die('Ошибка подключения к базе данных: ' . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');
?>