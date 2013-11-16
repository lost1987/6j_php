<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-10
 * Time: 下午1:43
 * To change this template use File | Settings | File Templates.
 * 系统日志
 */
class Syslog extends Service
{

    private $aid;//管理员的ID
    private $admin;//管理员账号
    private $flagname; //管理员描述
    private $type;//日志类型
    private $typename;//日志类型名称
    private $donetime;//申请时间,或一次性操作的时间(前提是该记录是不需要审核的 ,如邮件和礼包是一次性操作不存在审核,时间就存在这个字段)
    private $server_id;//服务器ID
    private $server_name;//服务器名称
    private $refer_id;//根据日志类型,关联操作记录的字段值
    private $refer_name;//根据日志类型,关联操作记录的字段名
    private $item_id;//发送的物品 可以为空
    private $item_num;//发送的物品数量
    private $content ; //内容字段
    private $title  ;  //标题字段
    private $state ; //状态标识
    private $optime;//操作时间


    function Syslog(){
        parent::__construct();
        require_once BASEPATH.'/Common/log.php';
        $this -> db -> select_db(DB_NAME);
    }

    public function setlog($log){
        if(empty($log)){
            error_log('日志实体不能为空');
            return;
        }
        $this -> aid = $log -> aid;
        $this -> admin = $log -> admin;
        $this -> flagname = $log -> flagname;
        $this -> type = $log -> type;
        $this -> typename = $log -> typename;
        $this -> donetime = $log -> donetime;
        $this -> server_id = $log -> server_id;
        $this -> server_name = $log -> server_name;
        $this -> refer_id = $log -> refer_id;
        $this -> refer_name = $log -> refer_name;
        $this -> item_id = empty($log->item_id) ? 0 : $log -> item_id;
        $this -> item_num = empty($log->item_num) ? 0 : $log -> item_num;
        $this -> content = empty($log->content) ? '' : $log -> content;
        $this -> title = empty($log->title) ? '' : $log -> title;
        $this -> state = empty($log->state) ?  0 : $log->state;
        $this -> optime = empty($log->optime) ? 0 : $log->optime;

        return $this;
    }

    /**
     * 配合事务使用
     */
    public function tran_save($db){
        $sql = "insert into ljzm_syslog (aid,admin,flagname,type,typename,donetime,server_id,server_name,refer_id,refer_name,itemid,itemnum,content,title,state,optime) values
        ($this->aid,'$this->admin','$this->flagname',$this->type,'$this->typename','$this->donetime',$this->server_id,'$this->server_name',$this->refer_id,'$this->refer_name',$this->item_id,$this->item_num,'$this->content','$this->title',$this->state,$this->optime)";
        if($db -> query($sql) -> queryState){
            return TRUE;
        }
        return FALSE;
    }

    /**
    当类型为邮件日志的时候,调用save完成后,记录收取该邮件的玩家
     * 配合日志使用
     */
    public function tran_saveMailPlayers($players,$db){
        $lid = $db->insert_id('ljzm_syslog');
        if(count($players) > 0){

            $pernum = 100;//每次插入100条
            $cur = 1;//游标
            $total = count($players);
            $sql = "insert into ljzm_mail_records (lid,playername,playerid) ";

            foreach($players as $player){
                if($cur%$pernum == 0){
                    $db->query($sql);
                    $sql = "insert into ljzm_mail_records (lid,playername,playerid) select $lid,'$player->name','$player->id' ";
                }else if($cur== 1){
                    $sql .=  " select $lid,'$player->name','$player->id' ";
                }
                else{
                    $sql .= " union all select $lid,'$player->name','$player->id' ";
                }

                if($total == $cur){
                   if(!$db->query($sql)->queryState)return FALSE;
                }

                $cur++;
            }
        }
        return TRUE;
    }

    public function getlogByTime($time,$server_id){
        $this -> db -> select_db(DB_NAME);
        $sql = "select * from ljzm_syslog where donetime = '$time' and server_id = $server_id";
        $log = $this-> db -> query($sql) -> result_object();
        return $log;
    }

    public function getlogByRefer($referVal,$referName,$server_id){
        $this -> db -> select_db(DB_NAME);
        $sql = "select * from ljzm_syslog where refer_id = '$referVal' and refer_name = '$referName' and server_id=$server_id";
        $log = $this-> db -> query($sql) -> result_object();
        return $log;
    }


    /**
    当类型奖励申请的时候,调用save完成后,记录收取该奖励的玩家
     */
    public function tran_saveRewardPlayers($player,$db){
        $lid = $db->insert_id('ljzm_syslog');
        $sql = "insert into ljzm_reward_records (lid,playername,playerid) values
                ($lid,'$player->name','$player->id')";
        if($db -> query($sql)->queryState)
        return TRUE;
        return FALSE;
    }

}
