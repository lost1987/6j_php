<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-7-16
 * Time: 下午4:39
 * To change this template use File | Settings | File Templates.
 */
class ShenbingService extends ServerDBChooser
{

    private $shenbing_skill_profession = array(
        0 => array(
              0=>'混元击',
              1=>'挥击',
              2=>'撕裂',
              3=>'蛮栾',
              4=>'猛虎重击',
              5=>'震荡波'
        ),

        1=> array(
            0=>'混元击',
            1=>'挥击',
            2=>'撕裂',
            3=>'蛮栾',
            4=>'猛虎重击',
            5=>'雷魂'
        )
        ,

        2=> array(
            0=>'凌风波',
            1=>'回春诀',
            2=>'御光术',
            3=>'地火冲击',
            4=>'噩梦之种',
            5=>'迷魂阵'
        )
    );

    function ShenbingService(){
        $this -> table_player = $this->prefix_1.'user';
        $this -> table_shenbing = $this->prefix_2.'usershenbing';
        $this -> table_shenbingvalue = $this->prefix_2.'usershenbingvalue';
    }


    public function detail($pid,$server){
         if(!empty($server)){
             $this->dbConnect($server,$server->dynamic_dbname);
             $shenbinglist = $this->db->query("select a.*,b.profession from $this->table_shenbingvalue a left join $this->table_player b on a.pid = b.id  where a.pid = $pid order by num asc") -> result_objects();
             if(!empty($shenbinglist)){
                     foreach($shenbinglist as &$sb)
                     {
                            switch($sb->num){
                                case 0: $sb->name = '斩玉';
                                        if($sb->isskill1)$sb->skill1 = $this->shenbing_skill_profession[$sb->profession][$sb->num];
                                        else $sb->skill1 = '/';
                                        break;
                                case 1: $sb->name = '沉虹';
                                        if($sb->isskill1)$sb->skill1 = $this->shenbing_skill_profession[$sb->profession][$sb->num];
                                        else $sb->skill1 = '/';
                                        break;
                                case 2: $sb->name = '龙牙';
                                        if($sb->isskill1)$sb->skill1 = $this->shenbing_skill_profession[$sb->profession][$sb->num];
                                        else $sb->skill1 = '/';
                                        break;
                                case 3: $sb->name = '鸣鸿';
                                        if($sb->isskill1)$sb->skill1 = $this->shenbing_skill_profession[$sb->profession][$sb->num];
                                        else $sb->skill1 = '/';
                                        break;
                                case 4: $sb->name = '龙雀';
                                        if($sb->isskill1)$sb->skill1 = $this->shenbing_skill_profession[$sb->profession][$sb->num];
                                        else $sb->skill1 = '/';
                                        break;
                            }
                     }
                     return $shenbinglist;
             }
             return null;
         }
    }


    protected function getCondition($condition)
    {
        // TODO: Implement getCondition() method.
    }

}
