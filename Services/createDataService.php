<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-20
 * Time: 下午1:53
 * To change this template use File | Settings | File Templates.
 * 创建注册
 */
class CreateDataService extends Service
{
    function CreateDataService(){
        parent::__construct();
        $this -> table_createData = 'createdata';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($condition){
        $server_ids = $condition -> server_ids;
        $starttime = strtotime($condition->starttime.' 00:00:00');
        $endtime = strtotime($condition->endtime.' 23:59:59');
        $timediff = $condition->timediff;

        $date = $this->db->timestamp('date');
        $timecondition = " $date >= '$starttime' and $date <= '$endtime' ";
        $sql = '';
        switch($timediff){
            //所有
            case 1:
                    $sql = "select date,registernum,createnum from $this->table_createDate where sid in ($server_ids) and $timecondition order by date asc";
                    break;
            //24小时
            case 2:
                    $sql = "select left(date,10) as date,sum(registernum) as registernum,sum(createnum) as createnum from $this->table_createData where sid in ($server_ids) and $timecondition
                            group by left(date,10) order by date asc";
        }

        $list = $this -> db -> query($sql) -> result_objects();

        if($timediff == 1){//因flex端无法识别 YYYY-MM-DD HH:NN:SS的格式所以这里做下处理
            foreach($list as &$obj){
                $dateCollection = explode(' ',$obj->date);
                $date = explode('/',$dateCollection[0]);
                $time = explode(':',$dateCollection[1]);
                $obj->date = implode('|',array($date[0],$date[1],$date[2],$time[0],$time[1],$time[2]));
            }
        }else{
            foreach($list as &$obj){
                $obj->date = date('Y-m-d',strtotime($obj->date));
            }
        }

        return $list;
    }

}
