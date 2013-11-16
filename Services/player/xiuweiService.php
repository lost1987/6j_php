<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-5-9
 * Time: 下午1:50
 * To change this template use File | Settings | File Templates.
 */
class XiuweiService extends  ServerDBChooser
{

    function XiuweiService(){
        $this -> table_userxiuwei = $this->prefix_2.'userxiuwei';
        $this -> table_xiuweilevel = $this -> prefix_2.'xiuwei';
        $this -> db_static = 'mmo2d_staticljzm';

        require BASEPATH.'/Common/itemeffect.php';
        $this -> item_effect = $item_effect;
    }

    public function detail($pid,$server){
        if(!empty($pid) && !empty($server)){
            $this -> dbConnect($server,$server->dynamic_dbname);
            $sql = "select layer1 as smxw,layer2 as wgxw,layer3 as fgxw from $this->table_userxiuwei where pid=$pid";
            $xw = $this -> db -> query($sql) -> result_object();
            if(empty($xw))return null;

            $xwlevel = Datacache::getXwlevel($this->db);

            if(empty($xwlevel))return null;

            $is_sm_level_inited = false;
            $is_wg_level_inited = false;
            $is_fg_level_inited = false;

            foreach($xwlevel as $xl){

                 if($xw -> smxw < $xl->val && !$is_sm_level_inited){
                     $xw -> smlevelval = $xl -> xwlevel;
                     $xw -> smlevel = '等级'.$xl -> xwlevel.',经验'.$xw->smxw;
                     $is_sm_level_inited = TRUE;
                 }

                 if($xw -> wgxw < $xl->val && !$is_wg_level_inited){
                     $xw -> wglevelval = $xl -> xwlevel;
                     $xw -> wglevel = '等级'.$xl -> xwlevel.',经验'.$xw->wgxw;
                     $is_wg_level_inited = TRUE;
                 }

                if($xw -> fgxw < $xl->val && !$is_fg_level_inited){
                    $xw -> fglevelval = $xl -> xwlevel;
                    $xw -> fglevel = '等级'.$xl -> xwlevel.',经验'.$xw->fgxw;
                    $is_fg_level_inited = TRUE;
                }
            }

            $gem = min(array($xw->smlevelval,$xw->wglevelval,$xw->fglevelval));
            $xw -> gem1 = $this -> getEffect($xwlevel[$gem] -> gemval1);
            $xw -> gem2 = $this -> getEffect($xwlevel[$gem] -> gemval2);
            $xw -> gem3 = $this -> getEffect($xwlevel[$gem] -> gemval3);
            $xw -> gem4 = $this -> getEffect($xwlevel[$gem] -> gemval4);
            $xw -> gem5 = $this -> getEffect($xwlevel[$gem] -> gemval5);
            $xw -> gem6 = $this -> getEffect($xwlevel[$gem] -> gemval6);

            return $xw;
        }
    }

    public function getCondition($condition){}

    private function getEffect($gemVal){
           $effect = '';
           if(!empty($gemVal)){
                $idx = $gemVal%100;
                if($idx > 17){//%属性
                    $val =  floor($gemVal/100)/100;
                    $effect = $this->item_effect[$idx]. '+'.$val.'%';
                }else{
                    $val = floor($gemVal/100);
                    $effect = $this->item_effect[$idx]. '+'.$val;
                }
           }
           return $effect;
    }

}
