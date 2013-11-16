<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zyy
 * Date: 13-4-27
 * Time: 下午2:23
 * To change this template use File | Settings | File Templates.
 * 军衔日志
 *
 *
 */
class RankDailyService extends ServerDBChooser{
    private $rankid='90000012';//军衔id

    function RankDailyService(){
        $this->rank_record_table= $this->prefix_2.'record';
        $this->rank_user_table=$this -> prefix_1.'user';
        $this->rank_rankid_table=$this->prefix_2.'playerrank';
        $this->rank_static_table = $this->prefix_2.'rank';
    }
    public function num_rows($condition){
        $server = $condition->server;
        $this -> dbConnect($server,$server->dynamic_dbname);
        $consql = $this->getCondition($condition);
        $sql = "select count(a.id1) as num  from $this->rank_record_table a left join $this->rank_user_table b on  a.id1=b.id left join  $this->rank_rankid_table p on a.id1=p.pid $consql";
        return $this->db->query($sql)->result_object()->num;
    }
    public function lists($page,$condition){
        $server=$condition->server;
        $list=array();
        if(!empty($server)){
            $this->dbConnect($server,$server->dynamic_dbname);
            $consql=$this->getCondition($condition);
            $time = $this->db->datetime('a.time');

            $list = $this->db->select("a.id1,a.type,a.str as action,a.param2,a.param4,$time
                        as time,b.id,b.account_name,b.name,b.levels,b.yuanbao,p.rankid")
                        ->from("$this->rank_record_table a left join $this->rank_user_table b on a.id1=b.id LEFT JOIN $this->rank_rankid_table as p")
                        -> on("p.pid=a.id1")
                        -> where($consql)
                        -> limit($page->start,$page->limit,'a.time desc')
                        -> order_by('a.time desc')
                        -> get() -> result_objects();
        }

        //查询静态表
        $this -> db -> select_db('mmo2d_staticljzm');
        $static_ranks = $this -> db -> select("rankid,name") -> from($this->rank_static_table) -> get() -> result_objects();

        include BASEPATH . '/Common/event.php';

        foreach($list as &$obj){

            $rank_obj = fetch_object_by_key('rankid',$obj->rankid,$static_ranks);

            $obj->rankname = $rank_obj -> name;

            $obj->detail = empty($gameevent[$obj->param4]) ? '未知' : $gameevent[$obj->param4];

            if($obj->type==1){
                $obj->typename = '消耗';
                $obj->rankchange = '-'.$obj->param2;
            }
            else {
                $obj->typename = '获取';
                $obj->rankchange = '+'.$obj->param2;
            }

            $obj->servername = $server->name;
        }
        return $list;
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

        if(empty($sql))
            return " where a.param1 = $this->rankid";
        return $sql = " where a.param1 = $this->rankid and ".$sql;


    }
}