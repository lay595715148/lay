<?php
namespace lay\entity;

use \lay\core\Entity;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 结构化响应返回数据对象
 * @author Lay Li
 * @property boolean $success
 * @property string $action
 * @property string $content
 * @property int $code
 */
class Response extends Entity {
    public function __construct() {
        parent::__construct(array(
            'success' => false,
            'action' => '',
            'content' => '',
            'code' => 0
        ));
    }
    protected function rules() {
        return  array(
            'success' => Bean::PROPETYPE_BOOLEAN,
            'action' => Bean::PROPETYPE_STRING,
            'code' => Bean::PROPETYPE_INTEGER
        );
    }
    public function summary() {
        return array(
            'success' => 'success',
            'action' => 'action',
            'content' => 'content',
            'code' => 'code'
        );
    }
    public function toSummary() {
        return $this->toArray();
    }
}
?>
