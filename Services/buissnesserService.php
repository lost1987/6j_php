<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-12
 * Time: 上午11:21
 * To change this template use File | Settings | File Templates.
 * 运营商数据
 */
class BuissnesserService extends Service implements IService
{

    function BuissnesserService(){
        parent::__construct();
        $this -> db -> select_db(DB_NAME);
        $this -> table_buissnesser = DB_PREFIX.'buissnesser';
    }

    public function lists($page,$condition=null)
    {
        // TODO: Implement lists() method.
        $res = $this->db->select(" a.id,a.name" )
               -> from($this->table_buissnesser.' a ')
               -> where(" a.stat = 1 ")
               -> limit($page->start,$page->limit,'a.id asc')
               -> get()
               -> result_objects();

        return $res;
    }

    public function save($obj)
    {
        // TODO: Implement save() method.
        $name = $obj -> name;
        $bid = $obj-> bid;

        if(!isset($obj->id)){
            $sql = "insert into $this->table_buissnesser (id,name,stat)
                    values ($bid,'$name',1)";
        }else{
            $sql = "update $this->table_buissnesser set name='$name' where id = $obj->id";
        }

        if($this->db->query($sql)->queryState){
            return TRUE;
        }

        return FALSE;
    }

    public function edit($id)
    {
        // TODO: Implement edit() method.
        if(!empty($id)){
            $sql = "select name from $this->table_buissnesser  where id = $id";
            return $this->db->query($sql)->result_object();
        }
    }


    public function num_rows($null)
    {
        // TODO: Implement num_rows() method.
        $sql = "select count(id) as num from $this->table_buissnesser where stat=1";
        return $this -> db -> query($sql) -> result_object() -> num;
    }

    public function del($ids)
    {
        // TODO: Implement del() method.
        if(!empty($ids)){
            $sql = "update  $this->table_buissnesser set stat=0 where id in ($ids)";
            return $this -> db -> query($sql) -> queryState;
        }
    }

}
