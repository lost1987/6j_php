<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-27
 * Time: 下午3:02
 * To change this template use File | Settings | File Templates.
 */
abstract class ServerDBChooser
{

    protected  $db;
    protected  $prefix_1 = 'fr_';
    protected  $prefix_2 = 'fr2_';
    protected  $prefix_3 = 'ht_';

    protected  function dbConnect($server,$dbname='',$newlink=FALSE){
        //查询服务器详细
        $server_db = new DB;
        $server_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
        $server_db -> select_db(DB_NAME);
        $serverinfo = $server_db -> query("select * from ljzm_servers where id = {$server->id}") -> result_object();
        $server_db -> close();
        unset($server_db);
        $this -> db = new DB();
        $this -> db -> connect($serverinfo->ip.':'.$serverinfo->port,$serverinfo->dbuser,$serverinfo->dbpwd,$newlink);
        if(!empty($dbname))
        $this -> db -> select_db($dbname);
    }

    /**
     * @param $server_ids  包含服务器信息的数组或者只包含服务器ID的字符串
     * @return mixed
     */
    protected  function getServers($server_ids){
        if(is_array($server_ids)){
            $servers = array();
            foreach($server_ids as $server){
                $servers[] = $server->id;
            }
            $server_ids = implode(',',$servers);
        }

        $server_db = new DB();
        $server_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD,TRUE);
        $server_db -> select_db(DB_NAME);
        $server_table = DB_PREFIX.'servers';
        $sql = "select * from $server_table where id in ($server_ids)";
        $servers = $server_db->query($sql) ->result_objects();
        $server_db -> close();
        unset($server_db);
        return $servers;
    }

    protected function getServer($server_id){
        $server_db = new DB();
        $server_db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD,TRUE);
        $server_db -> select_db(DB_NAME);
        $server_table = DB_PREFIX.'servers';
        $sql = "select * from $server_table where id = $server_id";
        $server =$server_db -> query($sql) -> result_object();
        $server_db -> close();
        return $server;
    }

    protected  function dbClose(){
        $this -> db -> close();
    }

    protected abstract function getCondition($condition);

}
