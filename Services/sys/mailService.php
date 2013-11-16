<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-3
 * Time: 下午2:05
 * To change this template use File | Settings | File Templates.
 * 邮件发送
 */
class MailService extends ServerDBChooser
{
     function MailService(){
           $this->table_user = $this->prefix_1.'user';
           $this->table_mail = $this->prefix_3.'mail';
           $this->table_mailDetail = $this->prefix_1.'postmail';
           $this -> table_item = $this->prefix_1.'item';
           $this -> table_syslog = 'ljzm_syslog';
           $this -> table_mail_record = 'ljzm_mail_records';
           $this -> db_static = 'mmo2d_staticljzm';
     }

     public function lists($page,$condition){
            $servers = $condition->servers;
            $list = array();
            $flag = 0;
            if(count($servers) > 0){
                foreach($servers as $server){
                    $db = new DB();//连接分发数据库
                    $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
                    $db -> select_db(DB_NAME);
                    $dtime = $db -> datetime('donetime');
                    $sql =  "select *,$dtime as dtime from $this->table_syslog where server_id=$server->id and (type=2 or type=10) order by donetime desc";
                    $loglist = $db->query($sql)->result_objects();
                    $db -> close();
                    unset($db);

                    //获取物品列表
                    $this->dbConnect($server,$this->db_static);
                    $sql = "select id,name from $this->table_item";
                    $items = $this->db->query($sql)->result_objects();
                    $this->dbClose();

                    foreach($loglist as $log){//查找物品
                        if($log->itemid != 0 && !empty($log->itemid)){
                            foreach($items as $item){
                                if($item->id == $log->itemid){
                                       $log->itemname = $item->name;
                                       break;
                                }
                            }
                        }else{
                            $log->itemname='';
                        }

                        $list[] = $log;
                    }
                }

                $return = array();
                foreach($list  as  $obj){
                    if($flag >= $page->start && $flag <= $page -> limit){
                        $return[] = $obj;
                    }
                    $flag++;
                }
            }
            return $return;
     }

     public function num_rows($condition){
         $servers = $condition->servers;
         $nums = 0;
         if(!empty($servers)){
             $db = new DB();//连接分发数据库
             $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
             $db -> select_db(DB_NAME);
             foreach($servers as $server){
                 $sql = "select count(id) as num from $this->table_syslog where server_id=$server->id and (type=2 or type=10)";
                 $num = $db -> query($sql) -> result_object() -> num;
                 $nums += $num;

             }
             $db-> close();
         }
         return $nums;
     }


     public function getCondition($condition){}

