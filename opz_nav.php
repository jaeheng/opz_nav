<?php
/*
Plugin Name: emlog导航站模版配套插件
Version: 0.0.1
Plugin URL:https://www.emlog.net/template/detail/1107
Description: emlog导航站模版配套插件，可在模版介绍页面查看
Author: 子恒博客<phpat@qq.com>
Author URL: https://blog.phpat.com
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
        return 'none';
    }
}

function _opz_url($id = null) {
    if ($id !== null) {
        return OpzNavClass::getInstance()->get_data($id)['opz_url'];
    } else {
        return 'none';
    }
}
