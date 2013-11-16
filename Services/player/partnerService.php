<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-17
 * Time: 下午3:41
 * To change this template use File | Settings | File Templates.
 */
class PartnerService extends ServerDBChooser
{

    function PartnerService(){
        $this -> table_partner = $this -> prefix_2.'huoban';
        $this -> table_dynamic_item = $this -> prefix_1 . 'dynamicitem';
        $this -> table_item = $this -> prefix_1.'item';
        $this -> table_huoban = $this -> prefix_2.'huobans';
        $this -> table_fabao = $this->prefix_2 . 'fabao';
        $this -> db_static = 'mmo2d_staticljzm';
    }

    public function detail($pid,$server){
        if(!empty($server)){
            $this->dbConnect($server,$server->dynamic_dbname);
            $sql = "select * from $this->table_partner where  pid = $pid";
            $huoban_list = $this->db->query($sql) -> result_objects();
            if(empty($huoban_list))return null;

            $this->db->select_db($this->db_static);
            foreach($huoban_list as &$huoban){
                $sql = "select name,color from $this->table_huoban where id = $huoban->hid";
                $huobans_static = $this -> db -> query($sql)->result_object();
                $huoban -> name = $huobans_static->name;
                $huoban -> color = $this->getColor($huobans_static->color);
            }

            $this->db->select_db($server->dynamic_dbname);
            foreach($huoban_list as &$huoban){
                $huoban_item_ids = $this->get_huoban_item_ids($huoban);
                if(!empty($huoban_item_ids)){
                    $sql = "select itemid,strength from $this->table_dynamic_item where id in ($huoban_item_ids)";
                    $ditems = $this->db->query($sql)->result_objects();
                    $dynamic_item_ids = $this->get_dynamic_item_ids($ditems);
                    if(!empty($dynamic_item_ids)){
                        $this->db->select_db($this->db_static);
                        $sql = "select name from $this->table_item where id in ($dynamic_item_ids)";
                        $items = $this->db->query($sql) -> result_objects();
                        $this->db->select_db($server->dynamic_dbname);
                    }
                    for($i = 0 ; $i < count($ditems) ; $i++){
                        $huoban -> {'zb'.($i+1)} = $items[$i]->name . ' +' . $ditems[$i]->strength;
                        $huoban -> {'zbcolor'.($i+1)} = Color::getColor(substr($ditems[$i]->itemid,-1,1));
                    }
                }

                //每个伙伴有8个法宝
                $this->db->select_db($this->db_static);
                for($i = 1 ; $i < 9 ; $i++){
                    if(!empty($huoban -> {'fabao'.$i})){
                        $fabao_type = intval($huoban -> {'fabao'.$i} % 10000 / 100);
                        $fabao_color = intval($huoban -> {'fabao'.$i} % 100 / 10);
                        $fabao_name = $this->db->query("select name from $this->table_fabao where type = $fabao_type and color=$fabao_color") ->result_object() ->name;
                        $huoban -> {'fabao_name'.$i} = $fabao_name;
                        $huoban -> {'fabao_color'.$i} = $this->getColor($fabao_color);
                        $huoban -> {'fabao_level'.$i} = intval($huoban -> {'fabao'.$i} % 10 + 1);
                    }else{
                        $huoban -> {'fabao_name'.$i} = '';
                        $huoban -> {'fabao_color'.$i} = '';
                        $huoban -> {'fabao_level'.$i} = '';
                    }
                }
                $this->db->select_db($server->dynamic_dbname);
            }

            return $huoban_list;

        }
    }

    private function get_huoban_item_ids($huoban){
        $huoban_item_ids = '';
        for($i = 1 ; $i < 7 ; $i++){
            if($huoban -> {'zb'.$i} !=0 && !empty($huoban -> {'zb'.$i}) )
            $huoban_item_ids .= $huoban -> {'zb'.$i} . ',';
        }
        if(strlen($huoban_item_ids) > 0)
        $huoban_item_ids = substr($huoban_item_ids,0,strlen($huoban_item_ids)-1);
        return $huoban_item_ids;
    }

    private function get_dynamic_item_ids($ditems){
        $dynamic_item_ids = '';
        foreach($ditems as $item){
            $dynamic_item_ids .= $item -> itemid.',';
        }
        if(strlen($dynamic_item_ids) > 0)
        $dynamic_item_ids = substr($dynamic_item_ids,0,strlen($dynamic_item_ids)-1);
        return $dynamic_item_ids;
    }

    private function getColor($color){
        switch($color){
            case 1: $_color = 'green';break;
            case 2: $_color = 'blue';break;
            case 3: $_color = 'purple';break;
            case 4: $_color = 'orange';break;
        }
        return $_color;
    }

    public function getCondition($condition){}
}
