<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-10-8
 * Time: 下午4:35
 * To change this template use File | Settings | File Templates.
 */
class Baseapi
{
        protected  $input;
        protected  $db;
        protected  $server;
        protected  $sid;//原始服务器ID

        function Baseapi(){
            $this->input = new Input();
            $serverid = $this->paramValidation();

            $this -> db = new DB;
            $this -> db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
            $this -> db -> select_db('mmo2d_admin');
            $this -> server = $this -> db -> select('*') -> from('ljzm_servers') -> where("id=$serverid") -> get() -> result_object();
            $this -> db -> connect($this->server->ip.':'.$this->server->port,
                                                $this->server->dbuser,$this->server->dbpwd);
            $this -> db -> select_db($this->server->dynamic_dbname);
        }

        public function paramValidation(){
            $time = $this-> input -> get('time');
            $key = $this -> input -> get('key');
            $sid = $this -> input -> get('sid');
            $bid = $this -> input -> get('bid');

            //md5(sha1());
            $mykey = md5(sha1($time.API_KEY));

            if($mykey != $key || empty($sid) || empty($bid)){
                echo -1;
                exit;
            }

            $this -> sid = $sid;
            $serverid =  $bid*10000 + intval($sid);
            return $serverid;
        }
}
