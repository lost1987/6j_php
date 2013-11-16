<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-29
 * Time: 下午2:59
 * To change this template use File | Settings | File Templates.
 */
class FacilityService extends ServerDBChooser
{
    function FacilityService(){
        $this -> table_sw = $this->prefix_2.'sw';
        $this -> table_facility = $this->prefix_2.'userfacility';

        $this -> db_static = 'mmo2d_staticljzm';
    }

    public function detail($pid,$server){
        $this -> dbConnect($server,$this -> db_static);
        $sql = "select id,sw,name from $this->table_sw order by sw asc";
        $total_sw = $this -> db -> query($sql) -> result_objects();


        $this -> db -> select_db($server->dynamic_dbname);
        $sql = "select facility1,facility2,facility3 from $this->table_facility where pid = $pid";
        $facility = $this -> db -> query($sql) -> result_object();
        if(!empty($facility->facility1)){
            $sw = $this -> fetchSw($facility->facility1,$total_sw);
            $facility -> facility1 = '铁匠铺声望   ['.$sw['name'].$facility->facility1.'/'.$sw['nextsw'].']';
            $sw = $this -> fetchSw($facility->facility2,$total_sw);
            $facility -> facility2 = '客栈声望   ['.$sw['name'].$facility->facility2.'/'.$sw['nextsw'].']';
            $sw = $this -> fetchSw($facility->facility3,$total_sw);
            $facility -> facility3 = '酒馆声望   ['.$sw['name'].$facility->facility3.'/'.$sw['nextsw'].']';
        }
        return $facility;
    }

    protected function getCondition($condition)
    {
        // TODO: Implement getCondition() method.
    }


    //获取当前角色的声望中文名和下一级升级的声望峰值
    private function fetchSw($currentSw,$totalSw){
        $sw  = array();
        for($i =0; $i< count($totalSw) ; $i++){
             if($currentSw < $totalSw[$i] -> sw){
                    $sw['name'] = $i-1 < 0 ? $totalSw[$i] -> name : $totalSw[$i-1] -> name;
                    $sw['nextsw'] = $totalSw[$i] -> sw;
                    break;
             }
         }
        return $sw;
    }
}
