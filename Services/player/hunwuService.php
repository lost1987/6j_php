<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-29
 * Time: 下午6:46
 * To change this template use File | Settings | File Templates.
 */
class HunwuService extends  ServerDBChooser
{

    private $hwCollection = array(
        0 => '天蝎',
        1 => '腾蛇',
        2 => '囚牛',
        3 => '睚眦',
        4 => '狴犴',
        5 => '螭吻',
        6 => '蒲牢',
        7 => '嘲风',
        8 => '娥皇',
        9 => '穷奇'
    );

    function HunwuService(){
        $this -> table_hw = $this->prefix_2.'playerhunwu';
    }

    public function detail($pid,$server){
        $this -> dbConnect($server,$server->dynamic_dbname);
        $sql = "select * from $this->table_hw where pid = $pid";
        $hw = $this -> db -> query($sql) -> result_object();
        if(!empty($hw->hwlevel0)){
            for($i=0;$i<10;$i++){
                $hw -> {'name'.$i} = $this->hwCollection[$i];
                $hw -> {'xing'.$i} = intval($hw->{'hwlevel'.$i}/20);
                $hw -> {'ceng'.$i} = $hw->{'hwlevel'.$i}%20;
                $hw -> {'hunwu'.$i} = $hw -> {'name'.$i} . ' ['.$hw -> {'xing'.$i}.'星,'.$hw -> {'ceng'.$i}.'层 ]';
            }
        }
        return $hw;
    }

    protected function getCondition($condition)
    {
        // TODO: Implement getCondition() method.
    }
}
