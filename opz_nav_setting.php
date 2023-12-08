<?php
!defined('EMLOG_ROOT') && exit('access denied!');

$api = Input::getStrVar('api');
$plugin = Input::getStrVar('plugin');
$url = Input::postStrVar('link');


function getWebPageData($url) {
    // 使用cURL获取页面内容
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function getTitle($html) {
    $dom = new DOMDocument;
    @$dom->loadHTML($html);

    // 获取页面标题
    $titleNodeList = $dom->getElementsByTagName('title');
    if ($titleNodeList->length > 0) {
        return $titleNodeList->item(0)->textContent;
    } else {
        return null;
    }
}

function getFavicon($html, $url) {
    $dom = new DOMDocument;
    @$dom->loadHTML($html);

    // 使用XPath查询获取favicon链接
    $xpath = new DOMXPath($dom);
    $faviconNodeList = $xpath->query('//link[@rel="icon" or @rel="shortcut icon"]/@href');

    if ($faviconNodeList->length > 0) {
        $faviconUrl = $faviconNodeList->item(0)->nodeValue;

        // 如果favicon链接是相对路径，则拼接成绝对路径
        if (!filter_var($faviconUrl, FILTER_VALIDATE_URL)) {
            $faviconUrl = (parse_url($url, PHP_URL_SCHEME) ? '' : $url) . $faviconUrl;
        }

        return $faviconUrl;
    } else {
        // 如果没有通过link标签引入favicon.ico，则使用默认的根目录favicon.ico
        $defaultFavicon = $url . '/favicon.ico';

        // 检查默认的favicon.ico是否存在
        if (checkFaviconExistence($defaultFavicon)) {
            return $defaultFavicon;
        } else {
            return null;
        }
    }
}

function checkFaviconExistence($faviconUrl) {
    $headers = get_headers($faviconUrl);
    return stripos($headers[0], '200 OK') !== false;
}

function getFileExtension($url) {
    // 使用pathinfo函数获取文件路径信息
    $pathInfo = pathinfo($url);

    // 检查是否有扩展名
    if (isset($pathInfo['extension'])) {
        return $pathInfo['extension'];
    } else {
        return '.ico';
    }
}

/**
 * Download remote files
 * @param string $source file url
 * @return string Temporary file path
 */
function opzDownFile($source) {
    $content = file_get_contents($source);
    if ($content === false) {
        return '';
    }

    $fileName = substr(md5($source), 0, 4) . time() . '.' . getFileExtension($source);

    $dir_name = gmdate('Ym');

    // 读取、写入文件使用绝对路径，兼容API文件上传
    $uploadFullPath = Option::UPLOADFILE_FULL_PATH . $dir_name . '/';
    $temp_file = $uploadFullPath . $fileName;

    if (!is_dir($uploadFullPath)) {
        mkdir($uploadFullPath);
    }
    $ret = file_put_contents($temp_file, $content);
    if ($ret === false) {
        return '';
    }

    return Option::UPLOADFILE_PATH . $dir_name . '/' . $fileName;
}

function get_link_info ($url) {
    $html = getWebPageData($url);

    if ($html) {
        $title = getTitle($html);
        $favicon = getFavicon($html, $url);
        $temp_path = '';
        if ($favicon) {
            $temp_path = opzDownFile($favicon);
        }

        $data = [
            'code' => 200,
            'data' => [
                'title' => $title,
                'cover' => $temp_path
            ]
        ];

    } else {
        $data = [
            'code' => 400,
            'data' => '获取失败'
        ];
    }
    echo json_encode($data);
}

if ($plugin === 'opz_nav' && !empty($api)) {
    if ($api === 'get_link_info') {
        get_link_info($url);
    }
    die();
}
function plugin_setting_view () {
    $url = BLOG_URL . 'admin/article.php?action=write';
    echo '<p>无更多设置内容, 开启插件即可</p>';
    echo '<p>开启后，发布文章的右侧会有填写链接地址的文本框</p>';
    echo '<p><a href="'.$url.'">去看看</a></p>';
}