     public function sendMailsWithServers($mail){
         set_time_limit(0);
         //通过随机验证码关联操作人
        require BASEPATH.'/Common/log.php';
        $servers = $mail -> servers;
        $item_id = empty($mail->item_id) ? 0 : $mail->item_id;
        $item_num = empty($mail->item_num) ? 0 : $mail->item_num;

         //已执行过的事务DB数组
         $executed_dbs = array();
         //日志数据库连接
         $logdbs = array();

         if(count($servers) < 1) return 2;

         $server_ids = array();
         foreach($servers as $server){
             $server_ids[] = $server->id;
         }
         $server_ids = implode(',',$server_ids);
         $servers = $this->getServers($server_ids);

         //创建多个数据库连接
         $db_flag  = 0;
         $dbs = array();
         foreach($servers as $server){
             $dbs[$db_flag]['db'] = new DB();
             $dbs[$db_flag]['db'] -> connect($server->ip.':'.$server->port,$server->dbuser,$server->dbpwd,TRUE);
             $dbs[$db_flag]['dynamic_dbname'] = $server->dynamic_dbname;
             $db_flag++;
         }

         try{

             for($t = 0 ; $t < count($dbs) ; $t++){
                 $db = $dbs[$t]['db'];
                 $dynamic_dbname=$dbs[$t]['dynamic_dbname'];
                 $code = time();//每个服务器一个时间标识

                 $db -> select_db($this->db_static);
                 //验证当前附件ID的正确性
                 $items = $db->query("select id,name from $this->table_item")->result_objects();
                 $item_arr = array();
                 foreach($items as $item){
                     $item_arr[] =  $item -> id;
                 }

                 if(!in_array($item_id,$item_arr) && $item_id != 0){
                     return 1;//附件ID错误
                 }

                 $db -> select_db($dynamic_dbname);
                 //查询此服所有玩家pid
                 $sql = "select id,name from $this->table_user";
                 $players = $db->query($sql)->result_objects();

                 if(count($players) > 0){
                     //开启事务
                     $db -> trans_begin();
                     $executed_dbs[] = $db;

                     $pernum = 100;//每次插入100条
                     $cur = 1;//游标
                     $total = count($players);
                     $sql = "insert into $this->table_mail (pid,itemid,itemnum,theme,contents,code) ";

                     foreach($players as $player){
                         if($cur%$pernum == 0){
                             if(!$db->query($sql)->queryState)throw new Exception('mail write data error');
                             $sql = "insert into $this->table_mail (pid,itemid,itemnum,theme,contents,code)  select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                         }else if($cur == 1){
                             $sql .=  " select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                         }
                         else{
                             $sql .= " union all select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                         }

                         if($total == $cur){
                             if(!$db->query($sql)->queryState)throw new Exception('mail write data error');
                         }

                         $cur++;
                     }

                     $log = new stdClass();
                     $log -> aid = $mail -> admin -> id;
                     $log -> admin = $mail -> admin -> admin;
                     $log -> flagname = $mail -> admin -> flagname;
                     $log -> type = 10;
                     $log -> typename = $log_action_type[10];
                     $log -> donetime = date('Y-m-d H:i:s');
                     $log -> server_id = $servers[$t]->id;
                     $log -> server_name = $servers[$t]->name;
                     $log -> refer_id = $code;
                     $log -> refer_name = 'code';
                     $log -> item_id = $item_id;
                     $log -> item_num = $item_num;
                     $log -> content = $mail->context;
                     $log -> title = $mail->title;

                     $slog = new Syslog();
                     $log_db = new DB();
                     $logdbs[] = $log_db;
                     $log_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD,TRUE);
                     $log_db -> select_db(DB_NAME);
                     $log_db -> trans_begin();
                     if(!$slog -> setlog($log) -> tran_save($log_db))throw new Exception('mail write data error');
                     if(!$slog -> tran_saveMailPlayers($players,$log_db))throw new Exception('mail write data error');
                 }
             }

             //执行完成 提交
             for($i = 0 ; $i < count($executed_dbs) ; $i++){
                 $executed_dbs[$i] -> commit();
                 $executed_dbs[$i] -> close();
             }

             for($i = 0 ; $i < count($logdbs) ; $i++){
                 $logdbs[$i] -> commit();
                 $logdbs[$i] -> close();
             }
             return TRUE;
         }catch (Exception $e){
             for($i = 0 ; $i < count($executed_dbs) ; $i++){
                 $executed_dbs[$i] -> rollback();
                 $executed_dbs[$i] -> close();
             }

             for($i = 0 ; $i < count($logdbs) ; $i++){
                 $logdbs[$i] -> rollback();
                 $logdbs[$i] -> close();
             }
         }
         return FALSE;
     }

    public function sendMailsWithPlayers($mail){
        set_time_limit(0);
        //通过随机验证码关联操作人
        require BASEPATH.'/Common/log.php';
        $players = $mail -> players;
        $servers = $mail -> servers;
        $item_id = empty($mail->item_id) ? 0 : $mail->item_id;
        $item_num = empty($mail->item_num) ? 0 : $mail->item_num;
        //已执行过的事务DB数组
        $executed_dbs = array();
        //日志数据库连接
        $logdbs = array();

        if(count($servers) < 1) return 2;

        $server_ids = array();
        foreach($servers as $server){
            $server_ids[] = $server->id;
        }
        $server_ids = implode(',',$server_ids);
        $servers = $this->getServers($server_ids);

        //创建多个数据库连接
        $db_flag  = 0;
        $dbs = array();
        foreach($servers as $server){
            $dbs[$db_flag]['db'] = new DB();
            $dbs[$db_flag]['db'] -> connect($server->ip.':'.$server->port,$server->dbuser,$server->dbpwd,TRUE);
            $dbs[$db_flag]['dynamic_dbname'] = $server->dynamic_dbname;
            $dbs[$db_flag]['server'] = $server;
            $db_flag++;
        }

        try{
            for($t=0; $t < count($dbs) ; $t++){
                $db = $dbs[$t]['db'];
                $dynamic_dbname=$dbs[$t]['dynamic_dbname'];
                $code = time();//每个服务器一个时间标识
                $server = $dbs[$t]['server'];//当前服务器

                $db -> select_db($this->db_static);

                //验证当前附件ID的正确性
                $items = $db->query("select id from $this->table_item")->result_objects();
                $item_arr = array();
                foreach($items as $item){
                    $item_arr[] =  $item -> id;
                }

                if(!in_array($item_id,$item_arr) && $item_id != 0){
                    return 1;//附件ID错误
                }

                //取当前server 被选中的player
                $plist = array();
                foreach($players as $player){
                    if($player->server->id == $server->id){
                        $plist[] = $player;
                    }
                }

                if(count($plist) > 0){
                    $db->select_db($dynamic_dbname);
                    //开启事务
                    $db -> trans_begin();
                    $executed_dbs[] = $db;
                    $sql = "insert into $this->table_mail (pid,itemid,itemnum,theme,contents,code) ";
                    $pernum = 100;//每次插入100条
                    $cur = 1;//游标
                    $total = count($players);
                    foreach($plist as $player){
                        if($cur%$pernum == 0){
                            if(!$db->query($sql)->queryState)throw new Exception('mail write data error');
                            $sql = "insert into $this->table_mail (pid,itemid,itemnum,theme,contents,code)  select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                        }else if($cur == 1){
                            $sql .=  " select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                        }
                        else{
                            $sql .= " union all select $player->id,$item_id,$item_num,'$mail->title','$mail->context',$code ";
                        }

                        if($total == $cur){
                            if(!$db->query($sql)->queryState)throw new Exception('mail write data error');
                        }

                        $cur++;
                    }

                    $log = new stdClass();
                    $log -> aid = $mail -> admin -> id;
                    $log -> admin = $mail -> admin -> admin;
                    $log -> flagname = $mail -> admin -> flagname;
                    $log -> type = 2;
                    $log -> typename = $log_action_type[2];
                    $log -> donetime = date('Y-m-d H:i:s');
                    $log -> server_id = $servers[$t]->id;
                    $log -> server_name = $servers[$t]->name;
                    $log -> refer_id = $code;
                    $log -> refer_name = 'code';
                    $log -> item_id = $item_id;
                    $log -> item_num = $item_num;
                    $log -> content = $mail->context;
                    $log -> title = $mail->title;
                    $slog = new Syslog();
                    $log_db = new DB();
                    $logdbs[] = $log_db;
                    $log_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD,TRUE);
                    $log_db -> select_db(DB_NAME);
                    $log_db -> trans_begin();
                    if(!$slog -> setlog($log) -> tran_save($log_db))throw new Exception('mail write data error');
                    if(!$slog -> tran_saveMailPlayers($plist,$log_db))throw new Exception('mail write data error');
                }
            }

            //执行完成 提交
            for($i = 0 ; $i < count($executed_dbs) ; $i++){
                $executed_dbs[$i] -> commit();
                $executed_dbs[$i] -> close();
            }

            for($i = 0 ; $i < count($logdbs) ; $i++){
                $logdbs[$i] -> commit();
                $logdbs[$i] -> close();
            }

            return 0;
        }catch (Exception $e){
            for($i = 0 ; $i < count($executed_dbs) ; $i++){
                $executed_dbs[$i] -> rollback();
                $executed_dbs[$i] -> close();
            }

            for($i = 0 ; $i < count($logdbs) ; $i++){
                $logdbs[$i] -> rollback();
                $logdbs[$i] -> close();
            }
        }
        return -1;
    }

    private function getItemByID($id,$items){
        foreach($items as $item){
            if($item->id == $id){
                $returnObj = $item;
                break;
            }
        }

        if(isset($returnObj))
        return $returnObj -> name;


        return null;
    }

    public function mailPlayers($server,$lid){
        $list= array();
        if(!empty($server)){
            $db = new DB();//连接分发数据库
            $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
            $db -> select_db(DB_NAME);

            $list = $db -> query("select playername from $this->table_mail_record where lid=$lid") -> result_objects();
            $db -> close();
        }
        return $list;
    }

}
