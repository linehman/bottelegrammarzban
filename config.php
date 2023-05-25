<?php
/*
pv  => @gholipour3
channel => @mirzapanel
*/
//-----------------------------database-------------------------------
$dbname = "databasename"; //  نام دیتابیس
$username = "username"; // نام کاربری دیتابیس
$password = 'password'; // رمز عبور دیتابیس
$connect = mysqli_connect("localhost", $username, $password, $dbname);
if ($connect->connect_error) {
    die("اتصال به دیتابیس ناموفق بود: " . $connect->connect_error);
}
mysqli_set_charset($connect, "utf8mb4");
//-----------------------------info-------------------------------

defined('API_KEY') or define('API_KEY', 'توکن ربات');// توکن ربات خود را وارد کنید
$adminnumber =5522424631;// آیدی عددی ادمین
$domainhost = "domain.com/bot";// دامنه  هاست و مسیر سورس
$apinowpayments = "token_api"; // api سایت nowpayments  
$usernamebot = "marzbaninfobot"; //نام کاربری ربات  بدون @
