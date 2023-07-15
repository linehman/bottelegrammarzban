<?php
/*
pv  => @gholipour3
channel => @mirzapanel
*/
//-----------------------------database-------------------------------
$dbname = "databasename"; //  نام دیتابیس
$usernamedb = "username"; // نام کاربری دیتابیس
$passworddb = "password"; // رمز عبور دیتابیس
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    die("اتصال به دیتابیس ناموفق بود: " . $connect->connect_error);
}
mysqli_set_charset($connect, "utf8mb4");
//-----------------------------info-------------------------------

$APIKEY = "**TOKEN**"; // توکن ربات خود را وارد کنید
$adminnumber = "5522424631";// آیدی عددی ادمین
$domainhosts = "domain.com/bot";// دامنه  هاست و مسیر سورس
$apinowpayments = "token_api"; // api سایت nowpayments  
$usernamebot = "marzbaninfobot"; //نام کاربری ربات  بدون @
