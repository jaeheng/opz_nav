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
        $opz_url = $opz_data['opz_url'];
        echo '<h3 style="font-size: 14px;
    color: #2196F3;
    text-align: center;
    margin: 2em 0;
    border: 1px dashed;
    padding: 10px;">👇emlog导航站模版专有属性👇</h3>';
        echo '<div class="form-group">';
        echo '<label for="opz_url">链接地址：<small class="text-muted">（用于链接类型分类文章）</small></label>';
        echo "<input type='text' name='opz_url' id='opz_url' class='form-control' value='{$opz_url}' placeholder='http(s)://'>";
        echo '</div>';
    }

    public function set_data($id, $data) {
        $sql = "select * from " . DB_PREFIX . "opz_nav where gid = {$id}";
        $res = $this->_db->query($sql);
        $prefix = DB_PREFIX;
        $data = serialize($data);
        if ($res->fetch_array()) {
            // 有则更新
            $sql = "UPDATE `{$prefix}opz_nav` SET `value` = '{$data}' WHERE `id` = {$id}";
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