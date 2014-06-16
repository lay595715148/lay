<?php
/**
 * 可设置自增涨键和过期时间的模型对象
 * @author Lay Li
 */
namespace lay\model;

use lay\core\Model;
use lay\core\I_Expireable;
use lay\core\I_Increment;

/**
 * 可设置自增涨键和过期时间的模型对象
 * @author Lay Li
 */
abstract class ExpireIncrementer extends Model implements I_Expireable, I_Increment {
    
}
?>
