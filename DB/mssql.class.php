<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-6
 * Time: 上午11:35
 * To change this template use File | Settings | File Templates.
 */
class Mssql
{

    public static $debug=FALSE;

    public $link;

    public $queryState;

    private $_sql;

    private $_limit_pre;

    private $_limit_after;

    private $_table;

    private $_condition;

    private $_on_condition;

    private $_order_by;

    private $_group_by;

    function Mssql(){
        $this -> flush();
    }

    public function connect($DB_HOST,$DB_USER,$DBPWD,$NEWLINK=FALSE){
        $this->link = mssql_connect($DB_HOST,$DB_USER,$DBPWD,$NEWLINK);
        return $this->link;
    }

    public function charset($charset){
    }

    public function select_db($dbname){
        return mssql_select_db($dbname,$this->link);
    }

    public function query($sql){
        if(!empty($this->link))
            $this -> queryState = mssql_query($sql,$this->link);
        return $this;
    }


    public function result_objects(){
        if(!empty($this->queryState)){
            $list = array();
            while($row = mssql_fetch_object($this->queryState)){
                $list[] = $row;
            }
            return $list;
        }
        return FALSE;
    }

    public function result_object(){
        if(!empty($this->queryState)){
            if($row = mssql_fetch_object($this->queryState)){
                return  $row;
            }
        }
        return FALSE;
    }

    public function insert_id($table){
        $sql = "SELECT IDENT_CURRENT('$table')  AS  insert_id";
        return $this->query($sql)->result_object()->insert_id;
    }

    public function close(){
        mssql_close($this->link);
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

    public function limit($start,$limit,$order='id asc'){
        if(stripos($order,'ORDER') > -1)$order = str_ireplace('ORDER','',$order);
        if(stripos($order,'BY') > -1)$order = str_ireplace('BY','',$order);
        $this->_limit_pre = "SELECT * FROM (SELECT row_number() OVER (ORDER BY $order) AS rownumber,  ";
        $this->_limit_after = " ) AS t WHERE t.rownumber > $start AND t.rownumber <= $limit";
        $this->_sql = str_ireplace('SELECT',' ',$this->_sql);
        return $this;
    }

    public function one(){
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

    public function get($flush=TRUE){
        if(self::$debug)
            error_log($this->_limit_pre.
                $this->_sql.
                $this->_table.
                $this->_on_condition.
                $this->_condition.
                $this->_group_by.
                $this->_limit_after.
                $this->_order_by);
        $this->queryState = mssql_query(
            $this->_limit_pre.
                $this->_sql.
                $this->_table.
                $this->_on_condition.
                $this->_condition.
                $this->_group_by.
                $this->_limit_after.
                $this->_order_by
            ,$this->link);

        if($flush)$this->flush();
        return $this;
    }

    public function fetch($flush=TRUE){
        $sql = $this->_limit_pre.
            $this->_sql.
            $this->_table.
            $this->_on_condition.
            $this->_condition.
            $this->_group_by.
            $this->_limit_after.
            $this->_order_by;

        if($flush)$this->flush();
        return $sql;
    }

    private function flush(){
        $this -> _sql = '';
        $this -> _limit_pre = '';
        $this -> _limit_after = '';
        $this -> _table = '';
        $this -> _condition = '';
        $this -> _on_condition = '';
        $this -> _order_by = '';
        $this -> _group_by = '';
    }

    public function datetime($columnName,$limit=20,$total=120){
        return "CONVERT(varchar($limit), $columnName, $total)";
    }


    public function cast($columnName){
        return "CAST($columnName as datetime)";
    }

    public function trans_begin(){
        mssql_query('BEGIN TRAN',$this->link);
    }

    public function commit(){
        mssql_query('COMMIT TRAN',$this->link);
    }

    public function rollback(){
        mssql_query('ROLLBACK TRAN',$this->link);
    }

    public function timestamp($columnName){
        return '';
    }

    public function fromunixtime($columnName,$format='%Y-%m-%d %H:%i:%S'){
        return '';
    }

    /**
     * 大数据批量写入 , 一定要配合事务使用
     * @param array $columns 每个元素具有相同数据结构的无一维key且二维含有key-value 的数组[key是字段名value是值]
     * @param $tableName
     * @param int $num_per_time 每隔多少行写入一次数据
     * @return bool
     */
    public function insert_multi(Array $columns,$tableName,$num_per_time = 100){
        if(!is_array($columns) || !is_array($columns[0]) || empty($tableName))
            return FALSE;
        $cur = 1; //定义游标
        $total = count($columns);
        $sql_pre = "INSERT INTO $tableName (";
        $tempColumnNames =  array_keys($columns[0]);
        asort($tempColumnNames);//按值进行排序,不能使用ksort 因为对无key的数组会自动补数字key
        $sql_pre.= implode(',',$tempColumnNames).')  ';
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
                if(!$this->query($sql)){
                    break;
                }
                $sql = $sql_pre.' SELECT '.implode(',',$valuefields);
            }
            else if($cur == 1){
                $sql .= '  SELECT '.implode(',',$valuefields);
            }
            else{
                $sql .= ' UNION ALL SELECT '.implode(',',$valuefields);
            }

            if($total == $cur){
                if(!$this->query($sql)){
                    break;
                }
            }

            $cur++;
        }
        return TRUE;
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
