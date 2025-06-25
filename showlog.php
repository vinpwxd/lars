<?php

$indexPath = './index.html';
$indexContent = file_get_contents($indexPath);

$indexContent = file_get_contents($indexPath);
$googleTagPattern = '/<!-- Meta Pixel Code -->(.*?)<!-- End Meta Pixel Code -->/is';

function extractGtm($content, $pattern) {
    if (preg_match($pattern, $content, $matches)) {
        return $matches[1];
    }
    return '';
}

$currentGtmScript = extractGtm($indexContent, $googleTagPattern);

$shortLinksFile = 'short_links.json';
$shortLinksData = [];
if (file_exists($shortLinksFile)) {
    $shortLinksData = json_decode(file_get_contents($shortLinksFile), true);
}

$gtmSuccess = false;
$linkSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'save_gtm') {
        $gtmScript = $_POST['gtm_code'] ?? '';

        if (trim($gtmScript) === '') {
            $newHtml = preg_replace($googleTagPattern, '', $indexContent);
        } else {
            $newGtmBlock = "<!-- Meta Pixel Code -->\n" . $gtmScript . "\n<!-- End Meta Pixel Code -->";
            if (preg_match($googleTagPattern, $indexContent)) {
                $newHtml = preg_replace($googleTagPattern, $newGtmBlock, $indexContent);
            } else {
                $newHtml = preg_replace('/<\/head>/i', $newGtmBlock . "\n</head>", $indexContent);
            }
        }

        file_put_contents($indexPath, $newHtml);
        $indexContent = file_get_contents($indexPath); // ✅ 重新读取
        $currentGtmScript = extractGtm($indexContent, $googleTagPattern); // ✅ 重新提取
        $gtmSuccess = true;
    }elseif ($_POST['action'] === 'save_short_links') {
        $urls = $_POST['url'] ?? [];
        $data = [];
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url) {
                $data[] = ['url' => $url];
            }
        }
        file_put_contents($shortLinksFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $shortLinksData = $data;
        $linkSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <title></title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            background: #f5f7fa;
            color: #333;
        }
        h2 {
            color: #0078d7;
            margin-bottom: 10px;
            border-bottom: 2px solid #0078d7;
            padding-bottom: 4px;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgb(0 0 0 / 0.1);
            margin-bottom: 40px;
        }
        textarea {
            width: 100%;
            min-height: 60px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            resize: vertical;
            padding: 8px;
        }

        #gtmForm textarea {
            min-height: 260px;
        }
        button {
            background-color: #0078d7;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            transition: background-color 0.25s;
        }
        button:hover {
            background-color: #005a9e;
        }
        .link-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .link-row textarea {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 8px;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #d9534f;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
            padding: 0 6px;
            line-height: 1;
            user-select: none;
            transition: color 0.2s;
        }
        .delete-btn:hover {
            color: #a94442;
        }
        .success-msg {
            margin-top: 10px;
            color: green;
            font-weight: bold;
        }
    </style>

    <style>
        .description {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #007bff;
            font-size: 15px;
            line-height: 1.8;
        }

        .description .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ffeaa7;
            margin: 15px 0;
        }

        .description .file-status {
            background-color: #ffffff;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            margin: 10px 0;
            font-family: monospace;
            font-size: 13px;
        }

        .description .feature-list {
            background-color: rgba(255,255,255,0.7);
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .status-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }

        .quick-links {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .quick-links a {
            display: inline-block;
            margin-right: 15px;
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            transition: background-color 0.2s;
        }

        .quick-links a:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;

        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section h2 {
            margin-top: 0;
            color: #444;
            font-size: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.5;
            resize: vertical;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        button:hover {
            background-color: #45a049;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #4CAF50;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* 表格优化样式 */
        .table-container {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            max-height: 600px;
            overflow-y: auto;
        }

        .visit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 800px;
        }

        .visit-table thead th {
            background-color: #f8f9fa;
            color: #495057;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .visit-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .visit-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .visit-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .visit-table td {
            padding: 10px;
            vertical-align: middle;
            word-break: break-word;
        }

        .visit-table .time-col {
            font-family: 'Courier New', monospace;
            min-width: 130px;
        }

        .visit-table .ip-col {
            font-family: 'Courier New', monospace;
            min-width: 120px;
        }

        .visit-table .device-col {
            min-width: 80px;
        }

        .visit-table .os-col {
            min-width: 100px;
        }

        .visit-table .browser-col {
            min-width: 80px;
        }

        .visit-table .resolution-col {
            min-width: 100px;
        }

        .visit-table .language-col {
            min-width: 60px;
        }

        .table-stats {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 13px;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .stats-item {
            margin: 5px 10px;
            font-weight: 500;
        }

        .stats-badge {
            background-color: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        /* 折叠功能样式 */
        .collapsible {
            cursor: pointer;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 0;
            user-select: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }

        .collapsible:hover {
            background-color: #e9ecef;
        }

        .collapsible::after {
            content: '▼';
            font-size: 12px;
            color: #6c757d;
            transition: transform 0.2s;
        }

        .collapsible.active::after {
            transform: rotate(-180deg);
        }

        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }

        .collapsible-content.active {
            max-height: 2000px;
            transition: max-height 0.5s ease-in;
        }

        /* 标签页样式 */
        .tabs-container {
            margin-top: 20px;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 24px;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            margin-right: 2px;
            border-radius: 5px 5px 0 0;
            transition: all 0.2s;
            font-weight: 500;
        }

        .tab:hover {
            background-color: #e9ecef;
        }

        .tab.active {
            background-color: #fff;
            border-bottom: 2px solid #fff;
            margin-bottom: -2px;
            color: #007bff;
            font-weight: 600;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .table-stats {
                flex-direction: column;
                text-align: center;
            }

            .stats-item {
                margin: 3px 0;
            }

            .visit-table {
                font-size: 12px;
            }

            .visit-table th,
            .visit-table td {
                padding: 8px 6px;
            }

            .table-container {
                max-height: 400px;
            }
        }

    </style>
</head>
<body>
<div class="container">
    <!-- 访问统计显示 -->
    <div class="section">
        <h2 class="collapsible">📊 访问统计</h2>
        <div class="collapsible-content">
            <div class="collapsible-content">
                <div class="description">
                    <div class="file-status">
                        📁 日志目录：logs/
                        <?php
                        $logsDir = __DIR__ . "/logs";
                        if(is_dir($logsDir) && is_writable($logsDir)): ?>
                            <span class="status-icon status-success"></span>正常运行中
                        <?php else: ?>
                            <span class="status-icon status-warning"></span>目录不存在或无权限
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tab="today">今日统计</div>
                    <div class="tab" data-tab="all">全部统计</div>
                </div>

                <!-- 今日统计标签页 -->
                <div class="tab-content active" id="today-tab">
                    <div class="highlight" style="background-color: #fff3cd; padding: 15px; border-radius: 6px; border: 1px solid #ffeaa7; margin-bottom: 20px;">
                        <strong>今日访问统计：</strong><?php
                        // 获取当前日期的日志文件
                        $currentDate = date('Ymd');
                        $logsDir = __DIR__ . "/logs";
                        $logFile = $logsDir . "/logs_$currentDate.txt";

                        $visitors = [];
                        $logLines = [];

                        // 处理IPv6地址的函数
                        function normalizeIP($ip) {
                            // 如果是IPv6地址
                            if(strpos($ip, ':') !== false) {
                                // 转换为小写
                                $ip = strtolower($ip);

                                // 处理特殊的::1地址
                                if($ip === '::1') {
                                    return '0000:0000:0000:0000:0000:0000:0000:0001';
                                }

                                // 展开IPv6压缩格式
                                if(strpos($ip, '::') !== false) {
                                    $count = substr_count($ip, ':');
                                    $needParts = 8 - ($count - 1);
                                    $expansion = ':' . str_repeat('0:', $needParts);
                                    $ip = str_replace('::', $expansion, $ip);
                                }

                                // 确保每个段都是4位
                                $parts = explode(':', $ip);
                                $parts = array_map(function($part) {
                                    return str_pad($part, 4, '0', STR_PAD_LEFT);
                                }, $parts);

                                // 组合为标准格式
                                return implode(':', array_slice($parts, 0, 8));
                            }
                            return $ip;
                        }

                        // 获取设备分辨率信息
                        function getResolution($userAgent) {
                            $resolution = '';
                            // 匹配DPI信息
                            if(preg_match('/(\d+)dpi/i', $userAgent, $matches)) {
                                $resolution .= $matches[1] . 'DPI';
                            }
                            // 匹配屏幕分辨率
                            if(preg_match('/(\d+)x(\d+)/i', $userAgent, $matches)) {
                                $resolution .= ($resolution ? '; ' : '') . $matches[1] . 'x' . $matches[2];
                            }
                            return $resolution ? $resolution : '未知分辨率';
                        }

                        // 获取操作系统信息
                        function getOS($userAgent) {
                            // Android版本识别
                            if(preg_match('/android\s([0-9.]+)/i', $userAgent, $matches)) {
                                return 'Android ' . $matches[1];
                            }
                            // iOS版本识别
                            if(preg_match('/iPhone OS ([0-9_]+)/i', $userAgent, $matches)) {
                                return 'iOS ' . str_replace('_', '.', $matches[1]);
                            }
                            if(preg_match('/iPad.*OS ([0-9_]+)/i', $userAgent, $matches)) {
                                return 'iPadOS ' . str_replace('_', '.', $matches[1]);
                            }
                            // Windows版本识别
                            if(preg_match('/Windows NT ([0-9.]+)/i', $userAgent, $matches)) {
                                $nt_versions = [
                                    '10.0' => 'Windows 10/11',
                                    '6.3' => 'Windows 8.1',
                                    '6.2' => 'Windows 8',
                                    '6.1' => 'Windows 7',
                                    '6.0' => 'Windows Vista',
                                    '5.2' => 'Windows Server 2003/XP x64',
                                    '5.1' => 'Windows XP'
                                ];
                                return isset($nt_versions[$matches[1]]) ? $nt_versions[$matches[1]] : 'Windows ' . $matches[1];
                            }
                            // Mac版本识别
                            if(preg_match('/Mac OS X ([0-9._]+)/i', $userAgent, $matches)) {
                                return 'macOS ' . str_replace('_', '.', $matches[1]);
                            }
                            // 其他系统
                            $other_os = [
                                '/linux/i' => 'Linux',
                                '/ubuntu/i' => 'Ubuntu',
                                '/webos/i' => 'WebOS'
                            ];
                            foreach($other_os as $regex => $value) {
                                if(preg_match($regex, $userAgent)) {
                                    return $value;
                                }
                            }
                            return '未知系统';
                        }

                        // 获取浏览器信息
                        function getBrowser($userAgent) {
                            $browser_array = [
                                '/msie/i'       =>  'Internet Explorer',
                                '/firefox/i'    =>  'Firefox',
                                '/safari/i'     =>  'Safari',
                                '/chrome/i'     =>  'Chrome',
                                '/edge/i'       =>  'Edge',
                                '/opera/i'      =>  'Opera',
                                '/mobile/i'     =>  'Mobile Browser'
                            ];
                            foreach ($browser_array as $regex => $value) {
                                if (preg_match($regex, $userAgent)) {
                                    return $value;
                                }
                            }
                            return '未知浏览器';
                        }

                        if(file_exists($logFile)) {
                            $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            foreach($logs as $log) {
                                $parts = explode(' ---- ', $log);
                                if(count($parts) >= 8) {
                                    $time = $parts[0];
                                    $ip = normalizeIP($parts[1]); // 标准化IP地址
                                    $device = $parts[2];
                                    $userAgent = $parts[4];
                                    $language = $parts[7];

                                    // 获取操作系统、浏览器和分辨率信息
                                    $os = getOS($userAgent);
                                    $browser = getBrowser($userAgent);
                                    $resolution = getResolution($userAgent);

                                    // 用标准化后的IP作为唯一标识
                                    $visitors[$ip] = true;

                                    // 更新日志行格式，添加分辨率信息
                                    $logLines[] = "{$time} | {$ip} | {$device} | {$os} | {$browser} | {$resolution} | {$language}";
                                }
                            }
                        }

                        $uniqueVisitors = count($visitors);
                        echo " 独立访客 <strong>" . number_format($uniqueVisitors) . "</strong> 人，总访问 <strong>" . count($logLines) . "</strong> 次";
                        ?>
                        <br><small style="color: #856404; margin-top: 5px; display: block;">📁 日志文件：logs/logs_<?php echo $currentDate; ?>.txt</small>
                    </div>

                    <div class="table-container">
                        <table class="visit-table">
                            <thead>
                            <tr>
                                <th class="time-col">访问时间</th>
                                <th class="ip-col">IP地址</th>
                                <th class="device-col">设备</th>
                                <th class="os-col">系统</th>
                                <th class="browser-col">浏览器</th>
                                <th class="resolution-col">分辨率</th>
                                <th class="language-col">语言</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (!empty($logLines)) {
                                // 逆序显示，最新的在上面
                                $reversedLogLines = array_reverse($logLines);
                                foreach ($reversedLogLines as $index => $logLine) {
                                    $parts = explode(' | ', $logLine);
                                    if (count($parts) >= 7) {
                                        echo '<tr>';
                                        echo '<td class="time-col">' . htmlspecialchars($parts[0]) . '</td>';
                                        echo '<td class="ip-col">' . htmlspecialchars($parts[1]) . '</td>';
                                        echo '<td class="device-col">' . htmlspecialchars($parts[2]) . '</td>';
                                        echo '<td class="os-col">' . htmlspecialchars($parts[3]) . '</td>';
                                        echo '<td class="browser-col">' . htmlspecialchars($parts[4]) . '</td>';
                                        echo '<td class="resolution-col">' . htmlspecialchars($parts[5]) . '</td>';
                                        echo '<td class="language-col">' . htmlspecialchars($parts[6]) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr><td colspan="7" class="empty-state">';
                                echo '<div style="font-size: 16px;">今日暂无访问记录</div>';
                                echo '<div style="margin-top: 5px; font-size: 12px; opacity: 0.7;">数据将在有访问时自动显示</div>';
                                echo '</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-stats">
                        <div>
                            <span class="stats-item">总访问量: <span class="stats-badge"><?php echo count($logLines); ?></span></span>
                            <span class="stats-item">独立访客: <span class="stats-badge"><?php echo $uniqueVisitors; ?></span></span>
                        </div>
                        <div style="font-size: 11px; color: #868e96;">
                            今日数据按访问时间倒序排列 | 最后更新: <?php echo date('H:i:s'); ?>
                        </div>
                    </div>
                </div>

                <!-- 全部统计标签页 -->
                <div class="tab-content" id="all-tab">
                    <div class="highlight" style="background-color: #d1ecf1; padding: 15px; border-radius: 6px; border: 1px solid #bee5eb; margin-bottom: 20px;">
                        <strong>全部访问统计：</strong><?php
                        // 获取所有日志文件
                        $allVisitors = [];
                        $allLogLines = [];
                        $totalDays = 0;

                        if(is_dir($logsDir)) {
                            $logFiles = glob($logsDir . "/logs_*.txt");
                            $totalDays = count($logFiles);

                            foreach($logFiles as $file) {
                                if(file_exists($file)) {
                                    $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                                    foreach($logs as $log) {
                                        $parts = explode(' ---- ', $log);
                                        if(count($parts) >= 8) {
                                            $time = $parts[0];
                                            $ip = normalizeIP($parts[1]);
                                            $device = $parts[2];
                                            $userAgent = $parts[4];
                                            $language = $parts[7];

                                            $os = getOS($userAgent);
                                            $browser = getBrowser($userAgent);
                                            $resolution = getResolution($userAgent);

                                            $allVisitors[$ip] = true;
                                            $allLogLines[] = "{$time} | {$ip} | {$device} | {$os} | {$browser} | {$resolution} | {$language}";
                                        }
                                    }
                                }
                            }
                        }

                        $allUniqueVisitors = count($allVisitors);
                        echo " 独立访客 <strong>" . number_format($allUniqueVisitors) . "</strong> 人，总访问 <strong>" . count($allLogLines) . "</strong> 次";
                        ?>
                        <br><small style="color: #0c5460; margin-top: 5px; display: block;">📊 统计周期：<?php echo $totalDays; ?> 天 | 数据来源：logs/ 目录下所有日志文件</small>
                    </div>

                    <div class="table-container">
                        <table class="visit-table">
                            <thead>
                            <tr>
                                <th class="time-col">访问时间</th>
                                <th class="ip-col">IP地址</th>
                                <th class="device-col">设备</th>
                                <th class="os-col">系统</th>
                                <th class="browser-col">浏览器</th>
                                <th class="resolution-col">分辨率</th>
                                <th class="language-col">语言</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (!empty($allLogLines)) {
                                // 按时间排序（最新的在上面）
                                usort($allLogLines, function($a, $b) {
                                    $timeA = explode(' | ', $a)[0];
                                    $timeB = explode(' | ', $b)[0];
                                    return strcmp($timeB, $timeA);
                                });

                                // 只显示最近500条记录以避免页面过长
                                $displayLines = array_slice($allLogLines, 0, 500);

                                foreach ($displayLines as $index => $logLine) {
                                    $parts = explode(' | ', $logLine);
                                    if (count($parts) >= 7) {
                                        echo '<tr>';
                                        echo '<td class="time-col">' . htmlspecialchars($parts[0]) . '</td>';
                                        echo '<td class="ip-col">' . htmlspecialchars($parts[1]) . '</td>';
                                        echo '<td class="device-col">' . htmlspecialchars($parts[2]) . '</td>';
                                        echo '<td class="os-col">' . htmlspecialchars($parts[3]) . '</td>';
                                        echo '<td class="browser-col">' . htmlspecialchars($parts[4]) . '</td>';
                                        echo '<td class="resolution-col">' . htmlspecialchars($parts[5]) . '</td>';
                                        echo '<td class="language-col">' . htmlspecialchars($parts[6]) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr><td colspan="7" class="empty-state">';
                                echo '<div style="font-size: 16px;">暂无访问记录</div>';
                                echo '<div style="margin-top: 5px; font-size: 12px; opacity: 0.7;">数据将在有访问时自动显示</div>';
                                echo '</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-stats">
                        <div>
                            <span class="stats-item">总访问量: <span class="stats-badge"><?php echo count($allLogLines); ?></span></span>
                            <span class="stats-item">独立访客: <span class="stats-badge"><?php echo $allUniqueVisitors; ?></span></span>
                            <span class="stats-item">统计天数: <span class="stats-badge"><?php echo $totalDays; ?></span></span>
                        </div>
                        <div style="font-size: 11px; color: #868e96;">
                            全部数据按访问时间倒序排列，显示最近500条记录 | 最后更新: <?php echo date('H:i:s'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 折叠功能
        document.querySelectorAll('.collapsible').forEach(collapsible => {
            collapsible.classList.add('active');
            const content = collapsible.nextElementSibling;
            content.classList.add('active');
        });


        // 标签页切换功能
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // 移除所有活动状态
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // 激活当前标签
                this.classList.add('active');
                document.getElementById(targetTab + '-tab').classList.add('active');
            });
        });
    });
</script>
</body>
</html>
