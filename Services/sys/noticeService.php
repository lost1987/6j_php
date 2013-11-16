<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-1
 * Time: 下午3:09
 * To change this template use File | Settings | File Templates.
 * 公告
 */
class NoticeService extends ServerDBChooser
{
    function NoticeService(){
        $this -> table_notice = $this->prefix_2.'notice';
    }

    public function lists($page,$condition){
         require BASEPATH.'/Common/log.php';
         $servers = $condition -> servers;
         $flag = 0;
         $list = array();
         if(!empty($servers)){
             foreach($servers as $server){
                 $this->dbConnect($server,$server->dynamic_dbname);
                 $starttime = $this->db->datetime('starttime');
                 $endtime = $this->db->datetime('endtime');
                 $sql = "select id,$starttime as starttime,$endtime as endtime,time,context from  $this->table_notice order by endtime desc";
                 $templist = $this -> db -> query($sql) -> result_objects();
                 foreach($templist as $obj){
                     if($flag >= $page->start && $flag <= $page -> limit){
                         $obj->servername = $server -> name;
                         $log = new Syslog();
                         $slog = $log -> getlogByRefer($obj->id,'id',$server->id);
                         $obj->flagname = empty($slog->flagname) ? '' : $slog->flagname;
                         $list[] = $obj;
                     }
                     $flag++;
                 }
                 $this->dbClose();
             }
         }
         return $list;
    }


    public function num_rows($condition){
        $servers = $condition -> servers;
        $num = 0;
        if(!empty($servers)){
            foreach($servers as $server){
                $this->dbConnect($server,$server->dynamic_dbname);
                $sql = "select id from $this->table_notice";
                $templist = $this -> db -> query($sql) -> result_objects();
                foreach($templist as $obj){
                    $num++;
                }
                $this -> dbClose();
            }
        }
        return $num;
    }


    public function getCondition($condition){}

    public function save($notice){
        set_time_limit(0);
        //通过id关联操作人
        require BASEPATH.'/Common/log.php';
        $servers = $notice -> servers;
        $starttime = $notice->starttime;
        $endtime = empty($notice -> endtime) ? date('Y-m-d H:i:s',strtotime($starttime)+60) : $notice->endtime;
        $context = $notice -> context;
        $type = $notice -> type;//0:单次 1:循环

        $starthour = date('H',strtotime($starttime));
        $endhour = date('H',strtotime($endtime));
        $startmin = date('i',strtotime($starttime));
        $endmin = date('i',strtotime($endtime));

        if($type == 0){
            $time = 100;
        }else{
            $time = $notice -> time;
        }

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
                $db -> select_db($dynamic_dbname);
                $sql = "select max(id) as mid from $this->table_notice";
                $res = $db -> query($sql) -> result_object();
                $id = intval($res->mid) + 1;
                $db -> trans_begin();
                $executed_dbs[] = $db;
                $sql = "insert into $this->table_notice (id,context,time,starttime,endtime,starthour,startmin,endhour,endmin)
                    values ($id,'$context',$time,'$starttime','$endtime',$starthour,$startmin,$endhour,$endmin)";
                if(!$db -> query($sql)->queryState)throw new Exception('notice write data error!');

                $log = new stdClass();
                $log -> aid = $notice -> admin -> id;
                $log -> admin = $notice -> admin -> admin;
                $log -> flagname = $notice -> admin -> flagname;
                $log -> type = 3;
                $log -> typename = $log_action_type[3];
                $log -> donetime = date('Y-m-d H:i:s');
                $log -> server_id = $servers[$t]->id;
                $log -> server_name = $servers[$t]->name;
                $log -> refer_id = $id;
                $log -> refer_name = 'id';

                $slog = new Syslog();
                $log_db = new DB();
                $logdbs[] = $log_db;
                $log_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD,TRUE);
                $log_db -> select_db(DB_NAME);
                $log_db -> trans_begin();
                if(!$slog -> setlog($log) -> tran_save($log_db))throw new Exception('log-notice write data error!');
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

    public function del($notices){
        //根据相同的server取出id
        $server_ids = array();
        foreach($notices as $notice){
           $sid =  $notice -> server -> id;
           if(!in_array($sid,$server_ids)){
               $server_ids[] = $sid;
           }
        }

        foreach($server_ids as $sid){
            //取得该SID的服务器信息,并且取出要删除的公告id
            $nids = array();
            foreach($notices as $notice){
                if($notice->server->id == $sid){
                    $nids[] = $notice->id;
                    $server = $notice->server;
                }
            }
            if(isset($server)){
                $this -> dbConnect($server,$server->dynamic_dbname);
                $_nids = implode(',',$nids);
                $sql = "delete from $this->table_notice where id in ($_nids)";
                $this -> db -> query($sql);
                $this -> dbClose();
            }
        }
        return TRUE;
    }

}
