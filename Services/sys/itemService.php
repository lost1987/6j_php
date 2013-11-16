<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-7
 * Time: 下午1:42
 * To change this template use File | Settings | File Templates.
 */
class ItemService extends ServerDBChooser
{
   function ItemService(){
        $this -> table_items = $this->prefix_1.'item';
        $this -> db_items = 'mmo2d_staticljzm';
   }

   public function lists($server){
       $list = array();
        if(!empty($server)){
            $this -> dbConnect($server,$this->db_items);
            $sql = "select id,name from $this->table_items";
            $list = $this -> db -> query($sql) -> result_objects();
            $this -> dbClose();
        }
       return $list;
   }

   public function getCondition($condition){}
}
