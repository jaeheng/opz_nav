<?php
!defined('EMLOG_ROOT') && exit('access denied!');

$options = Cache::getInstance()->readCache('options');

$site_key = $options['site_key'];
$site_description = $options['site_description'];
$site_title = $options['site_title'];
$blogname = $options['blogname'];

$icon = BLOG_URL . 'content/plugins/opz_nav/attention.svg';
$url = Input::getStrVar('url') ? base64_decode(Input::getStrVar('url')): BLOG_URL;
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
        <p>点击跳转：<a href="<?= $url;?>" rel="nofollow"><?= $url;?></a></p>
    </div>
</div>