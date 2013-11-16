<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-10-8
 * Time: 下午4:37
 * To change this template use File | Settings | File Templates.
 * TX 邀请好友
 */
class User extends Baseapi
{

    function User(){
         parent::__construct();
        $this -> tableInviter = 'ht_invite';
        $this -> tableUser = 'fr_user';
    }

     public function invitefriend(){

         $inviter = $this->input -> get('inviter');
         $invitedOpenid = $this->input -> get('invitedOpenid');

         //判断发起邀请的人是否有创建角色
         $role = $this->db->select('name,id')->from($this->tableUser) -> where("account_name='$inviter'") -> get() -> result_object();
         if(empty($role->name)){
             echo 0;//邀请失败
             exit;
         }

         //发起邀请人的pid
         $pid = $role->id;

         //查询是否存在被邀请人
         $num = $this -> db -> select('count(openid) as num') ->from($this->tableInviter) -> where("openid='$invitedOpenid'") -> get() -> result_object()->num;

         if($num == 0){//插入
                $data = array('openid'=>$invitedOpenid,'pid'=>$pid);
                if($this->db->insert($this->tableInviter,$data)){
                    echo 1 ;exit;
                }
         }else{//更新
               $data = array('pid'=>$pid,'new'=>1);
               if($this->db->update($this->tableInviter,$data,"openid='$invitedOpenid'")){
                   echo 1;exit;
               }
         }
         echo 0;
     }


     public function register(){
         $sid = $this -> input -> get('sid');
         $openid = $this->input->get('openid');
         $temp = $this->db->select("count(id) as num") -> from('fr_user') -> where("account_name = '$openid'") -> get() -> result_object();

         if($temp->num == 0){//如果不存在就往日志里写一条数据
              $temp = $this->db->select("count(id) as num") -> from('ht_register') -> where("openid = '$openid'") -> get() -> result_object();
              if($temp->num == 0){//防止账号自动注册重复
                  $this->db->query("insert into ht_register (openid) values ('$openid')");
                  //写入openid
                  $data = array(
                      'str2' => $openid,
                      'time' => date('Y-m-d H:i:s'),
                      'type' => 0,
                      'param4' => 200,
                      'str' => '注册',
                      'id1' => 1
                  );
                  $this->db->insert('fr2_record',$data);
                  $this->db->insert('fr2_analysis',$data);
              }
         }
         $this -> db -> close();
         echo 1;
     }

}
