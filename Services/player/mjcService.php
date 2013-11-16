<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-16
 * Time: 下午4:07
 * To change this template use File | Settings | File Templates.
 * 名将册
 */
class MjcService extends ServerDBChooser
{

    function MjcService(){
        $this->table_item = $this->prefix_1.'item';
        $this->table_cards = $this->prefix_2.'cards';
        $this->table_user_card = $this->prefix_2.'usercard';

        $this->db_static = 'mmo2d_staticljzm';
    }

    public function detail($pid,$server){
        if(!empty($pid) && !empty($server)){

            require BASEPATH.'/Common/itemeffect.php';

            $this->dbConnect($server,$server->dynamic_dbname);
            $sql = "select * from $this->table_user_card where pid = $pid";
            $usercard = $this->db->query($sql)->result_object();
            if(empty($usercard))return null;

            //计算牌组颜色
            $colors = array(1=>'green',2=>'blue',3=>'purple',4=>'orange');
            if($usercard->card1 != 0 && $usercard->card2!=0 && $usercard->card3!=0
            && $usercard->card4 !=0  && $usercard->card5!=0){
                $color_card1 = $usercard->card1%10;
                $color_card2 = $usercard->card2%10;
                $color_card3 = $usercard->card3%10;
                $color_card4 = $usercard->card4%10;
                $color_card5 = $usercard->card5%10;

                $usercard_min = min($color_card1,$color_card2,$color_card3,$color_card4,$color_card5);
                $usercard_color = $colors[$usercard_min];
            }

            $this->db->select_db($this->db_static);
            $sql = "select * from $this->table_cards where id = $usercard->cardid";
            $card = $this->db->query($sql)->result_object();
            if(empty($card))return null;
            $card_item_id = $this->get_card_ids($card);
            if(empty($card_item_id)) return null;
            $sql = "select name from $this->table_item where id in ($card_item_id)";
            $item_names = $this->db->query($sql)->result_objects();
            for($i = 0; $i < count($item_names);$i++){
                $card->{'cardname'.($i+1)} = $item_names[$i]->name;
            }

            if(isset($usercard_min)){
                   $card -> color = $usercard_color;
                   switch($usercard_min){
                       case 1:
                                $card->effect1  = $this->get_effect_name_and_val($card->xg1);
                                $card->effect2  = $this->get_effect_name_and_val($card->xg2);
                                break;
                       case 2:
                                $card->effect1  = $this->get_effect_name_and_val($card->xg3);
                                $card->effect2  = $this->get_effect_name_and_val($card->xg4);
                                break;
                       case 3:
                               $card->effect1  = $this->get_effect_name_and_val($card->xg5);
                               $card->effect2  = $this->get_effect_name_and_val($card->xg6);
                                break;
                       case 4:
                               $card->effect1  = $this->get_effect_name_and_val($card->xg7);
                               $card->effect2  = $this->get_effect_name_and_val($card->xg8);
                                break;
                   }
            }
            return $card;
        }
    }

    private function get_effect_name_and_val($xg){
        global $item_effect;
        $effect_type = $xg%100;
        if($effect_type > 17){
            //百分比效果
            $effectName = $item_effect[$effect_type];
            $effect_value = (($xg - $effect_type)/(100*100)) . '%';
        }else{
            //普通数值效果
            $effectName = $item_effect[$effect_type];
            $effect_value = ($xg - $effect_type)/100;
        }
        return $effectName.' +'.$effect_value;
    }

    private function get_card_ids($card){
        $card_id = '';
        for($i = 1 ; $i < 6 ; $i++ ){
            if($card->{'card'.$i} != 0 && !empty($card->{'card'.$i})){
              $card_id .= $card->{'card'.$i}.',';
            }
        }
        if(strlen($card_id) > 0)
        $card_id = substr($card_id,0,strlen($card_id)-1);
        return $card_id;
    }

    public function getCondition($condition){}

}
