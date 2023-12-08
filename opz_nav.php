<?php
/*
Plugin Name: AI资源网址导航模版配套插件
Version: 0.2.2
Plugin URL:https://www.emlog.net/plugin/detail/614
Description: AI资源网址导航模版配套插件，可在模版介绍页面查看
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

function _opz($id = null) {
    if ($id !== null) {
        return OpzNavClass::getInstance()->get_data($id);
    } else {
        return false;
    }
}

function _opz_url($id = null) {
    return _opz($id)['opz_url'];
}
