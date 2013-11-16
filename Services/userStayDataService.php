<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-26
 * Time: 上午10:34
 * To change this template use File | Settings | File Templates.
 * 玩家留存率
 */
class UserStayDataService extends Service
{
    function UserStayDataService(){
        parent::__construct();
        $this -> table_userStay = 'userstay';
        $this -> table_servers = DB_PREFIX.'servers';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($page,$condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime);
        $endtime = strtotime($condition->endtime);

        $list = array();

        $date = $this->db->timestamp('date');
        if($starttime == $endtime){
            $timecondition = " $date='$starttime' ";
        }else{
            $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        }

        $list = $this -> db -> select("*") -> from($this->table_userStay)
            ->where("sid in ($server_ids) and $timecondition")
            ->order_by("date desc")
            ->limit($page->start,$page->limit,'date desc')
            -> get() -> result_objects();

        $this -> db -> close();

        $tempDB = new DB();
        $tempDB -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
        $tempDB -> select_db('mmo2d_admin');
        $sql = "select id,name from $this->table_servers where id in ($server_ids)";
        $servers = $tempDB -> query($sql) -> result_objects();

        foreach($list as &$obj){
            $obj -> day1percent = empty($obj->createnum) ? 'N/A' : number_format($obj->day1num/$obj->createnum,2)*100 . '%';
            $obj -> day2percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day2num/$obj->createnum,2)*100 . '%';
            $obj -> day3percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day3num/$obj->createnum,2)*100 . '%';
            $obj -> day4percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day4num/$obj->createnum,2)*100 . '%';
            $obj -> day5percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day5num/$obj->createnum,2)*100 . '%';
            $obj -> day6percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day6num/$obj->createnum,2)*100 . '%';
            $obj -> day7percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day7num/$obj->createnum,2)*100 . '%';
            $obj -> day14percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day14num/$obj->createnum,2)*100 . '%';
            $obj -> day30percent = empty($obj->createnum) ? 'N/A' :number_format($obj->day30num/$obj->createnum,2)*100 . '%';
            foreach($servers as $server){
                if($server->id == $obj->sid){
                    $obj->server = $server->name;
                    break;
                }
            }
        }
        $tempDB -> close();

        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime);
        $endtime = strtotime($condition->endtime);

        $date = $this->db->timestamp('date');
        if($starttime == $endtime){
            $timecondition = " $date='$starttime' ";
        }else{
            $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        }
        $sql = "select count(date) as num from $this->table_userStay where sid in ($server_ids) and $timecondition ";
        $obj = $this -> db -> query($sql) -> result_object();
        return $obj->num;
    }

    public function total($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime);
        $endtime = strtotime($condition->endtime);

        $date = $this->db->timestamp('date');
        if($starttime == $endtime){
            $timecondition = " $date='$starttime' ";
        }else{
            $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        }

        $sql = "select
         sum(createnum) as createnum,sum(day1num) as day1num,sum(day2num) as day2num,
         sum(day3num) as day3num,sum(day4num) as day4num,sum(day5num) as day5num,sum(day6num) as day6num
         ,sum(day7num) as day7num,sum(day14num) as day14num,sum(day30num) as day30num
         from $this->table_userStay where sid in ($server_ids) and $timecondition";

        $obj = $this -> db -> query($sql) -> result_object();
        if(!is_null($obj->day1num)){
            $obj -> day1percent = number_format($obj->day1num/$obj->createnum,2)*100 . '%';
            $obj -> day2percent = number_format($obj->day2num/$obj->createnum,2)*100 . '%';
            $obj -> day3percent = number_format($obj->day3num/$obj->createnum,2)*100 . '%';
            $obj -> day4percent = number_format($obj->day4num/$obj->createnum,2)*100 . '%';
            $obj -> day5percent = number_format($obj->day5num/$obj->createnum,2)*100 . '%';
            $obj -> day6percent = number_format($obj->day6num/$obj->createnum,2)*100 . '%';
            $obj -> day7percent = number_format($obj->day7num/$obj->createnum,2)*100 . '%';
            $obj -> day14percent = number_format($obj->day14num/$obj->createnum,2)*100 . '%';
            $obj -> day30percent = number_format($obj->day30num/$obj->createnum,2)*100 . '%';
        }
        return $obj;
    }
}
