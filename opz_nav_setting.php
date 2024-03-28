<?php
!defined('EMLOG_ROOT') && exit('access denied!');

$api = Input::getStrVar('api');
$plugin = Input::getStrVar('plugin');


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

function isDeadLink($url, $timeout = 3) {
    // 创建流上下文设置超时为3秒
    $context = stream_context_create([
        'http' => [
            'timeout' => $timeout, // 设置超时时间为3秒
        ],
    ]);

    // 使用创建的流上下文来获取链接的HTTP头信息
    $headers = @get_headers($url, 0, $context);

    if ($headers && strpos($headers[0], '200') !== false) {
        return false; // 如果状态码为200，则链接有效
    } else {
        return true; // 否则链接无效
    }
}
function get_link_state($gid, $timeout)
{
    $link = _opz($gid)['opz_url'];

    $data = [
            'state' => !isDeadLink($link, $timeout),
        'link_url' => $link
    ];
    echo json_encode($data);
}

if ($plugin === 'opz_nav' && !empty($api)) {
    if ($api === 'get_link_info') {
        $url = Input::postStrVar('link');
        get_link_info($url);
    }

    if ($api === 'get_link_state') {
        $gid = Input::getStrVar('gid');
        $timeout = Input::getStrVar('timeout', 3);
        get_link_state($gid, $timeout);
    }
    die();
}
function plugin_setting_view () {
    $url = BLOG_URL . 'admin/article.php?action=write';
    echo '<h3>网址导航插件说明</h3>';
    echo '<div class="card mt-3"><div class="card-header">插件说明</div><div class="card-body">

        开启插件后，发布文章的右侧会有填写链接地址的文本框 <a href="'.$url.'">去看看</a>
        <h4 style="margin-top: 1em;font-size: 18px;color: black;">插件功能</h4>
        <ul>
            <li>1. 给文章增加链接字段，并可统计链接的访问量</li>
            <li>2. 可遍历链接列表，查询死链</li>
        </ul>
        </div></div>';
    echo '<div class="card mt-3"><div class="card-header">插件的函数可在模版中使用</div><div class="card-body">
        <p>1. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz($logid)</code>来获取文章对应的链接数据(地址和点击量)</p>
        <p>2. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz_url($logid)</code>来获取文章对应的链接地址</p>
        <p>3. 模版中可使用<code style="padding: 5px 10px;margin: 0 10px;background: #eee;">_opz_views($logid)</code>来获取文章对应链接的点击量</p>
    </div></div>';


    $gids = OpzNavClass::getInstance()->all_gid();

    echo '<script>var gids = ' . json_encode($gids) . ';</script>';


    echo '<div class="card mt-3"><div class="card-header">死链查询</div><div class="card-body">
        <p>死链查询受限于服务器网络，无法访问的、被墙的、访问超时的链接都会判断为死链，可自行根据查询结果判断</p>
        <p>共'.count($gids).'个链接<span id="num"></span></p>
        <p>超时设置: 秒<input type="text" class="form-control" id="timeout" value="3"></p>
        <button class="btn btn-primary" id="dead-link-btn">开始查询</button>
        
        <p style="margin-top: 1em;"><strong>死链列表</strong></p>
        <div id="dead-links">暂未查询</div>
        <style>#dead-links p{line-height: 1.5;margin-bottom: 0;color: red;}</style>
    </div></div>';
    ?>
    <script>
        // emlog相关
        setTimeout(hideActived, 3600);
        $("#menu_category_ext").addClass('active');
        $("#menu_ext").addClass('show');
        $("#opz_nav").addClass('active');
        var deadLinks = []

        function checkOneLink (gid, index, length) {
            var timeout = $('#timeout').val();
            $.getJSON('<?= BLOG_URL;?>/admin/plugin.php?plugin=opz_nav&api=get_link_state&gid=' + gid + '&timeout=' + timeout, function (resp) {
                console.log(resp)
                if (!resp.state) {
                    deadLinks.push("<p>文章ID: " +gid+"，链接: <a target='_blank' href='" + resp.link_url+"'>" + resp.link_url + "</a></p>")
                }
                $('#num').text(', 正在查询..., 其中' + (index + 1) + "个查询完成")
                if (index + 1 < length) {
                    checkOneLink(gids[index+1], index+1, length)
                } else {
                    $('#num').text(', ' + length + "个全部查询完成")
                }
                $('#dead-links').html(deadLinks.join(''))
            })
        }
        $('#dead-link-btn').on('click', function () {
            $(this).attr('disabled', true)
            checkOneLink(gids[0], 0, gids.length)
        })
    </script>
<?php
}