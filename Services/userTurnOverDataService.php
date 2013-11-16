<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-25
 * Time: 上午11:07
 * To change this template use File | Settings | File Templates.
 * 角色流失率
 */
class userTurnOverDataService extends  Service
{
     function userTurnOverDataService(){
          parent::__construct();
          $this -> table_userTurnOver = 'userturnover';
          $this -> db -> select_db('mmo2d_recordljzm');
     }

    public function lists($page,$condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $list = array();

        $date = $this->db -> timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";

        $list = $this->db->select("*")
                ->from("$this->table_userTurnOver")
                ->where("sid in ($server_ids) and $timecondition")
                ->order_by("date asc")
                ->limit($page->start,$page->limit,'date asc')
                ->get()
                ->result_objects();

        foreach($list as &$obj){
            $obj -> firstloadpercent = number_format($obj->firstloadpercent,2)*100 . '%';
            $obj -> loadallpercent = number_format($obj->loadallpercent,2)*100 . '%';
            $obj -> createpercent = number_format($obj->createpercent,2)*100 . '%';
            $obj -> secondloadpercent = number_format($obj->secondloadpercent,2)*100 . '%';
            $obj -> gamepercent = number_format($obj->gamepercent,2)*100 . '%';
            $obj -> firsttaskpercent = number_format($obj->firsttaskpercent,2)*100 . '%';
        }

        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        $sql = "select count(date) as num from $this->table_userTurnOver where sid in ($server_ids) and $timecondition ";
        $obj = $this -> db -> query($sql) -> result_object();
        return $obj->num;
    }

    public function total($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');
        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";

        $sql = "select
         sum(jumpnum) as jumpnum,sum(willcreatenum) as willcreatenum,sum(loginnum) as loginnum,sum(createnum) as createnum,sum(firsttasknum) as firsttasknum,sum(completetasknum) as completetasknum
         from $this->table_userTurnOver where sid in ($server_ids) and $timecondition";

        $obj = $this -> db -> query($sql) -> result_object();

        if(!is_null($obj -> jumpnum)){
            $obj -> firstloadpercent = empty($obj->jumpnum) ? 0 : number_format(($obj->jumpnum - $obj->willcreatenum)/$obj->jumpnum,2) * 100 . '%';
            $obj -> loadallpercent = empty($obj->jumpnum) ? 0 : number_format(($obj->jumpnum - $obj->loginnum)/$obj->jumpnum,2) * 100 . '%';
            $obj -> createpercent = empty($obj->willcreatenum) ? 0 : number_format(($obj->willcreatenum - $obj->createnum)/$obj->willcreatenum,2) * 100 . '%';
            $obj -> secondloadpercent = empty($obj->createnum) ? 0 : number_format(($obj->createnum - $obj->loginnum)/$obj->createnum,2) * 100 . '%';
            $obj -> gamepercent = empty($obj->loginnum) ? 0 : number_format(($obj->loginnum - $obj->firsttasknum)/$obj->loginnum,2) * 100 . '%';
            $obj -> firsttaskpercent = empty($obj->firsttasknum) ? 0 :  number_format(($obj->firsttasknum - $obj->completetasknum)/$obj->firsttasknum,2) * 100 . '%';
        }
        return $obj;
    }
}
