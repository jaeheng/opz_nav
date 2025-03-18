<?php
!defined('EMLOG_ROOT') && exit('Access Denied!');

class OpzNavClass
{

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

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function add_article_field()
    {
        $id = Input::getIntVar('gid');
        $opz_data = $this->get_data($id);
        $opz_url = $opz_data['opz_url'];
        $views = $opz_data['views'];
        $plugin_url = BLOG_URL . 'content/plugins/opz_nav/';
        echo '<script src="' . $plugin_url . 'opz_nav.js"></script>';
        echo '<div style="font-size: 14px;
    margin: 2em 0;
    border: 1px dashed;
    border-radius: 6px;
    padding: 10px;">';
        echo '<p style="text-align: center;color: #2196F3;line-height: 3;">----网址导航插件----</p>';
        echo '<div class="form-group">';
        echo '<div style="display: flex;justify-content: space-between">
<label for="opz_url">链接地址：<small class="text-muted">（用于链接型文章）</small></label><span>点击量: ' . $views . '</span></div>';
        echo "<input type='text' name='opz_url' id='opz_url' class='form-control' value='{$opz_url}' placeholder='http(s)://'>";
        echo "<p style='font-size: 12px;margin-top: 5px;'>Tips: 获取标题+ico功能受网络限制，可能不成功</p>";
        echo "<p style='margin-top: 5px;display: flex;justify-content: space-between;'>" . '<span class="btn btn-primary" id="get-link-info-btn">获取标题+ico</span>';
        echo '<span class="btn btn-light" id="visit-link-btn">访问该链接 ></span>';
        echo "</p>";
        echo '</div>';
        echo '</div>';
    }

    public function set_data($id, $data)
    {
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

    public function get_data($id)
    {
        $sql = "select * from " . DB_PREFIX . "opz_nav where gid = {$id}";
        $res = $this->_db->query($sql);
        if ($data = $res->fetch_assoc()) {
            $data = unserialize($data['value']);

            if (!is_array($data)) {
                return [
                    'opz_url' => '',
                    'views' => 0
                ];
            }

            // 保证获取的数据里有opz_url和views字段
            if (!isset($data['opz_url'])) {
                $data['opz_url'] = '';
            }
            if (!isset($data['views'])) {
                $data['views'] = 0;
            }

            return $data;
        } else {
            return [
                'opz_url' => '',
                'views' => 0
            ];
        }
    }

    public function save_article_field($id)
    {
        $opz_url = Input::postStrVar('opz_url');
        $sql = "select * from " . DB_PREFIX . "opz_nav where gid = {$id}";
        $res = $this->_db->query($sql)->fetch_assoc();

        if (!$res) {
            $this->set_data($id, [
                'opz_url' => $opz_url,
                'views' => 0
            ]);
        } else {
            $data = unserialize($res['value']);

            $this->set_data($id, [
                'opz_url' => $opz_url,
                'views' => $data['opz_url'] === $opz_url ? $data['views'] : 0
            ]);
        }
    }

    public function increase_views($url)
    {
        $sql = "select * from " . DB_PREFIX . "opz_nav where value like '%\"{$url}\"%'";

        $res = $this->_db->query($sql)->fetch_assoc();

        if ($res) {
            $data = unserialize($res['value']);

            $this->set_data($res['gid'], [
                'opz_url' => $data['opz_url'],
                'views' => $data['views'] + 1
            ]);

            // 浏览量+1
            $logModel = new Log_Model();
            $logModel->updateViewCount($res['gid']);

            // 记录最近访问
            $recently_link = isset($_COOKIE['recently_link']) ? json_decode($_COOKIE['recently_link'], true) : array();
            array_unshift($recently_link, $res['gid']);
            $recently_link = array_unique($recently_link);
            $recently_link = array_slice($recently_link, 0, _g('recently_link_count') ?: 8);
            setcookie('recently_link', json_encode($recently_link), time() + 2592000, '/'); // 有效期一个月
        }
    }

    public function all_gid()
    {
        $sql = "select gid from " . DB_PREFIX . "opz_nav";

        $res = $this->_db->query($sql)->fetch_all();

        return array_column($res, 0);
    }
}