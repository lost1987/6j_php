<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-5-9
 * Time: 下午4:44
 * To change this template use File | Settings | File Templates.
 * memcache 缓存数据类
 */
class Datacache
{

    public static $mc;

    //修为静态表数据
    public static function getXwlevel($db){
     /*   if(empty(self::$mc))self::$mc = new Memcaches(MEMCACHED_HOST,MEMCACHED_PORT);
        $xiuweilevel = @self::$mc  -> getCache() -> get('ljzm_xiuweilevel');
        if(!$xiuweilevel or empty($xiuweilevel)){*/
            $db -> select_db('mmo2d_staticljzm');
            $sql = "select * from fr2_xiuwei order by val asc";
            $xiuweilevel = $db -> query($sql) -> result_objects();
        //    @self::$mc  -> getCache() -> set('ljzm_xiuweilevel',$xiuweilevel,FALSE,MEMCACHED_TIMEOUT);
       // }
        return $xiuweilevel;
    }

    //静态物品表
    public static function getStaticItems($db){
     /*   if(empty(self::$mc))self::$mc = new Memcaches(MEMCACHED_HOST,MEMCACHED_PORT);
        $items = @self::$mc -> getCache() -> get('ljzm_staticitems');
        if(!$items or empty($items)){*/
            $db -> select_db('mmo2d_staticljzm');
            $sql = "select id,name from fr_item";
            $items = $db -> query($sql) -> result_objects();
       /*     @self::$mc -> getCache() -> set('ljzm_staticitems',$items,FALSE,MEMCACHED_TIMEOUT);
        }*/
        return $items;
    }

    //静态伙伴表
    public static function getHuobans($db){
       /* if(empty(self::$mc))self::$mc = new Memcaches(MEMCACHED_HOST,MEMCACHED_PORT);
        $huobans = @self::$mc->getCache()->get('ljzm_huobans');
        if(!$huobans or empty($huobans)){*/
            $db->select_db('mmo2d_staticljzm');
            $sql = "select * from fr2_huobans";
            $huobans = $db -> query($sql) -> result_objects();
           /* @self::$mc -> getCache() -> set('ljzm_huobans',$huobans,FALSE,MEMCACHED_TIMEOUT);
        }*/
        return $huobans;
    }

}
