<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zyy
 * Date: 13-4-27
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 * 灵气日志
 */
class ReikDailyService extends ServerDBChooser{
    private $reikid='90000005';//灵气id
    function ReikDailyService(){
        $this->reiki_record_table= $this -> prefix_2 . 'record';
        $this->reiki_user_table=$this -> prefix_1 . 'user';

    }
    public function lists($page,$condition){
        $server=$condition->server;
        $list=array();
        if(!empty($server)){
            $this->dbConnect($server,$server->dynamic_dbname);
            $consql=$this->getCondition($condition);
            $time = $this->db->datetime('a.time');
            $sql="select * from (select row_number() over (order by a.time desc) as rownumber,
                     a.id1,a.type,a.str as action,a.param2,a.param4,$time as time,b.id,b.account_name,b.name,b.levels,b.profession,b.lingqi from $this->reiki_record_table a left join $this->reiki_user_table b on a.id1=b.id $consql )
                    as t where t.rownumber > $page->start and t.rownumber <= $page->limit";

            $list = $this->db->select("a.id1,a.type,a.str as action,a.param2,a.param4,$time as time,b.id,b.account_name,b.name,b.levels,b.profession,b.lingqi")
                            -> from("$this->reiki_record_table a left join $this->reiki_user_table b")
                            -> on('a.id1 = b.id')
                            -> where($consql)
                            -> limit($page->start,$page->limit,'a.time desc')
                            -> order_by('a.time desc')
                            -> get() -> result_objects();

            include BASEPATH . '/Common/event.php';

            foreach($list as &$obj){
                $obj->detail = empty($gameevent[$obj->param4]) ? '未知' : $gameevent[$obj->param4];

                if($obj->type==1){
                    $obj->typename = '消耗';
                    $obj->reikchange = '-'.$obj->param2;
                }
                else {
                    $obj->typename = '获取';
                    $obj->reikchange = '+'.$obj->param2;
                }

                $obj->servername = $server->name;
            }
        }
        return $list;
    }
    public function num_rows($condition){
        $server = $condition->server;
        $this -> dbConnect($server,$server->dynamic_dbname);
        $consql = $this->getCondition($condition);
        $sql = "select count(a.id1) as num  from $this->reiki_record_table a left join $this->reiki_user_table b on  a.id1=b.id $consql";
        return $this->db->query($sql)->result_object()->num;

    }
    public function getCondition($condition){

        $starttime = $condition->starttime;
        $endtime = $condition->endtime;
        $account_name = $condition -> account_name;
        $type = $condition -> type;
        $child_type = $condition -> child_type;
        $level_start = $condition -> level_start;
        $level_limit = $condition -> level_limit;
        $vip_start = $condition -> vip_start;
        $vip_limit = $condition -> vip_limit;

        $sql = '';
        if(!empty($starttime) && !empty($endtime)){
            $starttime .= ' 00:00:00';
            $endtime .= ' 23:59:59';
            $time = $this->db->cast('a.time');
            $cond1 = " $time >= '$starttime' and $time <= '$endtime'";
        }

        if(!empty($account_name)){
            $cond2 = " ( b.account_name like '$account_name%' or b.name like '$account_name%')";
        }

        if($type != -1){
            $cond3 = " a.type = $type ";
        }

        if(!empty($child_type)){
            $cond4 = " a.param4 = $child_type ";
        }

        if(!empty($level_limit) && !empty($level_start)){
            if($level_limit == $level_start){
                $cond5 = " b.levels = $level_limit ";
            }else{
                $cond5 = " (b.levels >= $level_start and b.levels <= $level_limit ) ";
            }
        }

        if(!empty($vip_start) && !empty($vip_limit)){
            if($vip_limit == $vip_start)
                $cond6 = " (b.mask0%100) = $vip_limit";
            else
                $cond6 = " ((b.mask0%100) >= $vip_start and (b.mask0%100) <= $vip_limit) ";
        }

        if(isset($cond1)){
            $sql .= $cond1;
        }

        if(isset($cond2)){
            if(!empty($sql)){
                $sql .= ' and '.$cond2;
            }else{
                $sql .= $cond2;
            }
        }

        if(isset($cond3)){
            if(!empty($sql)){
                $sql .= ' and '.$cond3;
            }else{
                $sql .= $cond3;
            }
        }

        if(isset($cond4)){
            if(!empty($sql)){
                $sql .= 'and '.$cond4;
            }else{
                $sql .= $cond4;
            }
        }

        if(isset($cond5)){
            if(!empty($sql)){
                $sql .= 'and '.$cond5;
            }else{
                $sql .= $cond5;
            }
        }

        if(isset($cond6)){
            if(!empty($sql)){
                $sql .= 'and '.$cond6;
            }else{
                $sql .= $cond6;
            }
        }


        if(empty($sql)){
            return " where a.param1 = $this->reikid";
        }
        return " where a.param1 = $this->reikid and ".$sql;

    }
}