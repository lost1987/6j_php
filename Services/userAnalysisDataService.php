<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-9-22
 * Time: 下午1:31
 * To change this template use File | Settings | File Templates.
 * 用户分析--太他妈复杂了,赔我脑细胞
 */

class userAnalysisDataService extends ServerDBChooser{

    function userAnalysisDataService(){
        $this->table_user = $this->prefix_1.'user';
        $this->table_record = $this->prefix_2.'record';
    }

    public function lists($condition){
        set_time_limit(0);
        $server_ids = $condition->server_ids;
        $starttime = $condition->starttime;
        $endtime = $condition->endtime;
        $dateArray = timeTickArrayPoint($starttime,$endtime,60*60*24);
        $starttimestamp = strtotime($starttime);
        $endtimestamp = strtotime($endtime.' 23:59:59');

        $servers = $this -> getServers($server_ids);
        $list = array();
        foreach($servers as $server){
            $this->dbConnect($server,$server->dynamic_dbname);
            //查询时间段内的总注册UID
            $totalnewusers = $this->db -> select("id,unix_timestamp(left(createdate,10)) as date")
                            -> from ($this->table_user)
                            -> where("unix_timestamp(left(createdate,10)) >= $starttimestamp and unix_timestamp(left(createdate,10)) <= $endtimestamp")
                            -> get()->result_objects();
            if(count($totalnewusers) == 0)return array();
            //把时间归类 ,按日期手动分组
            $totalnewusers = $this->make_group($totalnewusers,$dateArray);

            //查询时间段内的总用户充值UID 注意这里的pid 会有重复值 后面使用array_unique来去除
            $totalrechargeusers = $this->db->select("id1 as id,unix_timestamp(left(time,10)) as date")
                                    -> from($this->table_record)
                                    -> where("unix_timestamp(left(time,10)) >= $starttimestamp and unix_timestamp(left(time,10)) <= $endtimestamp and
                                     param4 = 44 and id2=0 and param1=90000001")
                                    ->get() -> result_objects();
            $totalrechargeusers = $this->make_group($totalrechargeusers,$dateArray);

            $obj = new stdClass();
            $obj->datedesp= "新增玩家";
            $obj->row = -1;
            for($i=0;$i<30;$i++){//纵向
                    $element = new stdClass();
                    $element->datedesp = '第'.$i.'天';
                    $element->row = $i;
                    $flag = 0;
                    foreach($dateArray as $date){//横向
                            $date_key_current = date('Y-m-d',$date+$i*60*60*24);//当前日期的纵向第$i个格子的key
                            $date_key_source = date('Y-m-d',$date);
                            $element->{'createnum'.$flag} = $createnum = count($totalnewusers[$date_key_source]->pids);
                            if($createnum != 0 && ($date+$i*60*60*24) <= $endtimestamp){//防止日期超出
                                $element -> {'createInRechargeNum'.$flag} = $this->createInRecharge($totalnewusers[$date_key_source]->pids,array_unique($totalrechargeusers[$date_key_current]->pids));
                                //$createInRechargeNum = $this->createInRecharge($totalnewusers[$date_key_source]->pids,array_unique($totalrechargeusers[$date_key_current]->pids));
                               // $element->{'num'.$flag} = $createInRechargeNum.'('.number_format($createInRechargeNum/$createnum,2)*100 .'%)';
                            }else{
                               // $element->{'num'.$flag} = ' ';
                                $element-> {'createnum'.$flag} = 0;
                                 $element -> {'createInRechargeNum'.$flag} = 0;
                            }

                            if($i == 0){
                                //$obj->{'num'.$flag} = $createnum;
                                $obj->{'createnum'.$flag} = $createnum;
                                $obj->{'createInRechargeNum'.$flag} = 0;
                            }
                            $flag++;
                    }

                    if($i==0)$list[]=$obj;
                    $list[] = $element;
            }
        }

        //进行数据合并
        $newreturnlist = array();
        for($i=0 ; $i <30 ; $i++){
           $obj = new stdClass();
           $obj->datedesp = '第'.$i.'天';

            $special_obj = new stdClass();
            $special_obj->datedesp = '新增玩家';

           foreach($list as $element){
                    if($element->row ==$i){
                            for($j=0 ; $j < count($dateArray) ; $j++){
                                if(!isset($obj->{'createnum'.$j}))$obj->{'createnum'.$j}=0;
                                if(!isset($obj->{'createInRechargeNum'.$j}))$obj->{'createInRechargeNum'.$j}=0;
                                $obj->{'createnum'.$j} += $element ->{'createnum'.$j};
                                $obj->{'createInRechargeNum'.$j} += $element ->{'createInRechargeNum'.$j};
                            }
                    }

                    if($element->row == -1 && $i==0){
                        for($j=0 ; $j < count($dateArray) ; $j++){
                            if(!isset($special_obj->{'createnum'.$j}))$special_obj->{'createnum'.$j}=0;
                            if(!isset($special_obj->{'createInRechargeNum'.$j}))$special_obj->{'createInRechargeNum'.$j}=0;
                            $special_obj->{'createnum'.$j} += $element ->{'createnum'.$j};
                            $special_obj->{'createInRechargeNum'.$j} += $element ->{'createInRechargeNum'.$j};
                        }
                    }

           }


           for($k =0 ; $k < count($dateArray) ; $k++){
                   if($obj->{'createnum'.$k} !=0 && $dateArray[$k] <= $endtimestamp)
                       $obj->{'num'.$k} = $obj->{'createInRechargeNum'.$k}.'('.number_format($obj->{'createInRechargeNum'.$k}/$obj->{'createnum'.$k},4)*100 .'%)';
                   else
                       $obj->{'num'.$k} = ' ';

                   if($i == 0){
                       $special_obj->{'num'.$k} = $special_obj ->{'createnum'.$k};
                   }
           }

            if($i==0) $newreturnlist[] = $special_obj;
           $newreturnlist[] = $obj;
     }

        return $newreturnlist;
    }

    public function getCondition($condition){}

    /**
     * @param array $list
     * @param $dateArray
     * @return array
     * 把总时间段内的注册用户,根据日期分组合并
     */
    private function make_group(Array $list,$dateArray){
            $returnlist = array();
            foreach($dateArray as $date){
                    $element = new stdClass();
                    $element->pids = array();
                    $element->date = $date;
                    foreach($list as $obj){
                            if($obj->date == $date){
                                    $element -> pids[]  = $obj->id;
                            }
                    }
                  $returnlist[date('Y-m-d',$date)] = $element;
            }
            return $returnlist;
    }

    private function createInRecharge(Array $create_pids,Array $recharge_pids){
                $recharge_num = 0;
                foreach($create_pids as $pid){
                    foreach($recharge_pids as $rpid){
                            if($pid == $rpid){
                                    $recharge_num++;
                                    break;
                            }
                    }
                }
                return $recharge_num;
    }

}