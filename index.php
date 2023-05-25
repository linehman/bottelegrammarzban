<?php
/*
    pv  => @gholipour3
    channel => @mirzapanel
    */
global $connect, $keyboard,$get_username_chack, $backuser, $list_marzban_panel_user, $keyboardadmin, $channelkeyboard, $backadmin, $keyboardmarzban, $json_list_marzban_panel, $sendmessageuser, $textbot, $json_list_help, $rollkey, $confrimrolls, $keyboardhelpadmin, $request_contact, $User_Services, $shopkeyboard,$json_list_product_list, $payment, $admin_section_panel, $setting_panel, $valid_Number, $reports, $step_payment, $Confirm_pay,$json_list_product_list_admin, $change_product, $keyboard_usertest,$domainhost;
date_default_timezone_set('Asia/Tehran');
require_once 'config.php';
require_once 'botapi.php';
require_once 'apipanel.php';
require_once 'jdf.php';
#-----------------------#
$telegram_ip_ranges = [
    ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
    ['lower' => '91.108.4.0',    'upper' => '91.108.7.255']
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok = false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if (!$ok) die("false");
#-----------------------#
function tomantousd(){
    
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.tetherland.com/currencies",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Accept: application/json"
  ],
]);

$response = curl_exec($curl);
curl_close($curl);
    $response = json_decode($response, true);
