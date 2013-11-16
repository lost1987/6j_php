<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-15
 * Time: 上午10:23
 * To change this template use File | Settings | File Templates.
 * 管理员申请
 */
class ManageApplyService extends Service
{

    function ManageApplyService(){
        parent::__construct();
        $this->table_manage_apply = DB_PREFIX.'admin_apply';
        $this->table_admin = DB_PREFIX.'admin';
        $this -> db -> select_db(DB_NAME);
    }


    public function lists($page){
        require BASEPATH.'/Common/contentconfig.php';

        $res = $this -> db -> select("a.*")
               -> from("$this->table_manage_apply a")
               -> limit($page->start,$page->limit,'a.applytime desc')
               -> get()
               -> result_objects();

        foreach($res as &$apply){
            $apply->stateName = $state[$apply->state];
            $apply->applytime = date('Y-m-d H:i:s',$apply->applytime);
            $apply->permission = $apply_permissions[$apply->permission];
        }
        return $res;
    }

    public function num_rows($null){
        $sql = "select count(id) as num from $this->table_manage_apply";
        return $this->db->query($sql)->result_object() -> num;
    }


    public function save($apply){
         $admin = $apply->admin;
         $sql = "select count(id) as num from $this->table_admin where admin = '$admin'";
         if($this -> db -> query($sql) -> result_object() -> num > 0){
             return FALSE;
         }

         $applytime = time();
         $passwd = md5($apply->passwd.APPKEY);
         $sql = "insert into $this->table_manage_apply (admin,passwd,state,applytime,permission,bid,op_admin,op_adminid,buissnesser,flagname)
            values ('$apply->admin','$passwd',0,$applytime,$apply->permission,$apply->bid,'$apply->op_admin',$apply->op_adminid,'$apply->buissnesser','$apply->flagname')
         ";

         if($this-> db -> query($sql) -> queryState){
            return TRUE;
        }

        return FALSE;
}

}
