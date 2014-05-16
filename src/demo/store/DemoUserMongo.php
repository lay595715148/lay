<?php
class DemoUserMongo extends MongoStore {
    public function __construct() {
        parent::__construct(new DemoUser());
    }
}
?>
