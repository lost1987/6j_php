<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-2-22
 * Time: 下午2:56
 * To change this template use File | Settings | File Templates.
 * memcached -d  -m 512 -I 5242880 -p 11211 //限定5M的单个slab
 */
class Memcaches
{
    protected  $memcache;
    protected  $host;
    protected  $port;
    protected  $timeout;

    function memcaches($host,$port,$timeout=1,$pconnect=false,$debug=FALSE){
        $this -> host = $host;
        $this -> port = $port;
        $this -> timeout = $timeout;
        $this -> memcache = new Memcache();
        if($debug){
            if($pconnect)$this -> memcache -> pconnect($host,$port,$timeout);
            else $this -> memcache -> connect($host,$port,$timeout);
        }else{
            if($pconnect)@$this -> memcache -> pconnect($host,$port,$timeout);
            else @$this -> memcache -> connect($host,$port,$timeout);
        }
    }

    function getCache(){
        if(empty($this -> memcache ))return FALSE;
        return $this -> memcache;
    }

    /**
     * 批量设置一队key,value
     * @param array $array
     * @param bool $flag  是否使用 zlib 压缩 ,当flag=MEMCACHE_COMPRESSED的时侯，数据很小的时候不会采用zlib压缩，只有数据达到一定大小才对数据进行zlib压缩。
     * @param int $expire 0为永不超时
     */
    function setList(Array $array,$flag=FALSE,$expire=0){
        foreach($array as $k => $v){
            $this -> memcache -> set($k,$v,$flag,$expire);
        }
    }

    /**
     * 批量删除一组key
     * @param array $key
     * @param $timeout 如果为0 则立刻删除,反之如果为30 则在30秒后执行删除
     */
    function delList(Array $keylist,$timeout=0){
        foreach($keylist as $key){
            $this -> memcache -> delete($key,$timeout);
        }
    }

}
