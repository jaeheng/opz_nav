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
    echo '<h3>网址导航插件说明</h3>';
    echo '<div class="card mt-3"><div class="card-header">插件说明</div><div class="card-body">
        开启插件后，发布文章的右侧会有填写链接地址的文本框 <a href="'.$url.'">去看看</a>
        <p class="mt-3">点击量只有通过 <span class="text-danger">过渡页面</span> 跳转才会统计</p>
        <p><span class="text-danger">过渡页面获取</span>: <pre style="padding: 10px;background: #eee;"><code>$url = BLOG_URL . \'?plugin=opz_nav&url=\' . base64_encode(_opz_url($gid));</code></pre></p>
        </div></div>';
    echo '<div class="card mt-3"><div class="card-header">插件使用文档</div><div class="card-body">
        <p>1. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz($logid)</code>来获取文章对应的链接数据(地址和点击量)</p>
        <p>2. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz_url($logid)</code>来获取文章对应的链接地址</p>
        <p>3. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz_views($logid)</code>来获取文章对应链接的点击量</p>
    </div></div>';

    ?>
    <script>
        // emlog相关
        setTimeout(hideActived, 3600);
        $("#menu_category_ext").addClass('active');
        $("#menu_ext").addClass('show');
        $("#opz_nav").addClass('active');
    </script>
<?php
}