<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 业务逻辑处理类
 * 
 * @author Lay Li
 */
abstract class Service {
    /**
     * 为主表（或其他）数据模型的数据访问对象
     * 
     * @var Store
     */
    protected $store;
    public function __construct($store) {
        $this->store = $store;
    }
    /**
     * 获取某条记录
     * 
     * @param int|string $id
     *            the ID
     */
    public function get($id) {
        return $this->store->get($id);
    }
    /**
     * 增加一条记录
     * 
     * @param array $info
     *            information array
     */
    public function add(array $info) {
        return $this->store->add($info);
    }
    /**
     * 删除某条记录
     * 
     * @param int|string $id
     *            the ID
     */
    public function del($id) {
        return $this->store->del($id);
    }
    /**
     * 更新某条记录
     * 
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public function upd($id, array $info) {
        return $this->store->upd($id, $info);
    }
}
?>
