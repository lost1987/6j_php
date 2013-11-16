<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-16
 * Time: 上午10:05
 * To change this template use File | Settings | File Templates.
 * 运营商用户申请
 */
class AdminApplyManageService extends Service
{
    function AdminApplyManageService(){
        parent::__construct();
        $this->table_admin_apply = DB_PREFIX.'admin_apply';
        $this->table_admin = DB_PREFIX.'admin';
        $this->table_permission = DB_PREFIX.'permission';
        $this->db->select_db(DB_NAME);
    }

    public function lists($page,$condition){
            require BASEPATH.'/Common/contentconfig.php';
            $cond = $this->getCondition($condition);

            $list = $this->db->select("a.*")
                    ->from("$this->table_admin_apply a")
                    ->where($cond)
                    ->limit($page->start,$page->limit,'a.applytime desc')
                    -> get()
                    -> result_objects();

            foreach($list as &$apply){
                $apply->permission = $apply_permissions[$apply->permission];
                $apply->stateName = $state[$apply->state];
                $apply->applytime = date('Y-m-d H:i:s',$apply->applytime);
                if($apply->state!=0){
                    $apply->_enabled = false;
                }else{
                    $apply->_enabled = true;
                }
            }
            return $list;
    }

    public function num_rows($condition){
            $cond = $this->getCondition($condition);
            $sql = "select count(a.id) as num from $this->table_admin_apply a $cond";
            return $this->db->query($sql)->result_object()->num;
    }

    private function getCondition($condition){
        $state = empty($condition -> state) ? 0 : $condition->state;
        $cond = " where a.state = $state ";
        return $cond;
    }

    public function permit($apply){
        $admin = $apply -> admin;
        $passwd = $apply -> passwd;
        $bid = $apply -> bid;
        $flagname = $apply->flagname;

        $sql = "insert into $this->table_admin (admin,passwd,bid,flagname,permission) values
                ('$admin','$passwd',$bid,'$flagname',0)";
        if($this->db->query($sql)->queryState){
            $sql = "select max(id) as insert_id from $this->table_admin";
            $maxid = $this->db->query($sql)->result_object()->insert_id;
            $sql = "insert into $this->table_permission values ($maxid,0,0,0,0,0,0,0,0,0,0,0)";
            if($this->db->query($sql)->queryState){
                $sql = "update $this->table_admin_apply set state=2 where id = $apply->id";
                if($this->db->query($sql)->queryState)
                return TRUE;
            }
            $sql = "delete from $this->table_admin where id = $maxid";
            $this->db->query($sql);
            return FALSE;
        }
        return FALSE;
    }

    public function refuse($apply){
        $sql = "update $this->table_admin_apply set state=1 where id = $apply->id";
        if($this->db->query($sql)->queryState)
        return TRUE;
        return FALSE;
    }
}
