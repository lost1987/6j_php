<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-28
 * Time: 下午7:04
 * To change this template use File | Settings | File Templates.
 * 装备
 */
class ItemsService extends  ServerDBChooser
{
    function ItemsService(){
        $this -> table_item = $this->prefix_1.'useritem';
        $this -> table_dynamicItem = $this->prefix_1.'dynamicitem';
        $this -> table_staticitem = $this->prefix_1.'item';

        $this -> db_static = 'mmo2d_staticljzm';
    }

    public function detail($pid,$server){
        $this -> dbConnect($server,$server->dynamic_dbname);
        $sql = "select a.itemid,a.strength,a.fw1,a.fw2,a.fw3,a.fw4,a.fw5,a.fw6 from $this->table_dynamicItem a left join $this->table_item b on a.id=b.itemid where b.pid = $pid and b.position < 100";
        $items = $this -> db -> query($sql) -> result_objects();
        $item_ids = '';
        foreach($items as $item){
            $item_ids.= $item->itemid.',';
        }
        $item_ids = substr($item_ids,0,strlen($item_ids)-1);

        if(!empty($item_ids)){
            $this -> db -> select_db($this->db_static);
            $sql = "select id,name from $this->table_staticitem where id in ($item_ids)";
            $itemnames = $this -> db -> query($sql) -> result_objects();

            foreach($items as &$item){
                if(empty($item->strength))$item->strength = 0;
                foreach($itemnames as $name){
                    if($item->itemid == $name->id){
                        $item -> itemname = $name->name;
                        break;
                    }
                }
                //计算装备颜色
                $item -> color = Color::getColor(substr($item->itemid,-1,1));

                $fw_ids = array();
                //计算装备的符文
                for($i=1; $i < 7 ; $i++){
                    if(!empty($item->{'fw'.$i})){
                        $fw_ids[] = $item->{'fw'.$i};
                    }
                }

                $item -> fws = array();

                if(count($fw_ids) > 0){
                    $fw_ids = implode(',',$fw_ids);
                    $sql = " select name from $this->table_staticitem where id in ($fw_ids)";
                    $item -> fws = $this -> db -> query($sql) -> result_objects();
                }
            }
        }

        return $items;
    }

    protected function getCondition($condition)
    {
        // TODO: Implement getCondition() method.
    }
}
