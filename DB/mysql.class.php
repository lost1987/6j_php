<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-6
 * Time: 上午11:35
 * To change this template use File | Settings | File Templates.
 */
class Mysql
{

    public static $debug=FALSE;

    public $link;

    public $queryState;

    private $_sql;

    private $_limit;

    private $_table;

    private $_condition;

    private $_on_condition;

    private $_order_by;

    private $_group_by;

    private $_xid=null;

    function mysql(){
        $this -> flush();
    }

    public function connect($DB_HOST,$DB_USER,$DBPWD,$NEWLINK=FALSE){
        $this->link = mysql_connect($DB_HOST,$DB_USER,$DBPWD,$NEWLINK);
        $this->charset('UTF8',$this->link);
        return $this->link;
    }

    public function charset($charset){
        mysql_set_charset($charset,$this->link);
    }

    public function select_db($dbname){
        return   mysql_select_db($dbname,$this->link);
    }

    public function query($sql){
        if(!empty($this->link))
            $this -> queryState = mysql_query($sql,$this->link);
        return $this;
    }


    public function result_array(){
        if(!empty($this->queryState)){
            $list = array();
            while($row = mysql_fetch_assoc($this->queryState)){
                $list[] = $row;
            }
            return $list;
        }
        return FALSE;
    }


    public function row_array(){
        if(!empty($this->queryState)){
            if($row = mysql_fetch_assoc($this->queryState)){
                return  $row;
            }
        }
        return FALSE;
    }


    public function result_objects(){
        if(!empty($this->queryState)){
            $list = array();
            while($row = mysql_fetch_object($this->queryState)){
                $list[] = $row;
            }
            return $list;
        }
        return FALSE;
    }

    public function result_object(){
        if(!empty($this->queryState)){
            if($row = mysql_fetch_object($this->queryState)){
                return  $row;
            }
        }
        return FALSE;
    }

    public function insert_id($table=null){
        return mysql_insert_id($this->link);
    }

    public function close(){
        mysql_close($this->link);
    }

    /**
     * @addonal 扩展方法
     */

    public function select($sql){
        $this -> _sql = 'SELECT '.$sql;
        return $this;
    }

    public function from($table){
        $this -> _table = ' FROM '.$table;
        return $this;
    }

    public function where($condition){
        if(empty($condition))return $this;

        $testCondition = trim($condition);
        if(preg_match('/((^[ ]*?)|(^))WHERE(.*)/i',$testCondition))
            $condition = preg_replace('/((^[ ]*?)|(^))[ ]WHERE(.*)/i','$4',$condition);
        if(preg_match('/((^[ ]*?)|(^))WHERE(.*)/i',$this->_condition))
            $this -> _condition = preg_replace('/((^[ ]*?)|(^))WHERE(.*)/i','$4',$this->_condition);

        $this -> _condition = " WHERE $this->_condition $condition ";
        return $this;
    }

    public function on($on_condition){
        if(empty($on_condition))return $this;

        $this -> _on_condition = " ON $on_condition ";
        return $this;
    }

    public function limit($start,$limit,$order=null){
        $this->_limit = " LIMIT $start,$limit ";
        if(empty($this->_order_by) && !empty($order))
            $this->order_by($order);
        return $this;
    }

    public  function one(){
        $this->_limit = ' LIMIT 1 ';
        return $this;
    }

    public function order_by($order){
        if(stripos($order,'ORDER') > -1)$order = str_ireplace('ORDER','',$order);
        if(stripos($order,'BY') > -1)$order = str_ireplace('BY','',$order);
        $this -> _order_by = ' ORDER BY '.$order;
        return $this;
    }

    public function group_by($group_by){
        $this -> _group_by = ' GROUP BY '.$group_by;
        return $this;
    }

    //执行查询
    public function get($flush=TRUE){
        if(self::$debug)
            error_log( $this->_sql.
                $this->_table.
                $this->_on_condition.
                $this->_condition.
                $this->_group_by.
                $this->_order_by.
                $this->_limit);

        $this->queryState =  mysql_query(
            $this->_sql.
                $this->_table.
                $this->_on_condition.
                $this->_condition.
                $this->_group_by.
                $this->_order_by.
                $this->_limit
            ,$this->link);

        if($flush)$this->flush();
        return $this;
    }

    //返回当前的sql语句
    public function fetch($flush=TRUE){
        $sql =  $this->_sql.
            $this->_table.
            $this->_on_condition.
            $this->_condition.
            $this->_group_by.
            $this->_order_by.
            $this->_limit;

        if($flush)$this->flush();
        return $sql;
    }

    //清空所有变量
    private function flush(){
        $this -> _sql = '';
        $this -> _limit = '';
        $this -> _table = '';
        $this -> _condition = '';
        $this -> _on_condition = '';
        $this -> _order_by = '';
        $this -> _group_by = '';
    }

    public function datetime($columnName,$limit=20,$total=120){
        return $columnName;
    }

    public function cast($columnName){
        return $columnName;
    }

    public function trans_begin(){
        mysql_query('SET AUTOCOMMIT = 0',$this->link);
        mysql_query('BEGIN',$this->link);
    }

