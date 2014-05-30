<?php
use lay\store\MongoStore;

class DemoUserMongo extends MongoStore {
    public function __construct() {
        parent::__construct(new DemoUser());
    }
}
?>
