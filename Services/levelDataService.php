<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-21
 * Time: 下午1:47
 * To change this template use File | Settings | File Templates.
 */
class LevelDataService extends Service
{
    function LevelDataService(){
        parent::__construct();
        $this -> table_level = 'leveldata';
        $this -> db -> select_db('mmo2d_recordljzm');
    }

    public function lists($condition){
        $server_ids = $condition -> server_ids;
        $sql = "select  levels,sum(levelsnum) as levelsnum,sum(levelspercent) as levelspercent,sum(offline24) as offline24,sum(offline24percent) as offline24percent,sum(offline72) as offline72,sum(offline72percent) as offline72percent,sum(xmnum) as xmnum,sum(yfnum) as yfnum,sum(mynum) as mynum
         from $this->table_level where sid in ($server_ids)   group by levels";
        $list = $this -> db -> query($sql) -> result_objects();
        foreach($list as &$obj){
            $obj -> levelspercent = number_format($obj->levelspercent,2)*100 . '%';
            $obj -> offline24percent = number_format($obj->offline24percent,2)*100 . '%';
            $obj -> offline72percent = number_format($obj->offline72percent,2)*100 . '%';
        }
        return $list;
    }

    public function num_rows($condition){
        $server_ids = $condition -> server_ids;
        $sql = "select count(distinct(levels)) as num from $this->table_level where sid in ($server_ids) ";
        return $this -> db -> query($sql) -> result_object() -> num;
    }

    public function total($condition){
        $server_ids = $condition -> server_ids;

        $sql = "select
         sum(levelsnum) as levelsnum,sum(offline24) as offline24,sum(offline72) as offline72,sum(xmnum) as xmnum,sum(yfnum) as yfnum,sum(mynum) as mynum
         from $this->table_level where sid in ($server_ids)";

        $obj = $this -> db -> query($sql) -> result_object();
        return $obj;
    }
}
