<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-5-9
 * Time: 上午10:27
 * To change this template use File | Settings | File Templates.
 */
class SkillService extends ServerDBChooser
{

    private $_profession=array(
        0 => array('混元击','挥击','撕裂','蛮栾','抑制','猛虎重击','震荡波','神魔畏'),//降魔
        1 => array('御火术','冰咆哮','玄法飞弹','灵动术','雷暴','麦旋风','雷魂','冰魄术'),//御法
        2 => array('凌风波','回春诀','御光术','八卦阵','六气术','星辰坠','迷魂阵','噩梦之种')//牧云
    );

    function SkillService(){
        $this -> table_player = $this -> prefix_1 . 'user';
        $this -> table_skill = $this->prefix_2.'userskill';
    }

    public function detail($pid,$server){
        if(!empty($server) && !empty($pid)){
            $this -> dbConnect($server,$server->dynamic_dbname);
            $sql = "select a.*,b.profession from $this->table_skill a ,$this->table_player b where pid=$pid and a.pid = b.id";
            $skill = $this -> db -> query($sql) -> result_object();
            if(empty($skill))return null;
            $skill -> skillnames = implode(',',$this->_profession[$skill->profession]);
            return $skill;
        }
        return null;
    }

    public function getCondition($condition){}
}
