<?php
!defined('EMLOG_ROOT') && exit('access denied!');

$options = Cache::getInstance()->readCache('options');

$site_key = $options['site_key'];
$site_description = $options['site_description'];
$site_title = $options['site_title'];
$blogname = $options['blogname'];

$icon = BLOG_URL . 'content/plugins/opz_nav/attention.svg';
$url = Input::getStrVar('url') ? base64_decode(Input::getStrVar('url')): BLOG_URL;
$second = _g('redirect_second');
$transition_page = _g('transition_page');

// 访问次数+1
OpzNavClass::getInstance()->increase_views($url);

// 直接跳转
if ($transition_page === 'n') {
    echo "<script>window.location.href = '$url';</script>";
}

require_once View::getView('header');
?>

    <style>
        .box {
            width: 600px;
            max-width: 100%;
            background: #fff;
            margin: 130px auto;
            padding: 50px;
            display: flex;
        }
        .box .right {
            padding-left: 20px;
            box-sizing: border-box;
        }
        .box a {
            color: #0d6efd;
        }
    </style>

<div class="box">
    <img src="<?= $icon;?>" alt="请注意">
    <div class="right">
        <p>请注意您的账号和财产安全</p>
        <p>您即将离开 <strong><?= $blogname;?></strong></p>
        <p>
            <?php if(!$second):?>
            点击跳转：
            <?php else:?>
            <span id="countdown"><?= $second?></span>秒后跳转
            <?php endif;?>
            <a href="<?= $url;?>" rel="nofollow"><?= $url;?></a></p>
    </div>
</div>

<?php if($second > 0):?>
<script>
    function redirectToLink(link, seconds) {
        var countdownElement = document.getElementById('countdown');

        // 显示初始倒计时
        countdownElement.textContent = seconds;

        // 设置定时器，每秒更新倒计时
        var countdownInterval = setInterval(function() {
            seconds--;

            // 更新倒计时显示
            countdownElement.textContent = seconds;

            // 如果倒计时结束，清除定时器并跳转到指定链接
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = link;
            }
        }, 1000);
    }
    redirectToLink('<?= $url;?>', <?= $second?>)
</script>
<?php endif;?>