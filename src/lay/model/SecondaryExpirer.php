<?php
/**
 * 可设置过期时间的模型对象
 * @author Lay Li
 */
namespace lay\model;

use lay\core\Model;

/**
 * 可设置过期时间的拥有第二键的模型对象
 * @author Lay Li
 */
abstract class SecondaryExpirer extends Model implements Expireable, Secondary {
    
}
?>
