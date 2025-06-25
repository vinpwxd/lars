<?php
// ajax.php
// 定义函数来获取客户端信息
function getTerminalType() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/Mobile|Android/i', $userAgent)) {
        return "移动设备终端";
    } elseif (preg_match('/Windows/i', $userAgent)) {
        return "电脑终端";
    } elseif (preg_match('/Mac/i', $userAgent)) {
        return "苹果电脑终端";
    } elseif (preg_match('/iPad|iPhone/i', $userAgent)) {
        return "苹果平板或手机终端";
    } elseif (preg_match('/Android|BlackBerry|iPhone|Windows Phone/i', $userAgent)) {
        return "其他移动设备终端";
    }
    return "未知终端类型";
}

function getExternalIP() {
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            return $_SERVER[$header];
        }
    }
    return 'UNKNOWN';
}

function saveContent($data, $filename) {
    // 确保logs目录存在
    $logsDir = __DIR__ . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    file_put_contents($logsDir . '/' . $filename, $data, FILE_APPEND | LOCK_EX);
}


// 允许所有来源访问（如果用HTTP服务器，不要用file://）
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$link['url'] = !empty($_GET['url'])?$_GET['url']:'No Link!';

$selected_url = $link['url'];
// 记录访问数据
$ip = getExternalIP();
$currentDate = date('Ymd'); // 将日期文件名标准化为年-月-日格式
$currentTime = date('Y-m-d H:i:s');
$terminalType = getTerminalType();
$userAgent = $_SERVER['HTTP_USER_AGENT']; // 获取用户代理信息
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct Access'; // 获取来路信息
$host = $_SERVER['HTTP_HOST']; // 获取Host信息
$acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE']; // 获取Accept-Language信息

// 组合日志数据为文本格式
$long_logData = "$currentTime ---- $ip ---- $terminalType ---- $selected_url ---- $userAgent ---- $referer ---- $host ---- $acceptLanguage \n";
$logData = "$currentTime ---- $ip ---- $terminalType ---- $selected_url\n";

// 保存日志到不同的文件中
saveContent($long_logData, "logs_$currentDate.txt");
saveContent($logData, "$currentDate.txt");

// 返回url
echo $link['url'];