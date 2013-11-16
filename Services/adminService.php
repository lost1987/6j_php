<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-5
 * Time: 下午4:45
 * To change this template use File | Settings | File Templates.
 * 管理员
 */
class AdminService extends Service implements IService
{

  function __construct(){
      parent::__construct();
      $this -> db -> select_db(DB_NAME);
      $this -> table_admin = DB_PREFIX.'admin';
      $this -> table_permission = DB_PREFIX.'permission';
      $this -> table_buissnesser = DB_PREFIX.'buissnesser';
      $this -> table_servers = DB_PREFIX.'servers';
  }


  public function savePermission($admin){
      $id = $admin -> id;
      $permission = $admin -> permission;
      $sql  =  "update $this->table_admin set permission=$permission where id = $id";
      $this -> db -> query($sql);

      $sql = "update $this->table_permission set sjbb=$admin->sjbb,lsl=$admin->lsl,
              lchy=$admin->lchy,yxjsxx=$admin->yxjsxx,rzgl1=$admin->rzgl1,xtgl=$admin->xtgl,
              yygl=$admin->yygl,glygl=$admin->glygl,zhgl=$admin->zhgl,rzgl2=$admin->rzgl2,rzgl3=$admin->rzgl3 where id = $id";
      return $this -> db -> query($sql) -> queryState;
  }

  public function save($admin){

          $adminname = $admin -> admin;
          $passwd = md5($admin -> passwd.APPKEY);
          $flagname = $admin -> flagname;
          $bid = $admin->bid;
          $permission = 0;

          //检测是否用户重复
          if(empty($admin->id)){//只有添加用户的时候才会检测重复性
              $sql = "select id from $this->table_admin where admin = '$adminname'";
              $obj = $this -> db -> query($sql) -> result_object();
              if(!empty($obj->id)){
                  return FALSE;
              }
          }

          if(!isset($admin->id)){
              $sql = "insert into $this->table_admin(admin,passwd,permission,bid,flagname)
                    values ('$adminname','$passwd','$permission','$bid','$flagname')";

              if($this->db->query($sql)->queryState){
                  $sql = "select max(id) as id from $this->table_admin";
                  $maxid = $this->db->query($sql) -> result_object() -> id;
                  if(!empty($maxid)){
                      $sql = "insert into $this->table_permission (id,sjbb,lsl,lchy,yxjsxx,
                      rzgl1,xtgl,yygl,glygl,zhgl,rzgl2,rzgl3) values ($maxid,0,0,0,0,0,0,0,0,0,0,0)";
                      if($this->db->query($sql)->queryState)
                      return TRUE;
                  }
                  return FALSE;
              }

          }else{
              $sql = "update $this->table_admin set passwd='$passwd',flagname='$flagname',bid=$bid where id = $admin->id";
          }

          return FALSE;
  }

  public function edit($id){
        if(!empty($id)){
            $sql = "select a.id,a.admin,a.passwd,a.bid,a.flagname from $this->table_admin a where a.id = $id";
            return $this->db->query($sql)->result_object();
        }
  }

  public function lists($page,$condition=null){
    $res = $this -> db -> select(" a.id,a.admin,a.bid,a.flagname,b.name ")
           -> from("$this->table_admin a left join $this->table_buissnesser b")
           -> on("a.bid = b.id")
           -> where("a.id <> 1")
           -> limit($page->start,$page->limit,'a.id asc')
           -> get()
           -> result_objects();

    foreach($res as &$obj){
        if($obj -> bid == -1){
            $obj -> name = '无限制';
        }
    }
    return $res;
  }

  public function getBuissnessers($null){
      $sql = "select id,name from $this->table_buissnesser where stat=1";
      return $this->db->query($sql)->result_objects();
  }

  public function getServers($bid){
        $sql = "select id,name from $this->table_servers where bid = $bid where stat=1";
        return $this->db->query($sql)->result_objects();
  }

  public function num_rows($null){
      $sql = "select count(id) as num from $this->table_admin where id <> 1";
      return $this -> db -> query($sql) -> result_object() -> num;
  }

  public function del($ids){
      if(!empty($ids)){
          $sql = "delete from $this->table_admin where id in ($ids) and id <> 1";
          $this -> db -> query($sql);
          $sql = "delete from $this->table_permission where id in ($ids) and id <> 1";
          return $this -> db -> query($sql) -> queryState;
      }
  }

  public function all($null){
      $sql = "select a.id as aid,a.admin,a.permission,a.flagname,b.* from $this->table_admin a left join $this->table_permission b on a.id=b.id  where a.id <> 1";
      $res = $this->db->query($sql)->result_objects();
      return $res;
  }

  public function updatePWD($admin,$pwd){
        $oldpwd = $pwd -> oldpwd;
        $newpwd = $pwd -> newpwd;

        $oldpwd_validate = md5($oldpwd.APPKEY);
        $newpwd = md5($newpwd.APPKEY);

        $sql = "select passwd from $this->table_admin where id = $admin->id";
        $result = $this->db->query($sql) ->result_object();
        if($oldpwd_validate == $result->passwd){
            $sql = "update $this->table_admin set passwd = '$newpwd' where id = $admin->id";
            if($this->db->query($sql)->queryState)
            return TRUE;
        }
        return FALSE;
  }

}