    public function commit(){
        mysql_query('COMMIT',$this->link);
        mysql_query('END',$this->link);
        mysql_query('SET AUTOCOMMIT = 1',$this->link);
    }

    public function rollback(){
        mysql_query('ROLLBACK',$this->link);
        mysql_query('END',$this->link);
        mysql_query('SET AUTOCOMMIT = 1',$this->link);
    }

    public function setXID($xid){
        $this -> _xid = $xid;
    }

    public function xa_start(){
        if(is_null($this->_xid))throw new Exception('xid has not set');
        mysql_query("XA START '$this->_xid'",$this->link);
    }

    public function xa_end(){
        if(is_null($this->_xid))throw new Exception('xid has not set');
        mysql_query("XA END '$this->_xid'",$this->link);
    }

    public function xa_prepare(){
        if(is_null($this->_xid))throw new Exception('xid has not set');
        mysql_query("XA PREPARE '$this->_xid'",$this->link);
    }

    public function xa_commit(){
        if(is_null($this->_xid))throw new Exception('xid has not set');
        mysql_query("XA COMMIT '$this->_xid'",$this->link);
        $this->_xid = null;
    }

    public function xa_rollback(){
        if(is_null($this->_xid))throw new Exception('xid has not set');
        mysql_query("XA ROLLBACK '$this->_xid'",$this->link);
        $this->_xid = null;
    }

    public function timestamp($columnName){
        return " UNIX_TIMESTAMP($columnName) ";
    }

    public function fromunixtime($columnName,$format='%Y-%m-%d %H:%i:%S'){
        return " FROM_UNIXTIME($columnName,'$format')";
    }

    /**
     * 大数据批量写入 , 一定要配合事务使用
     * @param array $columns 每个元素具有相同数据结构的无一维key且二维含有key-value 的数组[key是字段名value是值]
     * @param $tableName
     * @param int $num_per_time 每隔多少行写入一次数据
     * @return bool
     */
    public function insert_multi(Array $columns,$tableName,$num_per_time = 100){
        $exec_result = TRUE;
        if(!is_array($columns) || !is_array($columns[0]) || empty($tableName))
            return FALSE;
        $cur = 1; //定义游标
        $total = count($columns);
        $sql_pre = "INSERT INTO $tableName (";
        $tempColumnNames =  array_keys($columns[0]);
        asort($tempColumnNames);//按值进行排序,不能使用ksort 因为对无key的数组会自动补数字key
        $sql_pre.= implode(',',$tempColumnNames).') VALUES ';
        $sql = $sql_pre;

        foreach($columns as $column){
            ksort($column);
            $valuefields = array();
            foreach($column as $k=>$v){
                if(is_string($v))
                    $valuefields[] = "'$v'";
                else
                    $valuefields[] = $v;
            }

            if($cur%$num_per_time == 0){
                $sql = substr($sql,0,strlen($sql)-1);//减去末尾的逗号
                if(!$this->query($sql)->queryState){
                    $exec_result = FALSE;
                    break;
                }
                $sql = $sql_pre.'('.implode(',',$valuefields).'),';
            }
            else{
                $sql .= '('.implode(',',$valuefields).'),';
            }

            if($total == $cur){
                $sql = substr($sql,0,strlen($sql)-1);//减去末尾的逗号
                if(!$this->query($sql)->queryState){
                    $exec_result = FALSE;
                    break;
                }
            }

            $cur++;
        }
        return $exec_result;
    }

    public function insert($tablename , $array){
        $sql = "INSERT INTO ";
        $sql_key = '(';
        $sql_val = '(';
        if(is_array($array) && count($array)>0 && !empty($tablename)){
            foreach($array as $ckey => $cvalue){
                $sql_key .= "$ckey,";
                if(gettype($cvalue) == 'string' || (empty($cvalue) && $cvalue!=0)){
                    $sql_val .= "'$cvalue',";
                }else{
                    $sql_val .= "$cvalue,";
                }
            }
            $sql_key = substr($sql_key,0,strlen($sql_key) - 1);
            $sql_val = substr($sql_val,0,strlen($sql_val) - 1);
            $sql_key .= ') ';
            $sql_val .= ')';
            $sql .= $tablename." ".$sql_key.' VALUES '.$sql_val ;

            if($this->query($sql)){
                return TRUE;
            }
            return FALSE;
        }
    }

    /**
     * @param $tablename
     * @param $array
     * @param $condition exp: id=5
     */
    public function update($tablename,$array,$condition){
        $sql = "UPDATE ";
        if(is_array($array) && count($array)>0 && !empty($tablename) && !empty($condition)){
            $sql .= "$tablename SET ";
            foreach($array as $ckey => $cvalue){
                $sql .= "$ckey=";

                if(gettype($cvalue) == 'string' || (empty($cvalue) && $cvalue!=0)){
                    $sql .= "'$cvalue',";
                }else{
                    $sql .= "$cvalue,";
                }
            }
            $sql = substr($sql,0,strlen($sql) - 1);
            $sql .= " WHERE ".$condition;

            if($this->query($sql)){
                return TRUE;
            }
            return FALSE;
        }
    }

}
