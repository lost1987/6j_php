<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-25
 * Time: 上午11:05
 * To change this template use File | Settings | File Templates.
 * 充值玩家排名
 */
class RechargeOrderService extends ServerDBChooser
{

    private $_profession = array('降魔','御法','牧云');

    function RechargeOrderService(){
        $this->table_record = $this->prefix_2.'record';
        $this->table_player = $this->prefix_1.'user';
    }

    public function lists($condition){
        set_time_limit(0);
        $servers = $condition->servers;
        $starttime = $condition -> starttime.' 00:00:00';
        $endtime = $condition -> endtime .' 23:59:59';
        $list = array();

        $num_per_server = 50/count($servers) < 1 ? 1 : floor(50/count($servers));
        if(count($servers)>0){
            foreach($servers as $server){
                 $this->dbConnect($server,$server->dynamic_dbname);


                //查询玩家充值的元宝
            /*    $sql = "
                select a.*,b.account_name,b.name,b.levels,b.profession from(
                select * from (select row_number() over (order by sum(param2) desc) as rownumber, id1 as pid,sum(param2) as recharge_yuanbao from $this->table_record  where type=0 and param1 = 90000001 and param4 = 44 and id2 <> null and  time > '$starttime' and time < '$endtime' and left(str2,6) <> 'REWARD' group by id1 )as t where t.rownumber > 0 and t.rownumber <= 50
                ) as a left join $this->table_player b on a.pid = b.id";

                $templist_recharge = $this -> db -> query($sql) -> result_objects();*/
                //AR模式
                $sql = $this->db->select("id1 as pid,sum(param2) as recharge_yuanbao")
                                   ->from("$this->table_record")
                                   ->where("type=0 and param1 = 90000001 and param4 = 44 and id2=0 and  time > '$starttime' and time < '$endtime' and left(str2,6) <> 'REWARD'")
                                   ->group_by('id1')
                                   ->order_by('sum(param2) desc')
                                   ->limit(0,$num_per_server,'sum(param2) desc')
                                   ->fetch();

                $templist_recharge = $this->db->select("a.*,b.account_name,b.name,b.levels,b.profession")
                                                ->from("($sql) as a , $this->table_player as b")
                                                ->where("a.pid = b.id")
                                                ->get()
                                                ->result_objects();


                $pids = array();
                foreach($templist_recharge as $recharge){
                     $pids[] = $recharge->pid;
                }
                $pids = implode(',',$pids);

                $templist_used_yuanbao = $this->db->select("sum(param2) as used_yuanbao,id1 as pid ")
                                                        -> from($this->table_record)
                                                        -> where("type=1 AND param1=90000001 and time>'$starttime' and time < '$endtime' and id1 in ($pids)")
                                                        ->group_by('id1')
                                                        -> get()
                                                        -> result_objects();

                $templist_unrecharge = $this->db->select("sum(param2) as unrecharge_yuanbao,id1 as pid")
                                                        ->from($this->table_record)
                                                        ->where("type = 0 and param4 <> 44 and str2 IS NULL and param1=90000001  and time >'$starttime' and time < '$endtime' and id1 in ($pids)")
                                                        ->group_by('id1')
                                                        -> get()
                                                        -> result_objects();

                $templist_shengyu = $this->db->select("yuanbao,id as pid")
                                                ->from($this->table_player)
                                                ->where("id in ($pids)")
                                                ->get()
                                                ->result_objects();


                /*$templist_reward = $this->db->select("sum(param2) as reward_yuanbao,id1 as pid")
                                                ->from($this->table_record)
                                                ->where("type=0 and id2=0 and param4=44 and left(str2,6) = 'REWARD' and param1=90000001 and time >'$starttime' and time < '$endtime' and id1 in ($pids)")
                                                ->group_by('id1')
                                                -> get()
                                                -> result_objects();*/

                foreach($templist_recharge as $recharge){

                    foreach($templist_used_yuanbao as $used_yuanbao){
                        if($recharge -> pid == $used_yuanbao->pid){
                            $recharge->used_yuanbao = $used_yuanbao->used_yuanbao ;
                            break;
                        }else{
                             $recharge->used_yuanbao = 0;
                        }
                    }

                    foreach($templist_unrecharge as $unrecharge){
                         if($recharge -> pid == $unrecharge -> pid){
                             $recharge->unrecharge_yuanbao =  $unrecharge->unrecharge_yuanbao;
                             break;
                         }else{
                             $recharge->unrecharge_yuanbao = 0;
                         }
                    }

                    foreach($templist_shengyu as $sy){
                        if($recharge -> pid == $sy -> pid){
                            $recharge->shengyu_yuanbao =  $sy->yuanbao;
                            break;
                        }else{
                            $recharge->shengyu_yuanbao = 0;
                        }
                    }

                    /*foreach($templist_reward as $reward){
                        if($recharge -> pid == $reward -> pid){
                            $recharge->reward_yuanbao =  $reward->reward_yuanbao;
                            break;
                        }else{
                            $recharge->reward_yuanbao = 0;
                        }
                    }*/

                    $recharge -> servername = $server->name;
                    $recharge -> profession = $this -> _profession[$recharge->profession];
                   // $recharge -> shengyu_yuanbao = ( $recharge -> unrecharge_yuanbao + $recharge -> recharge_yuanbao + $recharge->reward_yuanbao) - $recharge -> used_yuanbao;
                    $list[] = $recharge;
                }

            }


            for($i=0 ; $i < count($list) - 1; $i++){
                for($j = 0; $j < count($list)-$i-1; $j++){
                     if($list[$j] ->recharge_yuanbao < $list[$j+1] ->recharge_yuanbao){
                            $tmp = $list[$j];
                            $list[$j] = $list[$j+1];
                            $list[$j+1] = $tmp;
                     }
                }
            }

            $end = count($list) > 50 ? 50 : count($list);
            $list = array_slice($list,0,$end);

        }
        return $list;
    }

    public function getCondition($condition){}


    public function num_rows($condition){
        $servers = $condition->servers;
        $starttime = $condition -> starttime.' 00:00:00';
        $endtime = $condition -> endtime .' 23:59:59';
        $nums = 0;
        if(count($servers)>0){
            foreach($servers as $server){
                $this->dbConnect($server,$server->dynamic_dbname);
                $sql = "select a.id1 as pid from $this->table_record a  where a.type=0 and a.param1 = 90000001 and a.time > '$starttime' and a.time < '$endtime'  group by a.id1";
                $templist = $this -> db -> query($sql) -> result_objects();
                $nums+=count($templist);
            }
        }
        return $nums;
    }

}
