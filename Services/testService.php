<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-20
 * Time: 下午9:08
 * To change this template use File | Settings | File Templates.
 */
class TestService
{
    public function lists($null){
        $list = array();
        for($i = 0; $i < 20; $i++){
            $o = new stdClass();
            $o -> date = '2013-04-01 12:00:00';
            $o -> registernum = 40;
            $o -> createnum = 30;
            $list[] = $o;
        }
        return $list;
    }
}
