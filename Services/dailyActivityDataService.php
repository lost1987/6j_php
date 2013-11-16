<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-26
 * Time: 下午3:37
 * To change this template use File | Settings | File Templates.
 * 各类活动
 */
class DailyActivityDataService extends Service
{
    function DailyActivityDataService(){
        parent::__construct();
        $this -> table_dailyactivity = 'dailyactivity';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($page,$condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $list = array();

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";

        $list = $this->db->select("activityname,
                activitylevel,sum(loginlevel) as loginlevel,sum(jionactivityperson) as jionactivityperson,
                sum(jionactivitynum) as jionactivitynum,sum(completeactivitynum) as completeactivitynum")
               -> from($this->table_dailyactivity)
               -> where("sid in ($server_ids) and $timecondition" )
               -> group_by("activityname,activitylevel")
               -> limit($page->start,$page->limit,'activityname asc')
               -> get()
               -> result_objects();


        foreach($list as &$obj){
            $obj -> completeactivitypercent = $obj->jionactivitynum == 0 ? '0%' :  number_format($obj->completeactivitynum/$obj->jionactivitynum,4)*100 . '%';
            $obj -> loginpercent = $obj->loginlevel==0 ? '0%' :  number_format($obj->jionactivityperson/$obj->loginlevel,4)*100 . '%';
            $obj -> jionave = $obj->jionactivityperson == 0  ? '0' : number_format($obj->jionactivitynum/$obj->jionactivityperson,4);
        }

        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        $sql = "select activityname  from $this->table_dailyactivity where sid in ($server_ids) and $timecondition group by activityname";
        $obj = $this -> db -> query($sql) -> result_objects();
        return count($obj);
    }
}
