<?php
/*
Plugin Name: 网址导航插件
Version: 1.0.1
Plugin URL:https://www.emlog.net/plugin/detail/614
Description: 提供录入网址、获取网址ico、跳转过渡页面等功能，具体可见商店介绍页面
Author: 子恒博客
Author URL: https://www.emlog.net/author/index/74
*/

!defined('EMLOG_ROOT') && exit('Access Denied!');

if (!class_exists('OpzNavClass', false)) {
    include __DIR__ . '/opz_nav_class.php';
}

addAction('adm_writelog_side', function () {
    OpzNavClass::getInstance()->add_article_field();
});

addAction('save_log', function ($id) {
    OpzNavClass::getInstance()->save_article_field($id);
});

addAction('adm_menu_ext', function () {

    echo '<a class="collapse-item" id="opz_nav" href="' .BLOG_URL.'/admin/plugin.php?plugin=opz_nav">网址导航插件</a>';
});

/**
 * 获取文章对应的链接数据
 * @param $id
 * @return array
 */
function _opz($id = null) {
    if ($id !== null) {
        return OpzNavClass::getInstance()->get_data($id);
    } else {
        return ['opz_url' => '', 'views' => 0];
    }
}

/**
 * 获取文章对应的链接地址
 * @param $id
 * @return mixed
 */
function _opz_url($id = null) {
    return _opz($id)['opz_url'];
}

/**
 * 获取文章对应链接的点击量
 * @param $id
 * @return mixed
 */
function _opz_views($id = null) {
    return _opz($id)['views'];
}
