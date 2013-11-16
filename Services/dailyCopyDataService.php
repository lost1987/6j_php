<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-26
 * Time: 下午3:25
 * To change this template use File | Settings | File Templates.
 * 全部副本
 */
class DailyCopyDataService extends Service
{
    function DailyCopyDataService(){
        parent::__construct();
        $this -> table_dailycopy = 'dailycopy';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($page,$condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $list = array();

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";

       /* $sql = "select * from (select row_number() over (order by copyname asc) as rownumber,
                copyname,copylevel,sum(loginlevel) as loginlevel,sum(jioncopyperson) as jioncopyperson,
                sum(jioncopynum) as jioncopynum,sum(completecopynum) as completecopynum
                 from $this->table_dailycopy where sid in ($server_ids) and $timecondition group by copyname,copylevel) as t";


        $list = $this -> db -> query($sql) -> result_objects();*/

        $list = $this->db->select("copyname,copylevel,sum(loginlevel) as loginlevel,sum(jioncopyperson) as jioncopyperson,
                                    sum(jioncopynum) as jioncopynum,sum(completecopynum) as completecopynum")
                         ->from("$this->table_dailycopy")
                         ->where(" sid in ($server_ids) and $timecondition")
                         ->group_by('copyname,copylevel')
                         ->order_by('copyname asc')
                         ->get()
                         ->result_objects();

        foreach($list as &$obj){
            $obj -> completecopypercent = $obj -> jioncopynum == 0 ? '0%' : number_format($obj->completecopynum/$obj->jioncopynum,4)*100 . '%';
            $obj -> loginpercent = $obj -> loginlevel == 0 ? '0%' : number_format($obj->jioncopyperson/$obj->loginlevel,4)*100 . '%';
            $obj -> jionave = $obj->jioncopyperson == 0 ? '0' : number_format($obj->jioncopynum/$obj->jioncopyperson,4);
        }

        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        $sql = "select copyname from $this->table_dailycopy where sid in ($server_ids) and $timecondition group by copyname,copylevel";
        $obj = $this -> db -> query($sql) -> result_objects();
        return count($obj);
    }
}
