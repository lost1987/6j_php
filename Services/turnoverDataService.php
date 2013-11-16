<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-21
 * Time: 下午4:24
 * To change this template use File | Settings | File Templates.
 * 时间流失率
 */
class TurnoverDataService extends Service
{
    function TurnoverDataService(){
        parent::__construct();
        $this -> table_turnover = 'turnoverdata';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($page,$condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');

        $list = array();
        $date = $this->db->timestamp('date');
        $datetime = $this->db->datetime('date',10,120);
        if(substr($starttime,0,10) == substr($endtime,0,10)){
            $date_time_array = $this -> getDayTime($condition->starttime.' 00:00:00');

            $sql = '';
            $this->db->select("*");
            foreach($date_time_array as $datetime){
                $timecondition =   "$date >= '$starttime' and $date <= '$datetime')";
                $sql .= "select
                     sum(turnover24) as turnover24,avg(turnover24percent) as turnover24percent,
                     sum(turnover72) as turnover72,avg(turnover72percent) as turnover72percent,
                     sum(turnover168) as turnover168,avg(turnover168percent) as turnover168percent
                     from $this->table_turnover where sid in ($server_ids) and $timecondition union all ";
            }
            $sql = substr($sql,0,strlen($sql) - 11);
            $this->db->from( "($sql) as u" )
                ->order_by('u.turnover24 asc')
                ->limit($page->start,$page->limit,'u.turnover24 asc');
        }else{
            $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
            $this->db->select("$datetime as date ,sum(turnover24) as turnover24,
                                avg(turnover24percent) as turnover24percent,
                                 sum(turnover72) as turnover72,avg(turnover72percent) as turnover72percent,
                                    sum(turnover168) as turnover168,
                                    avg(turnover168percent) as turnover168percent")
                ->from($this->table_turnover)
                ->where("sid in ($server_ids) and $timecondition")
                ->group_by($datetime)
                ->order_by("$datetime desc")
                ->limit($page->start,$page->limit,"$datetime desc");
        }

        $list = $this -> db -> get() -> result_objects();

        $flag = $page->start;
        foreach($list as &$obj){
            if(isset($date_time_array)){
                $curdate = strtotime($date_time_array[$flag]);
                //计算时间差 秒
                $hour = 60 * 60;
                $start = strtotime(substr($starttime,0,10).' 00:00:00');
                $cha = $curdate - $start;
                if($cha < $hour)$obj->date = ceil($cha/60).'分钟';
                else $obj->date = ceil($cha/$hour).'小时';
                $flag++;
            }
            $obj -> turnover24percent = number_format($obj->turnover24percent,2)*100 . '%';
            $obj -> turnover72percent = number_format($obj->turnover72percent,2)*100 . '%';
            $obj -> turnover168percent = number_format($obj->turnover168percent,2)*100 . '%';
        }

        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $starttime = $condition->starttime.' 00:00:00';
        $endtime = $condition->endtime.' 23:59:59';

        if(substr($starttime,0,10) == substr($endtime,0,10)){
            return count($this -> getDayTime($starttime));
        }else{
            $date = $this->db->timestamp('date');
            $starttime = strtotime($condition->starttime.' 00:00:00');
            $endtime = strtotime($condition->endtime.' 23:59:59');
            $timecondition = " $date  >= '$starttime' and $date <= '$endtime' ";
            $sql = "select count(  distinct   (date )   ) as num from $this->table_turnover where sid in ($server_ids) and $timecondition";
            $obj = $this -> db -> query($sql) -> result_object();
            if(!empty($obj))
            return $obj -> num;
            else
            return 0;
        }
    }

    public function total($condition){
        $server_ids = $condition -> server_ids;
        $sql = "select sum(turnover24) as turnover24 , sum(turnover72) as turnover72
           , sum(turnover168) as turnover168 from $this->table_turnover where sid in ($server_ids)";
        return $this->db->query($sql)->result_object() ;
    }

    private function getDayTime($date){
            $sourcedate = substr($date,0,10);
            $date_time_array = array();
            $date_time_array[] = $sourcedate.' 00:10:00';
            $date_time_array[] = $sourcedate.' 00:20:00';
            $date_time_array[] = $sourcedate.' 00:30:00';
            $date_time_array[] = $sourcedate.' 00:40:00';
            $date_time_array[] = $sourcedate.' 00:50:00';

           $hour = 60 * 60;
           for($i=1;$i<24;$i++){
               $sourcetime = strtotime($sourcedate.' 00:00:00');
               $tempdate =$sourcetime+$hour*$i;
               $date_time_array[] = $tempdate;
           }

           return $date_time_array;

    }
}

/******
 *
 * select * from (select row_number() over (order by u.turnover24 desc) as rownumber ,* from

(	 select
sum(turnover24) as turnover24,sum(turnover24percent) as turnover24percent,
sum(turnover72) as turnover72,sum(turnover72percent) as turnover72percent,
sum(turnover168) as turnover168,sum(turnover168percent) as turnover168percent
from TurnoverData where sid in (6,7,8,9,10) and
(cast(date as datetime) >= '2013-03-15 00:00:00' and cast(date as datetime) <= '2013-03-15 00:30:00') union all
select
sum(turnover24) as turnover24,sum(turnover24percent) as turnover24percent,
sum(turnover72) as turnover72,sum(turnover72percent) as turnover72percent,
sum(turnover168) as turnover168,sum(turnover168percent) as turnover168percent
from TurnoverData where sid in (6,7,8,9,10) and
(cast(date as datetime) >= '2013-03-15 00:00:00' and cast(date as datetime) <= '2013-03-15 14:00:20')
) as u



) as t where t.rownumber > 0 and t.rownumber <= 18
 */