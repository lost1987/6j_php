<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-5-2
 * Time: 下午4:04
 * To change this template use File | Settings | File Templates.
 */
class SyslogService extends Service
{

    function SyslogService(){
        parent::__construct();
        $this->table_player = 'fr_user';
        $this ->  db -> select_db(DB_NAME);
        $this -> db_base = 'mmo2d_baseljzm';
        $this -> table_payinfo = 'fr2_payinfo';
        $this-> table_base = 'fr2_base';
        $this-> table_record= 'fr2_record';
    }


    /**
     *
     * 修改日志的状态[字段state]
     *
     * @param logIDs 日志ID字符串 逗号隔开
     * @param state  日志要修改的状态
     * @Param refername  这里 此字段作为 日志的操作人
     *
     */
    public function updateLogState($log,$state,$refername){
          $result = 0;
           try{
               $logid = $log->id;
               $optime = time();
               $this->db->trans_begin();
               $sql = "update ljzm_syslog set state = $state,refer_name='$refername',optime=$optime where id = $logid";
               $res = $this -> db -> query($sql) -> queryState;
               if(!$res) throw new Exception('log-pay error!');
               if($state == 2 && $res){//批准
                   //如果批准的话 直接调用支付接口
                   $sid = $log -> server_id;
                   $sql = "select bid,ip,port,dbuser,dbpwd,dynamic_dbname from ljzm_servers where id = $sid";
                   $server = $this -> db -> query($sql) -> result_object();

                   $db = new DB();
                   $db -> connect($server->ip.':'.$server->port,$server->dbuser,$server->dbpwd,TRUE);
                   $db -> select_db($server->dynamic_dbname);

                   $sql = "select account_name from $this->table_player where name = '$log->playername'";
                   $uname = $db -> query($sql) -> result_object() -> account_name;

                   if(empty($uname))return 0;
                   $db -> close();
                   unset($db);

                   $utime = time();
                   $aid = $server -> bid;//运营商ID
                   $goldmoney = $log -> itemnum;
                   $eventid = 'REWARD'.date('YmdHis').make_rand_str(5);
                   $realServerId = $this -> getRealSid($aid,$sid);

                   //把原来调用支付接口改成直接写数据库
                    $this -> db -> select_db($this->db_base);
                   if($this->db->select("count(id) as num")->from($this->table_payinfo)->where("eventid='$eventid'")->get()->result_object()->num==0){
                        $base = $this->db->select("*")->from($this->table_base)->where("loginname='$uname'")->get()->result_object();
                        if($base){
                                $this -> db -> select_db($server->dynamic_dbname);
                                $player = $this -> db -> select("*")->from($this->table_player)
                                    ->where("account_id = '$base->aountid' and state = 0 and server = $realServerId")->get()->result_object();
                                if($player){
                                        //以上验证完毕 开始写数据
                                        $this->db->select_db($this->db_base);
                                        if(!$this->db->query("update $this->table_base set yuanbao=yuanbao+$goldmoney,yuanbaonum=yuanbaonum+1 where aountid=$base->aountid")->queryState){
                                            error_log('数据写入失败1');
                                            throw new Exception("错误代码:-10 数据写入失败");
                                        }

                                       $this->db->select_db($server->dynamic_dbname);
                                        if(!$this->db->query("update $this->table_player set saveyuanbao=saveyuanbao+$goldmoney,mask31=mask31+$goldmoney where id = $player->id") -> queryState){
                                            error_log('数据写入失败2');
                                            throw new Exception("错误代码:-10 数据写入失败");
                                        }

                                        if(!$this->db->query("insert into $this->table_record (type, id1, id2, param1, param2, param4, str, str2) values(0, $player->id, 0, 90000001,$goldmoney , 44, '".$_SERVER['REMOTE_ADDR']."','$eventid')") -> queryState){
                                            error_log('数据写入失败3');
                                            throw new Exception("错误代码:-10 数据写入失败");
                                        }

                                       $ht = $this -> db ->query("select * from ht_topup where pid = $player->id") -> result_object();
                                       if(!$ht){
                                           if(!$this->db->query("insert into ht_topup(pid) values($player->id)")->queryState)
                                           {
                                               error_log('数据写入失败4');
                                               throw new Exception("错误代码:-10 数据写入失败");
                                           }
                                       }

                                       $this ->db -> select_db($this->db_base);
                                       if(!$this->db->query("insert into fr2_payinfo(eventid) values(' $eventid ')")->queryState){
                                           error_log('数据写入失败5');
                                           throw new Exception("错误代码:-10 数据写入失败");
                                       }

                                }else{
                                    error_log('错误代码:-1 角色不存在!');
                                    throw new Exception('错误代码:-1 角色不存在!');
                                }
                        }else{
                            error_log('错误代码:-2 用户验证失败!');
                            throw new Exception('错误代码:-2 用户验证失败!');
                        }
                   }else{
                       error_log('eventid[订单号]已存在!');
                       throw new Exception('eventid[订单号]已存在!');
                   }

                   $this-> db ->commit();
                   $this -> db -> close();
                   return 1;
               }else if($res){//拒绝
                   $this->db->commit();
                   $this -> db -> close();
                   return 1;
               }

               return intval($result);
           }catch (Exception $e){
                $this->db->rollback();
                $this->db->close();
                return intval($result);
           }
    }

    //得到真实的服务器ID
    private function getRealSid($bid,$sid){
        /**
         * sid的填写方式
         * 公式  服务器唯一标识 = 服务器ID + 运营商ID*10000;
         */
        if($sid > 10000*$bid){
            $serverid = $sid % (10000*$bid);
        }else{
            $serverid = (10000*$bid - $sid) * -1;//测试服务器 一般为负数 或者 0
        }
        return $serverid;
    }

}
