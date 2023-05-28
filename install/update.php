<?php
global $outputdownload,$outputzip,$Outputfnish,$outputdownload,$outputzip,$Outputfnish,$updated;
$version = file_get_contents('version');
function latestRelease(){
$url = "https://api.github.com/repos/mahdigholipour3/bottelegrammarzban/releases/latest";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
$response = curl_exec($curl);
curl_close($curl);
$latestRelease = json_decode($response, true);
return $latestRelease;

}
$latestRelease = latestRelease();
$githubUrl = 'https://github.com/mahdigholipour3/bottelegrammarzban/archive/'.$latestRelease['tag_name'].'.zip';
$filezip = '../file.zip';
if($version < $latestRelease['tag_name']){
$fileContent = file_get_contents($githubUrl);
if ($fileContent !== false) {
    file_put_contents($filezip, $fileContent);
    $outputdownload = "دانلود فایل با موفقیت انجام شد";
}
$destinationPath = '../';
$command = "unzip -o $filezip -d $destinationPath";
$output = shell_exec($command);
if ($output !== null) {
    $outputzip = "استخراج فایل با موفقیت انجام شد";
} else {
    $outputzip = "استراخ فایل با خطا مواجه شد";
}
$sourcePath = '../bottelegrammarzban-'.$latestRelease['tag_name'];
$destinationPath = '../';

if (!is_dir($destinationPath)) {
    mkdir($destinationPath);
}
$files = scandir($sourcePath);
$files = array_diff($files, ['.', '..']);
foreach ($files as $file) {
    $sourceFile = $sourcePath . '/' . $file;
    $destinationFile = $destinationPath . '/' . $file;
    if($file == 'config.php'){
    unlink($sourceFile);
    continue;
    }
    rename($sourceFile, $destinationFile);
}
$Outputfnish = "آپدیت با موفقیت انجام شد";
unlink($filezip);
if (is_dir($sourcePath)) {
rmdir($sourcePath);
}
}else{
    $updated  = "نسخه جدیدی منتشر نشده است";
}
?>
<html>
<head>
    <title>نصب ربات</title>
    <style>
    @font-face {
    font-family: 'vazir';
    src: url('/Vazir.eot');
    src: local('☺'), url('../fonts/Vazir.woff') format('woff'), url('../fonts/Vazir.ttf') format('truetype');
}
        body {
            font-family:vazir;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .installbox {
            background-color: #ffffff;
            border-radius: 8px;
            width:25%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333333;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            margin-bottom: 10px;
        }
        .btn{
            display:block;
            margin : 10px 0;
            padding:10px 20px;
            background-color:#49b200;
            color:#fff;
            text-decoration :none;
            border-radius:10px;
        }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      width:100%;
      text-align:right;
      direction:rtl;
    }
    
    .form-container {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
      text-align:center;
    }
    
    .form-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    </style>
</head>
<body>
 <div class="container">
    <div class="form-container">
      <h3><?php echo $updated ?></h3>
      <h3><?php echo $outputdownload ?></h3>
      <h3><?php echo $outputzip ?></h3>
      <h3><?php echo $Outputfnish ?></h3>
    </div>
  </div>
</body>
</html>