<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-18
 * Time: 下午1:37
 * To change this template use File | Settings | File Templates.
 * 背包
 */
class BagService extends ServerDBChooser
{
    function BagService(){
        $this->table_useritem = $this->prefix_1.'useritem';
        $this->table_item = $this->prefix_1.'item';
        $this->table_dynamicitem = $this->prefix_1.'dynamicitem';
        $this->table_lieming = $this->prefix_2.'userlieming';
        $this->table_fabao = $this->prefix_2.'fabao';
        $this->table_qiankun = $this->prefix_1.'usercj';
        $this->db_static = 'mmo2d_staticljzm';
    }

    public function detail($pid,$server,$type){
        if(empty($type))return null;

        $this->dbConnect($server);
        $static_items = Datacache::getStaticItems($this->db);
        $this->db->select_db($server->dynamic_dbname);

        switch($type){
                case 'normal' :             //普通背包
                    $sql = "select itemid,itemtype,num from $this->table_useritem where pid = $pid and position >= 100 and position < 500";
                    $items = $this->db->query($sql)->result_objects();
                    foreach($items as &$item){
                        if($item -> itemtype == 2){
                            $sql = "select itemid,strength from $this->table_dynamicitem where id = $item->itemid";
                            $temp = $this->db->query($sql)->result_object();
                            if(!empty($temp)){
                                $item->strength =$temp->strength;
                                $detail = fetch_object_by_key('id',$temp->itemid,$static_items);
                                if(empty($detail))continue;
                                $item->name = $detail->name.' +'.$item->strength;
                            }
                        }else{
                            $detail = fetch_object_by_key('id',$item->itemid,$static_items);
                            if(empty($detail))continue;
                            $item->name = $detail->name;
                        }

                        $item -> color = Color::getColor(substr($item->itemid,-1,1));
                    }
                    return $items;break;

                case 'temp' : //临时背包
                        $this->db->select_db($server->dynamic_dbname);
                        $sql = "select itemid,itemtype,num from $this->table_useritem where pid = $pid and position >= 500 and position < 600 ";
                        $items = $this -> db -> query($sql) -> result_objects();
                        foreach($items as &$item){
                            if($item -> itemtype == 2){
                                $sql = "select itemid,strength from $this->table_dynamicitem where id = $item->itemid";
                                $temp = $this->db->query($sql)->result_object();
                                if(!empty($temp)){
                                    $item->strength =$temp->strength;
                                    $detail = fetch_object_by_key('id',$temp->itemid,$static_items);
                                    if(empty($detail))continue;
                                    $item->name = $detail->name.' +'.$item->strength;
                                }
                            }else{
                                $detail = fetch_object_by_key('id',$item->itemid,$static_items);
                                if(empty($detail))continue;
                                $item->name = $detail->name;
                            }
                            $item -> color = Color::getColor(substr($item->itemid,-1,1));
                        }
                        return $items;break;

                case 'task' : //任务背包
                        $this->db->select_db($server->dynamic_dbname);
                        $sql = "select itemid,itemtype,num from $this->table_useritem where pid = $pid and position >= 600 and position < 700  ";
                        $items = $this -> db -> query($sql) -> result_objects();
                        foreach($items as &$item){
                            if($item -> itemtype == 2){
                                $sql = "select itemid,strength from $this->table_dynamicitem where id = $item->itemid";
                                $temp = $this->db->query($sql)->result_object();
                                if(!empty($temp)){
                                    $item->strength =$temp->strength;
                                    $detail = fetch_object_by_key('id',$temp->itemid,$static_items);
                                    if(empty($detail))continue;
                                    $item->name = $detail->name.' +'.$item->strength;
                                }
                            }else{
                                $detail = fetch_object_by_key('id',$item->itemid,$static_items);
                                if(empty($detail))continue;
                                $item->name = $detail->name;
                            }
                            $item -> color = Color::getColor(substr($item->itemid,-1,1));
                        }
                        return $items;break;

                case 'fabao' :  //法宝背包
                            $sql = "select * from $this->table_lieming where pid = $pid";
                            $fabao = $this->db->query($sql)->result_object();
                            if(empty($fabao))return null;
                            $this->db->select_db($this->db_static);
                            for($i=1 ; $i < 25;$i++){
                                if($fabao->{'lieming'.$i} == 0)continue;
                                $fabao->{'type'.$i} =  intval($fabao->{'lieming'.$i} % 10000 / 100);
                                $fabao->{'colorval'.$i} = intval( $fabao->{'lieming'.$i} % 100 / 10);
                                $fabao->{'color'.$i} = $this->getColor( $fabao->{'colorval'.$i} );
                                $fabao->{'level'.$i} = intval($fabao->{'lieming'.$i} % 10) + 1;
                                $fabao->{'exp'.$i} = intval($fabao->{'lieming'.$i}/10000);
                                $fabao->{'maxexp'.$i} = 12 * pow(2,$fabao->{'level'.$i} + $fabao->{'colorval'.$i} -2);
                                //取得法宝名字
                                $sql = "select name from $this->table_fabao where type=".$fabao->{'type'.$i}." and color = ".$fabao->{'colorval'.$i};
                                $fabao->{'name'.$i} = $this->db->query($sql)->result_object()->name;
                            }
                            return $fabao;break;

                case 'qk' : //乾坤袋
                        $sql = "select itemid,num from $this->table_qiankun where pid = $pid";
                        $qiankun = $this -> db ->query($sql) -> result_objects();
                        foreach($qiankun as &$qk){
                            $temp  = fetch_object_by_key('id',$qk->itemid,$static_items);
                            $qk->name = $temp->name;
                            $qk -> color = Color::getColor(substr($qk->itemid,-1,1));
                        }
                        return $qiankun;break;

                default : return null;
            }
    }

    public function getCondition($condition){}

    private function getColor($color){
        $_color = 'green';
        switch($color){
            case 1: $_color = 'green';break;
            case 2: $_color = 'blue';break;
            case 3: $_color = 'purple';break;
            case 4: $_color = 'orange';break;
        }
        return $_color;
    }
}
