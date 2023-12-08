<?php
!defined('EMLOG_ROOT') && exit('Access Denied!');

class OpzNavClass {

    //插件标识
    const ID = 'opz_nav';

    //实例
    private static $_instance;

    //数据库连接实例
    private $_db;

    public function __construct()
    {
        if ($this->_db !== null) {
            return $this->_db;
        }
        $this->_db = Database::getInstance();
        return $this->_db;
    }

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function add_article_field () {
        $id = Input::getIntVar('gid');
        $opz_data = $this->get_data($id);
        $opz_url = $opz_data['opz_url'] ?: 'https://blog.phpat.com';
        $plugin_url = BLOG_URL . 'content/plugins/opz_nav/';
        // http://localhost:3000/admin/plugin.php?plugin=em_stats
        echo '<script src="'.$plugin_url.'opz_nav.js"></script>';
        echo '<div style="font-size: 14px;
    margin: 2em 0;
    border: 1px dashed;
    border-radius: 6px;
    padding: 10px;">';
        echo '<p style="text-align: center;color: #2196F3;line-height: 3;">----导航站模版专有属性----</p>';
        echo '<div class="form-group">';
        echo '<div style="display: flex;justify-content: space-between">
<label for="opz_url">链接地址：<small class="text-muted">（用于链接型文章）</small></label> <span class="text-primary" style="cursor: pointer" id="get-link-info-btn">获取标题/ico</span></div>';
        echo "<input type='text' name='opz_url' id='opz_url' class='form-control' value='{$opz_url}' placeholder='http(s)://'>";
        echo '</div>';
        echo '</div>';

        # 临时：清洗数据
//        $sql = "select gid, link from " . DB_PREFIX . 'blog where link != ""';
//
//        $res = $this->_db->query($sql);
//        $data = [];
//        $prefix = DB_PREFIX;
//        while ($row = $res->fetch_assoc()) {
//            $data = serialize(['opz_url' => $row['link']]);
//            $sql = "INSERT INTO `{$prefix}opz_nav` (`gid`, `value`) VALUES ({$row['gid']}, '{$data}')";
//            $this->_db->query($sql);
//        }
//        var_dump($data[0]);
    }

    public function set_data($id, $data) {
        $sql = "select * from " . DB_PREFIX . "opz_nav where gid = {$id}";
        $res = $this->_db->query($sql);
        $prefix = DB_PREFIX;
        $data = serialize($data);

        if ($res->fetch_array()) {
            // 有则更新
            $sql = "UPDATE `{$prefix}opz_nav` SET `value` = '{$data}' WHERE `gid` = {$id}";
        } else {
            // 无则新增
            $sql = "INSERT INTO `{$prefix}opz_nav` (`gid`, `value`) VALUES ({$id}, '{$data}')";
        }
        $this->_db->query($sql);
    }

    public function get_data($id) {
        $sql = "select * from " . DB_PREFIX . "opz_nav where gid = {$id}";
        $res = $this->_db->query($sql);
        if ($data = $res->fetch_assoc()) {
            return unserialize($data['value']);
        } else {
            return [
                'opz_url' => ''
            ];
        }
    }

    public function save_article_field ($id) {
        $opz_url = Input::postStrVar('opz_url');
        $this->set_data($id, [
            'opz_url' => $opz_url
        ]);
    }
}