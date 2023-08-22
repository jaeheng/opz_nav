<?php
!defined('EMLOG_ROOT') && exit('Access Denied!');

// 插件开启时调用，可用于初始化配置
function callback_init()
{
    // 生成模版配套的数据表
    $prefix = DB_PREFIX;
    $db = Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}opz_nav` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `gid` int NOT NULL COMMENT '文章ID',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gid` (`gid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->query($sql);
}

// 插件删除时调用，可用于数据清理
function callback_rm()
{
    // 删除模版配套的数据表
    // 生成模版配套的数据表
    $prefix = DB_PREFIX;
    $db = Database::getInstance();
    $sql = "DROP TABLE IF EXISTS `{$prefix}opz_nav`";
    $db->query($sql);
}

// 插件更新时调用，可用于数据库变更等
function callback_up()
{
}