return $response;
}
$usdprice = tomantousd();
#-----------------------#
$user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$from_id' LIMIT 1"));
$Processing_value =  $user['Processing_value'];
$setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help"));
$datatextbotget = mysqli_query($connect, "SELECT * FROM textbot");
$channels = array();
$channels = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM channels  LIMIT 1"));
$id_admin = mysqli_query($connect, "SELECT * FROM admin");
$admin_ids = [];
while ($row = mysqli_fetch_assoc($id_admin)) {
    $admin_ids[] = $row['id_admin'];
}
$Discouncodesql = mysqli_query($connect, "SELECT * FROM Discount");
$Discouncode = [];
while ($row = mysqli_fetch_assoc($Discouncodesql)) {
    $Discouncode[] = $row['code'];
}
$id_user = mysqli_query($connect, "SELECT * FROM user");
$users_ids = [];
while ($row = mysqli_fetch_assoc($id_user)) {
    $users_ids[] = $row['id'];
}
$loc_marzban = mysqli_query($connect, "SELECT * FROM marzban_panel");
$marzban_list = [];
while ($row = mysqli_fetch_assoc($loc_marzban)) {
    $marzban_list[] = $row['name_panel'];
}
$list_product = mysqli_query($connect, "SELECT * FROM product");
$name_product = [];
while ($row = mysqli_fetch_assoc($list_product)) {
    $name_product[] = $row['name_product'];
}
$datatxtbot = array();
foreach ($datatextbotget as $row) {
    $datatxtbot[] = array(
        'id_text' => $row['id_text'],
        'text' => $row['text']
    );
}
$datatextbot = array(
    'text_usertest' => '',
    'text_Purchased_services' => '',
    'text_support' => '',
    'text_help' => '',
    'text_start' => '',
    'text_bot_off' => '',
    'text_dec_info' => '',
    'text_dec_usertest' => '',
    'text_roll' => '',
    'text_dec_support' => '',
    'text_fq' => '',
    'text_dec_fq' => '',
    'text_account'  => '',
    'text_sell' => '',
    'text_Add_Balance' => '',
    'text_cart_to_cart' => '',
    'text_channel' => '',
    'text_Discount' =>'',
);
foreach ($datatxtbot as $item) {
    if (isset($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}
#---------channel--------------#
$tch = '';
if (isset($channels['link'])) {
    $response = json_decode(file_get_contents('https://api.telegram.org/bot' . API_KEY . "/getChatMember?chat_id=@{$channels['link']}&user_id=$from_id"));
    $tch = $response->result->status;
}
#-----------------------#
$connect->query("INSERT IGNORE INTO user (id , step,limit_usertest,User_Status,number,Balance,pagenumber) VALUES ('$from_id', 'none','{$setting['limit_usertest_all']}','Active','none','0','1')");
if ($user['User_Status'] == "block") {
    $textblock = "
           🚫 شما از طرف مدیریت بلاک شده اید.
            
        ✍️ دلیل مسدودی: {$user['description_blocking']}
            ";
    sendmessage($from_id, $textblock, null);
    return;
}
if(empty($channels['Channel_lock'])){
    $channels['Channel_lock'] = "off";
    $channels['link'] = "تنظیم نشده";
}
if (!in_array($tch, ['member', 'creator', 'administrator']) && $channels['Channel_lock'] == "on" && !in_array($from_id, $admin_ids)) {
    $link_channel = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔗 عضویت در کانال", 'url' => "https://t.me/".$channels['link']],
            ],
        ]
    ]);
    sendmessage($from_id, $datatextbot['text_channel'], $link_channel);
    return;
}
#-----------------------#
if ($setting['roll_Status'] == "✅ تایید قانون روشن است" && $user['roll_Status'] == 0 && $text != "✅ قوانین را می پذیرم" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_roll'], $confrimrolls);
    return;
}
if ($text == "✅ قوانین را می پذیرم"){
    sendmessage($from_id, "✅ قوانین تایید شد از الان می توانید از خدمات ربات استفاده نمایید.", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET roll_Status = ? WHERE id = ?");
    $confrim = true;
    $stmt->bind_param("ss", $confrim, $from_id);
    $stmt->execute();
}

#-----------------------#
if ($setting['Bot_Status'] == "❌ ربات خاموش است" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_bot_off'], null);
    return;
}
#-----------------------#
if ($text == "/start") {
    sendmessage($from_id, $datatextbot['text_start'], $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
if ($text == "🏠 بازگشت به منوی اصلی") {
    $textback = "به صفحه اصلی بازگشتید!";
    sendmessage($from_id, $textback, $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
//_________________________________________________
if($user['step'] == 'get_number'){
    if (empty($user_phone)){
        sendmessage($from_id, "❌ شماره تلفن صحبح نیست شماره تلفن صحبح را ارسال نمایید.", $request_contact);
        return;
    }
    if ($contact_id != $from_id){
        sendmessage($from_id, "⚠️ خطا در ذخیره سازی شماره تلفن، شماره باید حتما برای همین اکانت باشد.",$request_contact );
        return;
    }
    if($setting['iran_number'] == "✅ احرازشماره ایرانی روشن است" && !preg_match("/989[0-9]{9}$/",$user_phone)){
                sendmessage($from_id, "⭕️ شماره موبایل نامعتبر است. فقط شماره های ایرانی مورد قبول می باشد.",$request_contact);
                return;
    }
    sendmessage($from_id, "✅ شماره موبایل شما با موفقیت تایید شد.", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET number = ? WHERE id = ?");
    $stmt->bind_param("ss", $user_phone, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

//________________________________________________________
if ($text == $datatextbot['text_Purchased_services'] || $datain == "backorder") {
$stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
$pages = 1;
$stmt->bind_param("ss", $pages, $from_id);
$stmt->execute();
$page = 1;
$items_per_page = 5;
$start_index = ($page - 1) * $items_per_page;
$result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'  LIMIT $start_index, $items_per_page");
$keyboardlists = [
    'inline_keyboard' => [],
];
while ($row = mysqli_fetch_assoc($result)) {
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => "⭕️".$row['username']."⭕️",
            'callback_data' => "product_" . $row['username']
        ],
    ];
}
$pagination_buttons = [
    [
        'text' => 'بعدی',
        'callback_data' => 'next_page'
    ],
    [
        'text' => 'قبلی ',
        'callback_data' => 'previous_page'
    ]
];
$keyboardlists['inline_keyboard'][] = $pagination_buttons;
$keyboard_json = json_encode($keyboardlists);
sendmessage($from_id, "🛍 اشتراک های خریداری شده توسط شما

⚜️برای مشاهده اطلاعات روی نام کاربری کلیک کنید", $keyboard_json);
}
if ($datain == 'next_page') {
$stmt = $connect->prepare("SELECT COUNT(id_user) FROM invoice WHERE id_user = '$from_id'");
$stmt->execute();
$result = $stmt->get_result();
$numpage = $result->fetch_array(MYSQLI_NUM);
$page = $user['pagenumber'];
$items_per_page  = 5;
$sum = $user['pagenumber'] * $items_per_page ;
if($sum > $numpage[0]){
$next_page = 1;
}
else{
$next_page = $page + 1;
}
$start_index = ($next_page - 1) * $items_per_page;
$result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'  LIMIT $start_index, $items_per_page");
$keyboardlists = [
    'inline_keyboard' => [],
];
while ($row = mysqli_fetch_assoc($result)) {
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => "⭕️".$row['username']."⭕️",
            'callback_data' => "product_" . $row['username']
        ],
    ];
}
$pagination_buttons = [
    [
        'text' => 'بعدی',
        'callback_data' => 'next_page'
    ],
    [
        'text' => 'قبلی ',
        'callback_data' => 'previous_page'
    ]
];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    $stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
    $stmt->bind_param("ss", $next_page, $from_id);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, $keyboard_json);
}
elseif ($datain == 'previous_page') {
$page = $user['pagenumber'];
$items_per_page  = 5;
if($user['pagenumber'] <= 1){
$next_page = 1;
}
else{
$next_page = $page - 1;
}
$start_index = ($next_page - 1) * $items_per_page;
$result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'  LIMIT $start_index, $items_per_page");
$keyboardlists = [
    'inline_keyboard' => [],
];
while ($row = mysqli_fetch_assoc($result)) {
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => "⭕️".$row['username']."⭕️",
            'callback_data' => "product_" . $row['username']
        ],
    ];
}
$pagination_buttons = [
    [
        'text' => 'بعدی',
        'callback_data' => 'next_page'
    ],
    [
        'text' => 'قبلی ',
        'callback_data' => 'previous_page'
    ]
];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    $stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
    $stmt->bind_param("ss", $next_page, $from_id);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, $keyboard_json);
}
if(preg_match('/product_(\w+)/',$datain, $dataget)) {
    $username= $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if ($data_useer['detail'] == "User not found") {
        sendmessage($from_id, "❌خطایی رخ داده است", $keyboard);
        return;
    }
    #-------------username----------------#
    $usernames = $data_useer['username'];
    #-------------status----------------#
    $status = $data_useer['status'];
    $status_var = [
        'active' => '✅ فعال',
        'limited' => '🚫 پایان حجم',
        'disabled' => '❌ غیرفعال',
        'expired' => '🔚 پایان زمان سرویس'
    ][$status];
    #--------------expire---------------#
    $expirationDate = $data_useer['expire'] ? jdate('Y/m/d', $data_useer['expire']) : "نامحدود";
    #-------------data_limit----------------#
    $LastTraffic = $data_useer['data_limit'] ? formatBytes($data_useer['data_limit']) : "نامحدود";
    #---------------RemainingVolume--------------#
    $output =  $data_useer['data_limit'] - $data_useer['used_traffic'];
    $RemainingVolume = $data_useer['data_limit'] ? formatBytes($output) : "نامحدود";
    #---------------used_traffic--------------#
    $usedTrafficGb = $data_useer['used_traffic'] ? formatBytes($data_useer['used_traffic']) : "مصرف نشده";
    #--------------day---------------#
    $timeDiff = $data_useer['expire'] - time();
    $day = $data_useer['expire'] ? floor($timeDiff / 86400) + 1 . " روز" : "نامحدود";
    #-----------------------------#


    $keyboardinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '👤نام کاربری', 'callback_data' => 'username'],
            ],
            [
                ['text' => $data_useer['username'], 'callback_data' => "username"],
            ],
            [
                ['text' => $status_var, 'callback_data' => 'status_var'],
                ['text' => 'وضعیت:', 'callback_data' => 'status_var'],
            ], [
                ['text' => $expirationDate, 'callback_data' => 'expirationDate'],
                ['text' => 'زمان پایان:', 'callback_data' => 'expirationDate'],
            ], [], [
                ['text' => $day, 'callback_data' => 'روز'],
                ['text' => 'زمان باقی مانده سرویس:', 'callback_data' => 'day'],
            ], [
                ['text' => $LastTraffic, 'callback_data' => 'LastTraffic'],
                ['text' => 'حجم کل سرویس:', 'callback_data' => 'LastTraffic'],
            ], [
                ['text' => $usedTrafficGb, 'callback_data' => 'expirationDate'],
                ['text' => 'حجم مصرف شده سرویس:', 'callback_data' => 'expirationDate'],
            ], [
                ['text' => $RemainingVolume, 'callback_data' => 'RemainingVolume'],
                ['text' => 'حجم باقی مانده سرویس:', 'callback_data' => 'RemainingVolume'],
            ],
             [
                ['text' => "🔗  دریافت لینک اشتراک", 'callback_data' => 'subscriptionurl_'.$usernames],
            ],
            [
                ['text' => '🏠 بازگشت به لیست سرویس ها', 'callback_data' => 'backorder'],
            ]
        ]
    ]);
        Editmessagetext($from_id, $message_id, "اطلاعات سرویس شما", $keyboardinfo);

}
if(preg_match('/subscriptionurl_(\w+)/',$datain, $dataget)) {
                sendmessage($from_id, $textsub, null);

    $username= $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $subscriptionurl = $data_useer['subscription_url'];
    $textsub = "
🔗 لینک اشتراک شما : 

```$subscriptionurl```";
            sendmessage($from_id, $textsub, null);
}
//________________________________________________________
if ($text == $datatextbot['text_usertest']) {
    if ($user['limit_usertest'] == 0) {
        sendmessage($from_id, "⚠️ محدودیت ساخت اشتراک تست شما به پایان رسید.", $keyboard);
        return;
    }
    sendmessage($from_id, "🌏 موقعیت سرویس تست را انتخاب نمایید.", $list_marzban_panel_user);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'createusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "createusertest") {
    $randomString = bin2hex(random_bytes(2));
    $username_ac = $randomString."_".$from_id;
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$text'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $Allowedusername = getuser($username_ac, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if(isset($Allowedusername['username'])){
        $random_number = rand(1000000, 9999999);
        $username_ac = $username_ac . "_". $random_number;
    }
    $date = strtotime("+" . $setting['time_usertest'] . "hours");
    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
    $expire = $timestamp;
    $data_limit = $setting['val_usertest'] * 1000000;
    $config_test = adduser($username_ac, $expire, $data_limit, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $data_test = json_decode($config_test, true);
    $output_config_link = $data_test['subscription_url'] ?? 'خطا';
        $usertestinfo = json_encode([
        'inline_keyboard' => [
                        [
                ['text' => $setting['time_usertest']." ساعت", 'callback_data' => "Service_time"],
                ['text' => "⏳ زمان اشتراک", 'callback_data' => "Service_time"],
            ],
                                    [
                ['text' => $setting['val_usertest']. " مگابایت", 'callback_data' => "Volume_constraint"],
                ['text' => "🌐 حجم سرویس", 'callback_data' => "Volume_constraint"],
            ]
        ]
    ]);
    $textcreatuser = "🔑 اشتراک شما با موفقیت ساخته شد.

👤 نام کاربری شما :
```$username_ac```

لینک اشتراک شما:
    ```%s```";
    $textcreatuser = sprintf($textcreatuser, $output_config_link);
    sendmessage($from_id, $textcreatuser, $usertestinfo);
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب نمایید", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $limit_usertest = $user['limit_usertest'] - 1;
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
    $stmt->bind_param("ss", $limit_usertest, $from_id);
    $stmt->execute();
    $count_usertest = $setting['count_usertest'] + 1;
    $stmt = $connect->prepare("UPDATE setting SET count_usertest = ?");
    $stmt->bind_param("s", $count_usertest);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $usertestReport = json_encode([
        'inline_keyboard' => [
                        [
                ['text' => $from_id, 'callback_data' => "iduser"],
                ['text' => "آیدی عددی کاربر", 'callback_data' => "iduser"],
            ],
                                    [
                ['text' => $user['number'], 'callback_data' => "iduser"],
                ['text' => "شماره تلفن کاربر", 'callback_data' => "iduser"],
            ],
                                                [
                ['text' => $Processing_value, 'callback_data' => "namepanel"],
                ['text' => "نام پنل", 'callback_data' => "namepanel"],
            ],
        ]
    ]);
    $text_report = " ⚜️ اکانت تست داده شد
    
⚙️ یک کاربر اکانت  با نام کانفیگ ```$username_ac```   اکانت تست دریافت کرد
    
اطلاعات کاربر 👇👇
⚜️ نام کاربری کاربر: @$username";
    if (strlen($setting['Channel_Report'] )> 0){
        sendmessage($setting['Channel_Report'], $text_report, $usertestReport);
    }
}
//_________________________________________________
if ($text == $datatextbot['text_help']) {
    if ($setting['help_Status'] == "❌ آموزش غیرفعال است"){
        sendmessage($from_id, "کاربر گرامی بخش آموزش درحال حاضر غیرفعال است. 😔", null);
        return;
    }
    sendmessage($from_id, "یکی از گزینه ها را انتخاب نمایید", $json_list_help);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendhelp';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendhelp") {
    $helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help WHERE name_os = '$text'"));
    if (strlen($helpdata['Media_os']) != 0) {
        if ($helpdata['type_Media_os'] == "video") {
            sendvideo($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
        } elseif ($helpdata['type_Media_os'] == "photo")
            sendphoto($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
    } else {
        sendmessage($from_id, $helpdata['Description_os'], $json_list_help);
    }
}

//________________________________________________________
if ($text == $datatextbot['text_support']) {
    sendmessage($from_id, "☎️", $backuser);
    sendmessage($from_id, $datatextbot['text_dec_support'], $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextpm';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == 'gettextpm') {
    sendmessage($from_id, "🚀 پیام شما ارسال شد منتظر پاسخ مدیریت باشید.", $keyboard);
    foreach ($admin_ids as $id_admin) {
        sendmessage($id_admin, "📥 یک پیام از کاربر با شناسه ```$from_id``` دریافت شد برای پاسخ ریپلای بزنید و پیام خود را ارسال کنید.", null);
        forwardMessage($from_id, $message_id, $id_admin);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if($text == $datatextbot['text_fq']){
    sendmessage($from_id,$datatextbot['text_dec_fq'] , null);
}
$dateacc = jdate('Y/m/d');
$timeacc = jdate('h:i:s');
if($text == $datatextbot['text_account']){
    $text_account = "
        👨🏻‍💻 وضعیت حساب کاربری شما:
    
👤 نام شما: $first_name
🕴🏻 شناسه شما: $from_id
💰 موجودی شما: {$user['Balance']} تومان
    
📆 $dateacc → ⏰ $timeacc
        ";
    sendmessage($from_id,$text_account , null);
}
if ($text == $datatextbot['text_sell']) {
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none"){
        sendmessage($from_id, "📞 لطفا شماره موبایل خود را  برای احراز هویت ارسال نمایید", $request_contact);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'get_number';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است" ) return;
    #-----------------------#
    sendmessage($from_id, "🌏 موقعیت سرویس  را انتخاب نمایید.", $list_marzban_panel_user);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_product';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_product"){
    if (!in_array($text , $marzban_list)){
        sendmessage($from_id, "❌ خطا 
    📝 موقعیت سرویس نامعتبر است", null);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "🛒 لوکیشن دریافت شد سرویسی که میخواهید خریداری کنید را انتخاب نمایید.", $json_list_product_list);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'endstepuser';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "endstepuser"){
    if (!in_array($text , $name_product)){
        sendmessage($from_id, "❌ خطا 
    📝 محصول انتخابی وجود ندارد", null);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE name_product = '$text' LIMIT 1"));
    $randomString = bin2hex(random_bytes(2));
    $username_ac = "$randomString$from_id";
        $stmt = $connect->prepare("UPDATE user SET Processing_value_tow = ? WHERE id = ?");
    $stmt->bind_param("ss", $username_ac, $from_id);
    $stmt->execute();
    $textin = "
     📇 پیش فاکتور شما:
👤 نام کاربری: $username_ac
🔐 نام سرویس: {$info_product['name_product']}
📆 مدت اعتبار: {$info_product['Service_time']} روز
💶 قیمت: {$info_product['price_product']} هزار تومان
👥 حجم اکانت: {$info_product['Volume_constraint']} گیگ
      
      💰 سفارش شما آماده پرداخت است.  ";
    sendmessage($from_id, $textin, $payment);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "payment" && $text == "💰 پرداخت و دریافت سرویس"){
        $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE name_product = '{$user['Processing_value_one']}' LIMIT 1"));
        if(empty($info_product['price_product']) || empty($info_product['price_product']))return;
    if ($info_product['price_product'] > $user['Balance']){
        sendmessage($from_id, "🚨 خطایی در هنگام پرداخت رخ داده است.
                
    📝 دلیل خطا: عدم موجودی کافی ابتدا موجودی خود را افزایش دهید سپس مجددا سرویس را خریداری کنید", $keyboard);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }
    $username_ac = $user['Processing_value_tow'];
    $randomString = bin2hex(random_bytes(2));
    $username_ac = "$randomString$from_id";
    $stmt = $connect->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, Service_location, name_product, price_product, Volume, Service_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $from_id, $randomString, $username_ac, $Processing_value, $info_product['name_product'], $info_product['price_product'], $info_product['Volume_constraint'], $info_product['Service_time']);
    $stmt->execute();
    $stmt->close();
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($username_ac, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $random_number = rand(1000000, 9999999);
    $username_ac = isset($get_username_Check['username']) ? $username_ac ."_". $random_number : $username_ac ;
    $date = strtotime("+" . $info_product['Service_time'] . "days");
    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
    $data_limit = $info_product['Volume_constraint'] * pow(1024, 3);
    $config = adduser($username_ac, $timestamp, $data_limit, $Check_token['access_token'],$marzban_list_get['url_panel']);
    $data = json_decode($config, true);
if($setting['sublink'] == "✅ لینک اشتراک فعال است."){
        $output_config_link = $data['subscription_url'] ?? 'خطا';
        $link_config = "            
لینک اشتراک شما:
    ```$output_config_link```";
    }
if($setting['configManual'] == "✅ ارسال کانفیگ بعد خرید فعال است."){
        foreach($data['links'] as $configs){
            $config .= "\n\n".$configs;
        }
        $text_config = "            
کانفیگ های شما:
    ```$config```";
    }
        $Shoppinginfo = json_encode([
        'inline_keyboard' => [
                        [
                ['text' => $info_product['Service_time']." روز", 'callback_data' => "Service_time"],
                ['text' => "⏳ زمان اشتراک", 'callback_data' => "Service_time"],
            ],
                                    [
                ['text' => $info_product['Volume_constraint']. " گیگابایت", 'callback_data' => "Volume_constraint"],
                ['text' => "🌐 حجم سرویس", 'callback_data' => "Volume_constraint"],
            ]
        ]
    ]);
    $textcreatuser = "
👤 نام کاربری شما :
```$username_ac```
🔑 اشتراک شما با موفقیت ساخته شد.

$text_config
$link_config
";
    sendmessage($from_id, $textcreatuser, $Shoppinginfo);
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب نمایید", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_prim= $user['Balance']- $info_product['price_product'];
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    $ShoppingReport = json_encode([
        'inline_keyboard' => [
                        [
                ['text' => $from_id, 'callback_data' => "iduser"],
                ['text' => "آیدی عددی کاربر", 'callback_data' => "iduser"],
            ],
                                    [
                ['text' => $user['number'], 'callback_data' => "iduser"],
                ['text' => "شماره تلفن کاربر", 'callback_data' => "iduser"],
            ],
                                                [
                ['text' => $Processing_value, 'callback_data' => "namepanel"],
                ['text' => "نام پنل", 'callback_data' => "namepanel"],
            ],
        ]
    ]);
    $text_report = " 🛍 خرید جدید
    
⚙️ یک کاربر اکانت  با نام کانفیگ ```$username_ac``` خریداری کرد
    
اطلاعات کاربر 👇👇
⚜️ نام کاربری کاربر: @$username";
    if (strlen($setting['Channel_Report'] )> 0){
        telegram('sendmessage',[
        'chat_id' => $setting['Channel_Report'],
        'text' => $text_report,
        'reply_markup' => $ShoppingReport,
        'parse_mode' => "HTML",
        
        ]);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------text_Add_Balance---------------------#
if($text == $datatextbot['text_Add_Balance']){
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none"){
        sendmessage($from_id, "📞 لطفا شماره موبایل خود را برای احراز هویت ارسال نمایید", $request_contact);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'get_number';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است" ) return;
    sendmessage($from_id, "💸 مبلغ را  به تومان وارد کنید:
        
    ✅ حداکثر مبلغ 10.000.000میلیون تومان می باشد", $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getprice';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "getprice"){
    if (!ctype_digit($text)){
        sendmessage($from_id, "❌ خطا 
    💬 مبلغ باید بدون کاراکتر اضافی و فقط عدد باشد ", null);
        return;
    }
    if(intval($text)>10000000){
        sendmessage($from_id, "❌ خطا 
    💬 مبلغ باید کمتر 10 میلیون تومان باشد",  null);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "💵 روش پرداخت خود را انتخاب نمایید", $step_payment);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_step_payment"){
    if ($text == "💳 کارت به کارت"){
        sendmessage($from_id, $datatextbot['text_cart_to_cart'], $backuser);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'cart_to_cart_user';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($text == "💵 پرداخت nowpayments"){
    $usdprice = round($Processing_value/$usdprice['data']['currencies']['USDT']['price'],2);
        if($usdprice <= 4){
        sendmessage($from_id, "❌ خطا 
کمترین مبلغ برای  پرداخت در این درگاه 4 دلار می باشد.", null);
return;
        }
    $dateacc = date('Y/m/d h:i:s');
    $randomString = bin2hex(random_bytes(5));
    $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status) VALUES (?,?,?,?,?)");
    $payment_Status = "Unpaid";
    $stmt->bind_param("sssss", $from_id ,$randomString, $dateacc,$Processing_value,$payment_Status);
    $stmt->execute();
            $paymentkeyboard = json_encode([
        'inline_keyboard' => [
            [
                    ['text' => "پرداخت", 'url' => "https://$domainhost/payment/nowpayments/nowpayments.php?price=$usdprice&order_description=Add_Balance&order_id=$randomString"],
            ]
        ]
    ]);
    $textnowpayments = "
    💰 برای پرداخت روی دکمه زیر کلیک کنید
    مبلغ پرداختی: $usdprice دلار";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard);
    }
}
elseif($user['step'] =="cart_to_cart_user"){
    if (!$photo){
        sendmessage($from_id, "رسید نامعتبر است رسید باید فقط عکس باشد", null);
        return;
    }
    $dateacc = date('Y/m/d h:i:s');
    $randomString = bin2hex(random_bytes(5));
    $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status) VALUES (?,?,?,?,?)");
    $payment_Status = "Unpaid";
    $stmt->bind_param("sssss", $from_id ,$randomString, $dateacc,$Processing_value,$payment_Status);
    $stmt->execute();
    sendmessage($from_id, "🚀 رسید پرداخت  شما ارسال شد پس از تایید توسط مدیریت مبلغ به کیف پول شما واریز خواهد شد.", $keyboard);
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید پرداخت", 'callback_data' => "Confirm_pay_{$randomString}"],
                ['text' => '❌ رد پرداخت', 'callback_data' => "reject_pay_{$randomString}"],
            ]
        ]
    ]);
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_user = '$from_id' LIMIT 1"));
    $textsendrasid = "
        ⭕️ یک پرداخت جدید انجام شده است .
    
    👤 شناسه کاربر: $from_id
    🛒 کد پیگیری پرداخت: $randomString
    ⚜️ نام کاربری: $username
    💸 مبلغ پرداختی: $Processing_value تومان
    
    توضیحات: $caption
    ✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.
    ";
    foreach ($admin_ids as $id_admin) {
        telegram('sendphoto',[
            'chat_id' => $id_admin,
            'photo'=> $photoid,
            'reply_markup' => $Confirm_pay,
            'caption'=> $textsendrasid,
            'parse_mode' => "HTML",
        ]);
        }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();

}
#----------------Discount------------------#
if($text == $datatextbot['text_Discount']){
    sendmessage($from_id, "💝 برای دریافت موجودی کد هدیه خود را ارسال نمایید", $backuser);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_code_user';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_code_user"){
    if(!in_array($text , $Discouncode)){
            sendmessage($from_id, "❌ کد هدیه یافت نشد", null);
            return;
    }
    $Checkcodesql = mysqli_query($connect, "SELECT * FROM Giftcodeconsumed WHERE id_user = '$from_id'");
$Checkcode = [];
    while ($row = mysqli_fetch_assoc($Checkcodesql)) {
    $Checkcode[] = $row['code'];
}
    if(in_array($text , $Checkcode)){
    sendmessage($from_id, "⭕️ این کد تنها یک بار قابل استفاده است.", $keyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'اhome';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
    }
    $get_codesql = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Discount WHERE code = '$text' LIMIT 1"));
    $balance_user = $user['Balance'] + $get_codesql['price'];
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $balance_user, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'اhome';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $text_balance_code = "کد هدیه با موفقیت ثبت شد و به موجودی شما مبلغ {$get_codesql['price']} تومان اضافه گردید. 🥳";
    sendmessage($from_id, $text_balance_code ,$keyboard);
    $stmt = $connect->prepare("INSERT INTO Giftcodeconsumed (id_user,code) VALUES (?,?)");
    $stmt->bind_param("ss", $from_id ,$text);
    $stmt->execute();
}

#----------------admin------------------#
if (!in_array($from_id, $admin_ids)) return;
if ($text == "panel" || $text == "/panel" || $text == "پنل مدیریت" || $text == "ادمین" ) {
    sendmessage($from_id, "به پنل ادمین خوش آمدید", $keyboardadmin);
}
if ($text == "🏠 بازگشت به منوی مدیریت") {
    sendmessage($from_id, "به پنل ادمین بازگشتید! ", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
if ($text == "🔑 روشن / خاموش کردن قفل کانال") {
    if ($channels['Channel_lock'] == "off") {
        sendmessage($from_id, "عضویت اجباری روشن گردید", $channelkeyboard);
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'on';
        $stmt->bind_param("s", $Channel_lock);
        $stmt->execute();
    } else {
        sendmessage($from_id, "عضویت اجباری خاموش گردید", $channelkeyboard);
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'off';
        $stmt->bind_param("s", $Channel_lock);
        $stmt->execute();
    }
}
if ($text == "📣 تنظیم کانال جوین اجباری") {
    $text_channel = "
برای تنظیم کانال عضویت اجباری لطفا آیدی کانال خود را بدون @ وارد نمایید.
            
کانال فعلی شما: @" . $channels['link'];
    sendmessage($from_id, $text_channel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addchannel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "addchannel") {
    $text_set_channel = "
🔰 کانال با موفقیت تنظیم گردید.
برای روشن کردن عضویت اجباری از منوی ادمین دکمه 📣 تنظیم کانال جوین اجباری را بزنید";
    sendmessage($from_id, $text_set_channel, $channelkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("SELECT COUNT(link) FROM channels");
    $stmt->execute();
    $result = $stmt->get_result();
    $channels_ch = $result->fetch_array(MYSQLI_NUM);
    if ($channels_ch[0] == 0) {
        $stmt = $connect->prepare("INSERT INTO channels (link,Channel_lock) VALUES (?,?)");
        $Channel_lock = 'off';
        $stmt->bind_param("ss", $text, $Channel_lock);
        $stmt->execute();
    } else {
        $stmt = $connect->prepare("UPDATE channels SET link = ?");
        $stmt->bind_param("s", $text);
        $stmt->execute();
    }

}
if ($text == "👨‍💻 اضافه کردن ادمین") {
    sendmessage($from_id, "🌟آیدی عددی ادمین جدید را ارسال نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "addadmin") {
    sendmessage($from_id, "🥳 ادمین با موفقیت اضافه گردید", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("INSERT INTO admin (id_admin) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "❌ حذف ادمین") {
    sendmessage($from_id, "🛑 آیدی عددی ادمین را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'deleteadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "deleteadmin") {
    if (!is_numeric($text) || !in_array($text, $admin_ids)) return;
    sendmessage($from_id, "✅ ادمین با موفقیت حذف گردید.", $keyboardadmin);
    $stmt = $connect->prepare("DELETE FROM admin WHERE id_admin = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای کاربر") {
    $text_add_user_admin = "
            ⚜️ آیدی عددی کاربر را ارسال کنید 
        توضیحات: در این بخش میتوانید محدودیت ساخت اکانت تست را برای کاربر تغییر دهید. بطور پیشفرض محدودیت ساخت عدد 1 است.
            ";
    sendmessage($from_id, $text_add_user_admin, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_limit_usertest_foruser';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "add_limit_usertest_foruser") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    sendmessage($from_id, "آیدی عددی دریافت شد لطفا تعداد ساخت اکانت تست را ارسال کنید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "get_number_limit") {
    sendmessage($from_id, "محدودیت برای کاربر تنظیم گردید.", $keyboard_usertest);
    $id_user_set = $text;
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای همه") {
    sendmessage($from_id, "تعداد ساخت اکانت تست را وارد نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'limit_usertest_allusers';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "limit_usertest_allusers") {
    sendmessage($from_id, "محدودیت ساخت اکانت برای تمام کاربران تنظیم شد", $keyboard_usertest);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE setting SET limit_usertest_all = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "📯 تنظیمات کانال") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $channelkeyboard);
}
if ($text == "📊 آمار ربات") {
    $stmt = $connect->prepare("SELECT COUNT(id) FROM user");
    $stmt->execute();
    $result = $stmt->get_result();
    $statistics = $result->fetch_array(MYSQLI_NUM);
    #-------------------------#
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statistics[0], 'callback_data' => 'countusers'],
                ['text' => '👤 تعداد کاربران', 'callback_data' => 'countusers'],
            ],
            [
                ['text' => $setting['count_usertest'], 'callback_data' => 'count_usertest_var'],
                ['text' => '🖥 مجموع اکانت تست', 'callback_data' => 'count_usertest_var'],
            ],
            [
                ['text' => phpversion(), 'callback_data' => 'phpversion'],
                ['text' => '👨‍💻 نسخه php هاست', 'callback_data' => 'phpversion'],
            ],
        ]
    ]);
    sendmessage($from_id, "📈 آمار ربات شما", $keyboardstatistics);
}
if ($text == "🖥 پنل مرزبان") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $keyboardmarzban);
}
if ($text == "🔌 وضعیت پنل") {
    sendmessage($from_id, "پنل خود را انتخاب کنید", $json_list_marzban_panel);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "get_panel") {
    $stmt = $connect->prepare("SELECT * FROM marzban_panel WHERE name_panel = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $marzban_list_get = $stmt->get_result()->fetch_assoc();
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    if (isset($Check_token['access_token'])) {
        $Condition_marzban = "✅ پنل متصل است";
    } elseif ($Check_token['detail'] == "Incorrect username or password") {
        $Condition_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
    } else {
        $Condition_marzban = "امکان اتصال به پنل مرزبان وجود ندارد 😔";
    }
    $System_Stats = Get_System_Stats($marzban_list_get['url_panel'], $Check_token['access_token']);
    $active_users = $System_Stats['users_active'];
    $text_marzban = "
            اطلاعات پنل شما👇:
                 
        🖥 وضعیت اتصال پنل مرزبان: $Condition_marzban
        👤 تعداد کاربران فعال: $active_users
            ";
    sendmessage($from_id, $text_marzban, $keyboardmarzban);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "📜 مشاهده لیست ادمین ها") {
    $List_admin = null;
    $admin_ids = array_filter($admin_ids);
    foreach ($admin_ids as $admin) {
        $List_admin .= "$admin\n";
    }
    $list_admin_text = "👨‍🔧 آیدی عددی ادمین ها: 
        
    $List_admin";
    sendmessage($from_id, $list_admin_text, $admin_section_panel);
}

if ($text == "🖥 اضافه کردن پنل  مرزبان") {
    $text_add_panel = "
            برای اضافه کردن پنل مرزبان به ربات ابتدا یک نام برای پنل خود ارسال کنید
            
         ⚠️ توجه: نام پنل نامی است که  در هنگام انجام عملیات جستجو  نشان داده می شود.
            ";
    sendmessage($from_id, $text_add_panel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_name_panel") {
    $stmt = $connect->prepare("INSERT INTO marzban_panel (name_panel) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $text_add_url_panel = "
                🔗 نام پنل ذخیره شد حالا آدرس پنل خود ارسال کنید
            
        ⚠️ توجه:
        🔸 آدرس پنل باید بدون dashboard ارسال شود.
        🔹 در صورتی که پورت پنل 443 است پورت را نباید وارد کنید.  
                ";
    sendmessage($from_id, $text_add_url_panel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_link_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_link_panel") {
    if (filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, "👤 آدرس پنل ذخیره شد حالا نام کاربری  را ارسال کنید.", $backadmin);
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'add_username_panel';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE marzban_panel SET  url_panel = ? WHERE name_panel = ?");
        $stmt->bind_param("ss", $text, $Processing_value);
        $stmt->execute();
    } else {
        sendmessage($from_id, "🔗 آدرس دامنه نامعتبر است", $backadmin);
    }
} elseif ($user['step'] == "add_username_panel") {
    sendmessage($from_id, "🔑 نام کاربری ذخیره شد در پایان رمز عبور پنل مرزبان خود را وارد نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_password_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  username_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
} elseif ($user['step'] == "add_password_panel") {
    sendmessage($from_id, "تبریک پنل شما با موفقیت اضافه گردید.", $backadmin);
    sendmessage($from_id, "🥳", $keyboardmarzban);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  password_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "❌ حذف پنل") {
    sendmessage($from_id, "پنلی که میخواهید حذف کنید را انتخاب کنید.", $json_list_marzban_panel);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'removepanel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "removepanel") {
    sendmessage($from_id, "پنل با موفقیت حذف گردید.", $keyboardmarzban);
    $stmt = $connect->prepare("DELETE FROM marzban_panel WHERE name_panel = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "📨 ارسال پیام به کاربر") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $sendmessageuser);
} elseif ($text == "✉️ ارسال همگانی") {
    sendmessage($from_id, "متن خود را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforsendall';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforsendall") {
    foreach ($users_ids as $id) {
        sendmessage($id, $text, null);
    }
    sendmessage($from_id, "✅ پیام برای تمامی کاربران ارسال شد.", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📤 فوروارد همگانی") {
    sendmessage($from_id, "متن فورواردی خود را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforwardMessage';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforwardMessage") {
    foreach ($users_ids as $id) {
        forwardMessage($from_id, $message_id, $id);
    }
    sendmessage($from_id, "✅ پیام برای تمامی کاربران فوروارد شد.", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text  == "📝 تنظیم متن ربات") {
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید.", $textbot);
} elseif ($text == "تنظیم متن شروع") {
    $textstart = "
                متن جدید خود را ارسال کنید.
            متن فعلی :
             " . $datatextbot['text_start'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextstart';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextstart") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_start'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه سرویس خریداری شده") {
    $textstart = "
            متن جدید خود را ارسال کنید.
            متن فعلی:
             " . $datatextbot['text_Purchased_services'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfo';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextinfo") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Purchased_services'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه اکانت تست") {
    $textstart = "
            متن جدید خود را ارسال کنید.
            متن فعلی:
             " . $datatextbot['text_usertest'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextusertest") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_usertest'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات اطلاعات سرویس") {
    $textstart = "
            متن جدید خود را ارسال کنید.
            متن فعلی:
            ``` " . $datatextbot['text_dec_info'] . "```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfodec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "changetextinfodec") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_info'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "متن دکمه 📚 آموزش") {
    $textstart = "
            متن جدید خود را ارسال کنید.
            متن فعلی:
            " . $datatextbot['text_help'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_help") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_help'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "متن دکمه ☎️ پشتیبانی") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            " . $datatextbot['text_support'];
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_support';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_support") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_support'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات پشتیبانی") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_dec_support']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_support';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_dec_support") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_support'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "دکمه سوالات متداول") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_fq']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_fq") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات سوالات متداول") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_dec_fq']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_dec_fq") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات عضویت اجباری") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_channel']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_channel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_channel") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_channel'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "متن دکمه حساب کاربری") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_account']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_account';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "text_account") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_account'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "دکمه افزایش موجودی") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_Add_Balance']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_Add_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_Add_Balance") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Add_Balance'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "📝 تنظیم متن توضیحات شماره کارت") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_cart_to_cart']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_cart_to_cart';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_cart_to_cart") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_cart_to_cart'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text == "✍️ ارسال پیام برای یک کاربر") {
    sendmessage($from_id, "متن خود را ارسال کنید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetext';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendmessagetext") {
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, "✅ متن دریافت شد حالا آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetid';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendmessagetid") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $textsendadmin = "
            👤 یک پیام از طرف ادمین ارسال شده است  
        
        متن پیام:
        $Processing_value
            ";
    sendmessage($text,  $textsendadmin, null);
    sendmessage($from_id, "✅ پیام ارسال شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

//_________________________________________________
if ($text == "📚 بخش آموزش"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید 😊", $keyboardhelpadmin);
}
elseif ($text == "📚 اضافه کردن آموزش") {
    $text_add_help_name = "
            برای اضافه کردن آموزش یک نام ارسال کنید 
         ⚠️ توجه: نام آموزش نامی است که کاربر در لیست مشاهده می کند.
            ";
    sendmessage($from_id, $text_add_help_name, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_name_help") {
    $stmt = $connect->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $text_add_dec = "
                🔗 نام آموزش ذخیره شد حالا توضیحات خود را ارسال کنید 
            
        ⚠️ توجه:
        🔸 توضیحات میتوانید همراه با عکس یا فیلم ارسال کنید
                ";
    sendmessage($from_id, $text_add_dec, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_dec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_dec") {
    if ($photo) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $photoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "photo";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    } elseif ($text) {
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $text, $Processing_value);
        $stmt->execute();
    } elseif ($video) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $videoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "video";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    }
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($text == "❌ حذف آموزش") {
    sendmessage($from_id, "نام آموزش را انتخاب کنید", $json_list_help);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'remove_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "remove_help") {
    $stmt = $connect->prepare("DELETE FROM help WHERE name_os = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, "✅ آموزش حذف گردید.", $keyboardhelpadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($forward_from_id != 0) {
    $textSendAdminToUser = "
            📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
        
        متن پیام : 
        $text";
    sendmessage($forward_from_id, $textSendAdminToUser, null);
    sendmessage($from_id, "✅ پیام با موفقیت برای کاربر ارسال گردید.", null);
}
//_________________________________________________
$Bot_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['Bot_Status'], 'callback_data' => $setting['Bot_Status']],
        ],
    ]
]);
if ($text == "📡 وضعیت ربات") {
    sendmessage($from_id, "وضعیت ربات", $Bot_Status);
}
if ($datain == "✅  ربات روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = '❌ ربات خاموش است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "ربات خاموش گردید ❌", null);
}
elseif ($datain == "❌ ربات خاموش است") {
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = "✅  ربات روشن است";;
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "🤖 ربات روشن گردید.", null);
}
//_________________________________________________
if ($text == "🔒 مسدود کردن کاربر") {
    sendmessage($from_id, "👤 آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userblock = mysqli_fetch_assoc($result);
    if ($userblock['User_Status'] == "block") {
        sendmessage($from_id, "کاربر از قبل بلاک بوده است❗️", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "block";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    sendmessage($from_id, "🚫 کاربر مسدود شد حالا دلیل مسدودی هم ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'adddecriptionblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "adddecriptionblock") {
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, "✍️ دلیل مسدودی کاربر ذخیره شد", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "🔓 رفع مسدودی کاربر") {
    sendmessage($from_id, "👤 آیدی عددی کاربر را ارسال کنید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidunblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidunblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userunblock = mysqli_fetch_assoc($result);
    if ($userunblock['User_Status'] == "Active") {
        sendmessage($from_id, "کاربر بلاک نیست 😐", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "Active";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $spcae = "";
    $stmt->bind_param("ss", $spcae, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, "کاربر از حالت مسدودی خارج گردید. 🤩", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if($text == "♨️ بخش قوانین"){
    sendmessage($from_id, "یکی از گزینه های ز یر را انتخاب کنید", $rollkey);
}
elseif ($text == "⚖️ متن قانون") {
    $textstart = "
            متن جدید خود راارسال کنید.
            متن فعلی:
            ```". $datatextbot['text_roll']."```";
    sendmessage($from_id, $textstart, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_roll';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_roll") {
    sendmessage($from_id, "✅ متن با موفقیت ذخیره شد", $textbot);
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_roll'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
$roll_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['roll_Status'], 'callback_data' => $setting['roll_Status']],
        ],
    ]
]);
if($text == "💡 روشن / خاموش کردن تایید قوانین"){
    sendmessage($from_id, "وضعیت قانون ", $roll_Status);
}
if ($datain == "✅ تایید قانون روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
    $Status = '❌ تایید قوانین خاموش است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "قانون غیرفعال گردید ❌", null);
} elseif ($datain == "❌ تایید قوانین خاموش است") {
    $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
    $Status = '✅ تایید قانون روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "♨️ قانون فعال گردید. از این پس اگر کاربری قوانین را تایید نکرده باشد نمی تواند از امکانات ربات استفاده نماید", null);
}
//_________________________________________________
if($text == "👤 خدمات کاربر"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید 👇😊", $User_Services);
}
#-------------------------#

elseif($text == "📊 وضعیت تایید شماره کاربر"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_status';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_status"){
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $user_phone_status = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    if ($user_phone_status['number'] == "none"){
        sendmessage($from_id, "🛑شماره موبایل تایید نشده است🛑", $User_Services);
    }
    else{
        sendmessage($from_id,"شماره موبایل کاربر تایید شده است ✅🎉" , $User_Services);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#

$get_number = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['get_number'], 'callback_data' => $setting['get_number']],
        ],
    ]
]);
if($text == "☎️ وضعیت احراز هویت شماره تماس"){
    sendmessage($from_id, "📞 وضعیت احراز هویت شماره تماس", $get_number);
}
if ($datain == "✅ تایید شماره موبایل روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '❌ احرازهویت شماره تماس غیرفعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "احرازهویت شماره تماس غیرفعال گردید ❌", null);
} elseif ($datain == "❌ احرازهویت شماره تماس غیرفعال است") {
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '✅ تایید شماره موبایل روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "🥳 احرازهویت شماره تماس فعال گردید. از این پس هرکاربری بخواهد از خدمات ربات استفاده کند باید شماره تماس خود را ارسال کند.", null);
}
#-------------------------#
if ($text == "👀 مشاهده شماره تلفن کاربر"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_admin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_number_admin"){
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $user_phone_number = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    if ($user_phone_number['number'] == "none"){
        sendmessage($from_id, "کاربر شماره موبایل خود را ارسال نکرده است", $User_Services);
        return;
    }
    $text_number = "
        ☎️ شماره تلفن کاربر :{$user_phone_number['number']}
         ";
    sendmessage($from_id, $text_number, $User_Services);
}
#-------------------------#
if ($text == "👈 تایید دستی شماره"){
    sendmessage($from_id, "آیدی عددی کاربر را ارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'confrim_number';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "confrim_number"){
    $stmt = $connect->prepare("UPDATE user SET number  = ? WHERE id = ?");
    $confrimnum = 'confrim number by admin';
    $stmt->bind_param("ss", $confrimnum, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step  = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $text);
    $stmt->execute();
    sendmessage($from_id, "✅ شماره موبایل کاربر  تایید شد", $User_Services);
}
if($text == "📣 تنظیم کانال گزارش"){
    $text_channel = "
            📣در این بخش میتوانید آیدی عددی کانال یا گروه را برای ارسال اعلان ارسال نمایید
    
    برای دریافت آیدی عددی کانال می توانید پیامی داخل کانال ارسال کرده و پیام ارسال شده را برای آیدی زیر فوروارد کنید تا ایدی عددی دریافت کنید.
    برای گروه هم میتوانید ربات را اد کرده و دستور ارسال ایدی ارسال کنید تا آیدی عددی گروه را دیافت کنید
    @myidbot
            
         آیدی عددی فعلی شما:" . $setting['Channel_Report'];
    sendmessage($from_id, $text_channel, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addchannelid';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "addchannelid") {
    sendmessage($from_id, "🔰 کانال با موفقیت تنظیم گردید.", $keyboardadmin);
    $stmt = $connect->prepare("UPDATE setting SET Channel_Report = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    sendmessage($setting['Channel_Report'], "تست ارسال کانال گزارش", null);
}
#-------------------------#
if($text == "🏬 بخش فروشگاه"){
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $shopkeyboard);
}
elseif ($text == "🛍 اضافه کردن محصول"){
    $text_add_name_product = "
        ابتدا نام اشتراک خود را ارسال نمایید
    ⚠️ نکات هنگام وارد کردن ام محصول:
    • در کنار نام اشتراک حتما قیمت اشتراک را هم وارد کنید.
    • در کنار نام اشتراک حتما زمان اشتراک را هم وارد کنید.
        ";
    sendmessage($from_id, $text_add_name_product, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_limit"){
    $stmt = $connect->prepare("INSERT IGNORE INTO product (name_product) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id,"نام محصول ذخیره شد ✅
    حجم اشتراک را ارسال کنید توجه واجد حجم گیگابایت است", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_time';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_time"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"حجم نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Volume_constraint = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"حجم اشتراک ذخیره شد ✅
    زمان اشتراک را وارد نمایید توجه زمان واحد زمان اشتراک روز است", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_price"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"زمان نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Service_time = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"زمان اشتراک ذخیره شد ✅
    قمیت اشتراک  را ارسال کنید.
    توجه: 
    قیمت محصول براساس تومان است و قیمت را بدون هیچ کاراکتر اضافی ارسال نمایید.", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'endstep';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "endstep"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"قیمت نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET price_product = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"محصول با موفقیت ذخیره شد 🥳🎉", $shopkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "👨‍🔧 بخش ادمین"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید", $admin_section_panel);
}
#-------------------------#
if($text == "⚙️ تنظیمات"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$setting_panel);
}
#-------------------------#
if($text == "📱 احراز هویت شماره"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$valid_Number);
}
#-------------------------#
if($text == "📊 بخش گزارشات"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$reports);
}
#-------------------------#
if($text == "🔑 تنظیمات اکانت تست"){
    sendmessage($from_id,"یکی از گزینه های زیر را انتخاب کنید",$keyboard_usertest);
}
#-------------------------#
if(preg_match('/Confirm_pay_(\w+)/',$datain, $dataget)) {
    $order_id= $dataget[1];
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$order_id' LIMIT 1"));
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject"){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "❌ این پرداخت قبلا توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
    $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();

    $textconfrom = "
        💵 پرداخت با موفقیت تایید گردید.
          به موجودی کاربر مبلغ {$Payment_report['price']} اضافه گردید.
        ";
    sendmessage($from_id,$textconfrom,null);
    sendmessage($Payment_report['id_user'],"💎 کاربر گرامی مبلغ{$Payment_report['price']} تومان به کیف پول شما واریز گردید با تشکر از پرداخت شما.
    
    🛒 کد پیگیری شما: {$Payment_report['id_order']}",null);
}
#-------------------------#
if(preg_match('/reject_pay_(\w+)/',$datain, $datagetr)) {
    $id_order= $datagetr[1];
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$id_order' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Payment_report['id_user'], $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $id_order, $from_id);
    $stmt->execute();
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status']  == "paid"){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "❌ این پرداخت قبلا توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "reject";
    $stmt->bind_param("ss", $Status_change, $id_order);
    $stmt->execute();

    sendmessage($from_id,"دلیل رد کردن پرداخت را ارساال نمایید",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "reject-dec";
    $stmt->bind_param("ss", $step, $Payment_report['id_user']);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, null);
}
elseif($user['step'] == "reject-dec"){
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $stmt->bind_param("ss", $text, $user['Processing_value_one']);
    $stmt->execute();
    $text_reject = "❌ کاربر گرامی پرداخت شما به دلیل زیر رد گردید.
    ✍️ $text
    🛒 کد پیگیری پرداخت: {$user['Processing_value_one']}
    ";
    sendmessage($Processing_value,$text_reject,null);
    sendmessage($from_id,"⭕️ پرداخت با موفقیت رد گردید و به کاربر پیام ارسال شد.",$keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "❌ حذف محصول"){
    sendmessage($from_id,"محصولی که میخوای حذف کنی ر و انتخاب کن",$json_list_product_list_admin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'remove-product';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}elseif ($user['step'] == "remove-product"){
    if (!in_array($text , $name_product)){
        sendmessage($from_id, "❌ خطا 
    📝 محصول انتخابی وجود ندارد", null);
        return;
    }
    $stmt = $connect->prepare("DELETE FROM product WHERE name_product = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id,"✅ محصول با موفقیت حذف گردید.",$shopkeyboard);
}
#-------------------------#
if ($text == "✏️ ویرایش محصول"){
    sendmessage($from_id,"محصولی که میخوای ویرایش کنی رو انتخاب کن",$json_list_product_list_admin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_filde';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "change_filde"){
    if (!in_array($text , $name_product)){
        sendmessage($from_id, "❌ خطا 
    📝 محصول انتخابی وجود ندارد", null);
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id,"فیلدی که مخیواهید ویرایش کنید را انتخاب کنید",$change_product);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "قیمت"){
    sendmessage($from_id,"قیمت جدید را ارسال کنید",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_price';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "change_price"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"قیمت نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET price_product = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"✅ قیمت محصول بروزرسانی شد",$shopkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "حجم"){
    sendmessage($from_id,"حجم جدید را ارسال کنید",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_val';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "change_val"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"حجم نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Volume_constraint = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"✅ حجم محصول بروزرسانی شد",$shopkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "زمان"){
    sendmessage($from_id,"زمان جدید را ارسال کنید",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_time';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "change_time"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"زمان نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Service_time = ? WHERE name_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"✅ حجم محصول بروزرسانی شد",$shopkeyboard);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "⏳ زمان سرویس تست"){
    sendmessage($from_id,"🕰 مدت زمان سرویس تست را ارسال کنید.
    زمان فعلی: {$setting['time_usertest']} ساعت
    ⚠️ زمان بر حسب ساعت است.",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'updatetime';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "updatetime"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"زمان نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE setting SET time_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id,"✅ زمان سرویس تست بروزرسانی شد",$keyboard_usertest);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if($text == "💾 حجم اکانت تست"){
    sendmessage($from_id,"حجم سرویس تست را ارسال کنید.
    زمان فعلی: {$setting['val_usertest']} مگابایت
    ⚠️ حجم بر حسب مگابایت است.",$backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'val_usertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "val_usertest"){
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"حجم نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE setting SET val_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id,"✅ حجم سرویس تست بروزرسانی شد",$keyboard_usertest);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "⬆️️️ افزایش موجودی کاربر") {
    $text_add_user_admin = "
            ⚜️ آیدی عددی کاربر را ارسال کنید 
        توضیحات: برای افزایش موجودی کاربر ابتدا آیدی عددی کاربر را ارسال نمایید
            ";
    sendmessage($from_id, $text_add_user_admin, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    sendmessage($from_id, "آیدی عددی دریافت شد مبلغی که میخواهید به کاربر اضافه کنید را ارسال کنید مبلغ به تومان باشد", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_add';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_price_add") {
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"مبلغ نامعتبر است", $backadmin);
        return;
    }
    sendmessage($from_id, "✅ مبلغ به موجودی کاربر اضافه شد", $User_Services);
    $Balance_user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$Processing_value' LIMIT 1"));
    $Balance_add_user = $Balance_user['Balance'] +$text;
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_add_user, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "⬇️ کم کردن موجودی") {
    $text_add_user_admin = "
            ⚜️ آیدی عددی کاربر را ارسال کنید 
        توضیحات: برای کم کردن موجودی کاربر ابتدا آیدی عددی کاربر را ارسال نمایید
            ";
    sendmessage($from_id, $text_add_user_admin, $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'Negative_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "Negative_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    sendmessage($from_id, "آیدی عددی دریافت شد مبلغی که میخواهید از کاربر کاربر کم کنید را ارسال کنید مبلغ به تومان باشد", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_Negative';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_price_Negative") {
    if (!ctype_digit($text))
    {
        sendmessage($from_id,"مبلغ نامعتبر است", $backadmin);
        return;
    }
    sendmessage($from_id, "✅ مبلغ از موجودی کاربر کسر شد", $User_Services);
    $Balance_add_user = $user['Balance'] - $text;
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_add_user, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "👁‍🗨 مشاهده اطلاعات کاربر"){
    sendmessage($from_id, "برای مشاهده اطلاعات کاربر آیدی عددی کاربر راارسال نمایید", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'show_info';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "show_info"){
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "کاربری با این شناسه یافت نشد", $backadmin);
        return;
    }
    $user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    $roll_Status = [
        '1' => 'تایید شده',
        '0' => 'تایید نشده',
    ][$user['roll_Status']];
    $userinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $text, 'callback_data' => "id_user"],
                ['text' => "🔵 آیدی عددی کاربر", 'callback_data' => "id_user"],
            ],
            [
                ['text' => $user['limit_usertest'], 'callback_data' => "limit_usertest"],
                ['text' => "🔵 محدودیت اکانت تست", 'callback_data' => "limit_usertest"],
            ],
            [
                ['text' => $roll_Status, 'callback_data' => "roll_Status"],
                ['text' => "🔵 وضعیت تایید قانون", 'callback_data' => "roll_Status"],
            ],
            [
                ['text' => $user['number'], 'callback_data' => "number"],
                ['text' => "🔵 شماره موبایل", 'callback_data' => "number"],
            ],
            [
                ['text' => $user['Balance'], 'callback_data' => "Balance"],
                ['text' => "🔵 موجودی کابر", 'callback_data' => "Balance"],
            ],
        ]
    ]);
    sendmessage($from_id, "👀 اطلاعات کاربر: ", $userinfo);
    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید", $User_Services);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
$help_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['help_Status'], 'callback_data' => $setting['help_Status']],
        ],
    ]
]);
if ($text == "💡 وضعیت بخش آموزش") {
    sendmessage($from_id, "وضعیت بخش آموزش", $help_Status);
}
if ($datain == "✅آموزش فعال است") {
    $stmt = $connect->prepare("UPDATE setting SET help_Status = ?");
    $Status = '❌ آموزش غیرفعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "❌ آموزش غیرفعال است", null);
}
elseif ($datain == "❌ آموزش غیرفعال است") {
    $stmt = $connect->prepare("UPDATE setting SET help_Status = ?");
    $Status = '✅ آموزش فعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "📚 بخش آموزش روشن گردید", null);
}
#-------------------------#
if($text == "🎁 ساخت کد هدیه"){
    sendmessage($from_id, "کدی را برای کدهدیه ارسال کنید ", $backadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_code';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_code"){
    if (!preg_match('/^[A-Za-z]+$/', $text)) {
            sendmessage($from_id,"کد نامعتبر است کد باید حتما انگلیسی بدون کاراکتر اضافی باشد",null);
            return;
}
    $stmt = $connect->prepare("INSERT INTO Discount (code) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id,"کد دریافت شد حالا مبلغ کد هدیه رو بفرست",null);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_code';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "get_price_code"){
        if (!ctype_digit($text))
    {
        sendmessage($from_id,"مبلغ نامعتبر است", $backadmin);
        return;
    }
    $stmt = $connect->prepare("UPDATE Discount SET price = ? WHERE code = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id,"✅  کد هدیه با موفقیت ثبت گردید.",$keyboardadmin);
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();

}
#-------------------------#
$getNumberIran = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['iran_number'], 'callback_data' => $setting['iran_number']],
        ],
    ]
]);
if ($text == "تایید شماره ایرانی 🇮🇷") {
    sendmessage($from_id, "در این قسمت میتوانید تعیین کنید در زمان احراز هویت شماره تلفن از کاربر فقط شماره ایرانی گرفته شود یا  تمامی شماره ها",$getNumberIran);
}
if ($datain == "✅ احرازشماره ایرانی روشن است") {
    $stmt = $connect->prepare("UPDATE setting SET iran_number = ?");
    $Status = "❌ بررسی شماره ایرانی غیرفعال است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "❌ احراز هویت شماره از کاربران با تمام شماره ها از این پس امکان پذیر می باشد.", null);
}
elseif ($datain == "❌ بررسی شماره ایرانی غیرفعال است") {
    $stmt = $connect->prepare("UPDATE setting SET iran_number = ?");
    $Status = "✅ احرازشماره ایرانی روشن است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "احراز هویت شماره تماس فقط مخصوص شماره های ایرانی فعال گردید 🇮🇷", null);
}
#-------------------------#
$sublinkkeyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['sublink'], 'callback_data' => $setting['sublink']],
        ],
    ]
]);
if ($text == "🔗 ارسال اشتراک لینکی بعد خرید") {
    sendmessage($from_id, "در ای قسمت می توانید تنظیم کنید که کاربر بعد از خرید لینک سابسکرایبشن دریافت کند یا نه",$sublinkkeyboard);
}
if ($datain == "✅ لینک اشتراک فعال است.") {
    $stmt = $connect->prepare("UPDATE setting SET sublink = ?");
    $Status = "❌ ارسال لینک سابسکرایب غیرفعال است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "⭕️ ارسال لینک اشتراک غیرفعال گردید. از این پس کاربر پس از خرید لینک سابسکرایب دریافت نخواهد کرد.", null);
}
elseif ($datain == "❌ ارسال لینک سابسکرایب غیرفعال است") {
    $stmt = $connect->prepare("UPDATE setting SET sublink = ?");
    $Status = "✅ لینک اشتراک فعال است.";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "لینک سابسکرایب فعال گردید. از این پس کاربر پس خرید لینک سابسکرایب دریافت خواهد کرد.", null);
}
#-------------------------#
$configkeyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['configManual'], 'callback_data' => $setting['configManual']],
        ],
    ]
]);
if ($text == "⚙️ ارسال کانفیگ بعد خرید") {
    sendmessage($from_id, "در این قسمت می توانید تعیین کنید که بعد از خرید کاربر کانفیگ های دستی دریافت کند یا خیر",$configkeyboard);
}
if ($datain == "✅ ارسال کانفیگ بعد خرید فعال است.") {
    $stmt = $connect->prepare("UPDATE setting SET configManual = ?");
    $Status = "❌ ارسال کانفیگ دستی خاموش است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "⭕️ ارسال کانفیگ دستی غیرفعال گردید. از این پس کاربر پس از خرید  کانفیگ دستی دریافت نخواهد کرد.", null);
}
elseif ($datain == "❌ ارسال کانفیگ دستی خاموش است") {
    $stmt = $connect->prepare("UPDATE setting SET configManual = ?");
    $Status = "✅ ارسال کانفیگ بعد خرید فعال است.";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "ارسال کانفیگ بعد خرید فعال شد از این پس کاربران کانفیگ دستی هم دریافت خواهند کرد", null);
}
#-------------------------#
