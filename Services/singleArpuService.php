<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-22
 * Time: 上午9:50
 * To change this template use File | Settings | File Templates.
 * 单系统Arpu
 */
class SingleArpuService extends ServerDBChooser
{

    function singleArpuService(){
        $this -> table_record = $this->prefix_2.'record';
    }

    public function lists($page,$condition){
        $servers = $condition -> servers;
        $starttime = $condition -> starttime.' 00:00:00';
        $endtime = $condition -> endtime.' 23:59:59';

        if(count($servers) > 0){
            $slist = array();
            $list = array();
            $rechargenum = 0;//总充值人数
            foreach($servers as $server){
                $this->dbConnect($server,$server->dynamic_dbname);
                $sql = "select param4 as eventtype,count(param4) as offernum,sum(param2) as offer,str from $this->table_record where type=1 and param1 = 90000001 and time > '$starttime' and time < '$endtime'  group by param4,str";
                $sarpu_list = $this->db->query($sql)->result_objects();
                $slist[] = $sarpu_list;

                //查询时间段内 该服务器所有的充值人数
                $sql = "select count(distinct(id1)) as rechargenum from $this->table_record where param4=44 and  time > '$starttime' and time < '$endtime' and left(str2,6) <> 'REWARD'";
                $rechargenum += $this->db->query($sql)->result_object()->rechargenum;
            }

            $yuanbao_total = 0;
            foreach($slist as $sarpu_list){
                foreach($sarpu_list as $sarpu){
                    $yuanbao_total += $sarpu -> offer;
                    if(array_key_exists($sarpu->eventtype,$list)){
                        $list[$sarpu->eventtype]->offer += $sarpu->offer;
                        $list[$sarpu->eventtype]->offernum += $sarpu->offernum;
                    }else{
                        $list[$sarpu->eventtype] = $sarpu;
                    }
                }
            }

            $return_array = array();
            $flag = 0;
            foreach($list as $k=>$v){
                    $v -> offer_percent = number_format($v->offer/$yuanbao_total,6) * 100 .'%';
                    if($rechargenum==0)$v->arpu=0;
                    else $v -> arpu = number_format($v->offer/10/$rechargenum,2);
                    $return_array[] = $v;
            }
            return $return_array;
        }

        return null;

    }

    public function num_rows($condition){
        $starttime = $condition -> starttime;
        $endtime = $condition -> endtime;
        $servers = $condition->servers;
        if(count($servers) > 0){
            $slist = array();
            $list = array();
            foreach($servers as $server){
                $this->dbConnect($server,$server->dynamic_dbname);
                $sql = "select param4 as eventtype,sum(param2) as offer,str from $this->table_record where type=1 and param1 = 90000001 and time > '$starttime' and time < '$endtime' group by param4,str";
                $sarpu_list = $this->db->query($sql)->result_objects();
                $slist[] = $sarpu_list;
            }


            foreach($slist as $sarpu_list){
                foreach($sarpu_list as $sarpu){
                    if(array_key_exists($sarpu->eventtype,$list)){
                        $list[$sarpu->eventtype]->offer += $sarpu->offer;
                    }else{
                        $list[$sarpu->eventtype] = $sarpu;
                    }
                }
            }
             return count($list);
         }
         return 0;
    }

    public function getCondition($condition){}

}
