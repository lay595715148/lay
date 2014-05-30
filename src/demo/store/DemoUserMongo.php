<?php
namespace demo\store;

use lay\store\MongoStore;
use demo\model\DemoUser;

class DemoUserMongo extends MongoStore {
    public function __construct() {
        parent::__construct(new DemoUser());
    }
}
?>
