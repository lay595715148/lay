<?php
include_once('./bootstrap.origin.php');
//echo '<pre>';print_r($_SERVER);echo '</pre>';exit;
use cn\laysoft\laywork\core\Criteria;
use cn\laysoft\laywork\core\Criterion;
use cn\laysoft\laywork\core\Action;
use cn\laysoft\laywork\core\Store;
use cn\laysoft\laywork\core\Mysql;

//Laywork::initialize(true);
//Layload::initialize(true);

Laywork::configure(array('actions.index.classname'=>'TestAction'), false);

class TestAction extends Action {
    public function launch() {
        //echo 'Hello world';
        $my = new Criterion();
        $my->name = 'id';
        $my->operator = Criterion::OPERATOR_GREATER_EQUAL;
        $my->value = 23;
        $my->combine = Criterion::COMBINE_BRACKET_AND;
        $yu = new Criterion();
        $yu->name = 'name';
        $yu->operator = Criterion::OPERATOR_EQUAL;
        $yu->value = 'ob';
        $yu->combine = Criterion::COMBINE_BRACKET_AND;
        $we = new Criterion();
        $we->name = 'type';
        $we->operator = Criterion::OPERATOR_EQUAL;
        $we->value = '1';
        $we->combine = Criterion::COMBINE_BRACKET_OR;
        $te = new Criterion();
        $te->name = 'type';
        $te->operator = Criterion::OPERATOR_EQUAL;
        $te->value = '2';
        $te->combine = Criterion::COMBINE_BRACKET_OR;
        $m1 = new Criteria();
        $m1->push(array($my, $yu));
        //$str = $m->toCriteria();
        $m2 = new Criteria();
        $m2->push(array($we, $te));
        //$str = $m->toCriteria();
        $str = Criteria::combine(array($m1,$m2));
        //$str = Criterion::combine($my, "`name` = 'ob'");

        //echo '<pre>';var_dump($str);echo '</pre>';

        $filter = 'id:>=21&&name:=ob&type:=1||type:=2';
        $pattern = '/([&|\|]{0,1})([^&\|]{1,})/';
        $ret = preg_split('/(&|\|)\1+/', $filter);
        Debugger::debug($ret);

        $item = '(!&alsk:=00"s&&ks:<12&(|ks:>=20&&as:{}16,18';
        $ret = Criterion::parse($item);
        $ret = Criterion::combine($ret);
        //echo "<pre>Criteria::parse('{$item}')\n";print_r($ret);echo '</pre>';
        Debugger::debug("Criteria::parse('{$item}')\n");
        Debugger::debug($ret);
        $service = $this->service('out');
        Debugger::debug(Store::newInstance('mysql'));
        $userid = 'ssoAdmin';
        $sql = "SELECT `uid`, `mailid` FROM `laysoft`.`uid_mailid` WHERE `uid` = '".mysql_escape_string($userid)."'";
        Store::newInstance('mysql')->query($sql);
        $row = Store::newInstance('mysql')->toArray(1);
        Debugger::debug($row);
        //Debugger::debug(Laywork::get());
        new MyClass();
    }
}

Laywork::start();
?>