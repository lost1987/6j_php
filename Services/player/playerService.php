<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-27
 * Time: 下午2:42
 * To change this template use File | Settings | File Templates.
 * 玩家
 */
class PlayerService extends ServerDBChooser
{

     private $_profession = array('降魔','御法','牧云');
     private $_camp = array(
         1 => '无',
         17 => '阴阳',
         33 => '浑沌'
     );
     private $_rightcode = array(0 => '防沉迷',1=>'未沉迷');
     private $_partyjob = array(
         0 => '',
         1 => '帮主',
         2 => '长老',
         3 => '官员',
         4 => '精英',
         5 => '成员'
     );

     private $_map_location = array(
         101 => '梵天城',
         102 => '灵柩沙漠',
         103 => '玛拉王庙',
         105 => '流沙古城',
         106 => '枉生城',
         107 => '方寸山',
         108 => '万利城',
         109 => '祥云山',
         110 => '梵天城郊',
         111 => '海怪巢穴',
         112 => '星灵山',
         113 => '雪山山脊',
         201 => '江湖村',
         202 => '桃花村',
         204 => '乙木仙境',
         205 => '轩辕冢',
         206 => '轮回之塔',
         207 => '九目山',
         208 => '矿洞',
         209 => '天城废墟',
         301 => '破碎神殿',
         302 => '祥云洞',
         303 => '黑风寨',
         304 => '通天国',
         305 => '江湖村郊',
         306 => '八龙困锁阵',
         307 => '塔林地下',
         308 => '戈壁',
         309 => '神奇山谷',
         311 => '虚空之门',
         312 => '寐龙镇',
         313 => '万利城郊',
         314 => '方寸山脚',
         401 => '城',
         402 => '桃花深处',
         403 => '城内',
         404 => '园林',
         405 => '荒野',
         406 => '松林草原',
         407 => '沙漠',
         408 => '大地伤疤',
         409 => '雪原',
         414 => '地心之井',
         410 => '海滩',
         411 => '雪原2',
         213 => '离火惊门',
         211 => '炼狱门',
         210 => '迷魂阵',
         501 => '白杨谷',
         502 => '赤砾荒原',
         212 => '水牢'
     );

    private $jingyingfuben = array(
        0 => '人界',
        1 => '鬼界',
        2 => '魔界',
        3 => '神界',
        4 => '妖界',
        5 => '仙界',
        6 => '天界',
        7 => '煞界',
        8 => '异界'
    );

     function PlayerService(){
            $this -> table_player = $this->prefix_1.'user';
            $this -> table_base = $this->prefix_2.'base';
            $this -> table_rank = $this->prefix_2.'rank';
            $this -> table_playerrank = $this->prefix_2.'playerrank';
            $this -> table_userother = $this->prefix_1.'userothervalue';
            $this -> table_map = $this->prefix_1.'map';
            $this -> table_tianshen = $this->prefix_2.'usertianshen';
            $this -> table_jingying = $this->prefix_2.'userjingying';
            $this -> table_limit = $this->prefix_3.'limit';

            $this -> db_base = 'mmo2d_baseljzm';
            $this -> db_static = 'mmo2d_staticljzm';
     }

     public function lists($page,$condition){
           $this -> dbConnect($condition->server,$condition->server->dynamic_dbname);
           $condition_sql = $this -> getCondition($condition);
           $orderSql = $this->getOrder($condition->order);

           $list = $this -> db -> select("account_id,account_name,id,name,profession,levels,money,yuanbao,camp,partyname,server")
                               -> from($this->table_player)
                               -> where($condition_sql)
                               -> order_by($orderSql)
                               -> limit($page->start,$page->limit,$orderSql)
                               -> get()
                               -> result_objects();

           foreach($list as &$obj){
               $obj -> servername = $condition->server->name;
               $obj -> profession = $this -> _profession[$obj->profession];
               $obj -> camp  = $this -> _camp[$obj->camp];
           }
           $this -> dbClose();
           return $list;
     }

    public  function num_rows($condition){
        $this -> dbConnect($condition->server,$condition->server->dynamic_dbname);
        $condition_sql = $this -> getCondition($condition);
        $sql = "select count(account_id) as num from $this->table_player $condition_sql";
        $num = $this -> db -> query($sql) -> result_object() -> num;
        $this -> dbClose();
        if(is_null($num))return 0;
        return $num;
    }

    protected  function getCondition($condition){
        $account_or_name = str_replace(' ','',$condition -> account_or_name);
        $opstate = intval($condition->opstate);
        $condition_sql = '';

        if($condition->onlinestatus == 1){
            $condition_sql .= "   defencecap = 1 and state = $opstate ";
        }else if($condition->onlinestatus == 2){
            $condition_sql .= ' defencecap <> 1 or state <>0 ';
        }else{
            $condition_sql .= " state = $opstate "  ;
        }

        if(!empty($account_or_name)){
            if(empty($condition_sql)){
                 $condition_sql .= "  (account_name like '$account_or_name%' or name like '$account_or_name%')";
            }else{
                $condition_sql .= "and  (account_name like '$account_or_name%' or name like '$account_or_name%')";
            }
        }

        if(!empty($condition_sql))
            $condition_sql = ' where '.$condition_sql;
        else
            $condition_sql = " where state = $opstate";
        return $condition_sql;
    }

    private  function getOrder($order){
        switch($order){
            case 0: $orderSql = "order by levels desc";
                    break;
            case 1: $orderSql = "order by levels desc";
                    break;
            case 2: $orderSql = "order by yuanbao desc";
                    break;
            case 3: $orderSql = "order by money desc";
                    break;
        }
        return $orderSql;
    }

    public function detail($id,$server){
        $this -> dbConnect($server,$server->dynamic_dbname);
        $createdate = $this->db->datetime('a.createdate');
        $lastlogin = $this->db->datetime('a.last_login');
        $lastlogout = $this->db->datetime('a.last_logout');
        $sql = "select a.id,a.account_name,a.name,a.defencecap,a.state,$createdate as createdate,$lastlogin as last_login,a.last_ip,a.onlinetime,a.loginday,
                $lastlogout as last_logout,a.camp,a.levels,a.profession,a.yuanbao,a.money,a.mask0,a.partyname,a.account_id,
                a.partyjob,a.banggong,a.rongyu,a.lingqi,a.mask18,a.recordmap_id,a.recordx,a.recordy,b.pvalue,c.rankid,c.rankpt,d.lev,d.exp,e.CurLayer,e.CurRound
                from $this->table_player a left join $this->table_userother b on a.id = b.id and b.pindex=0 left join
                $this->table_playerrank c on a.id = c.pid left join $this->table_tianshen d on a.id = d.pid
                left join $this->table_jingying e on a.id=e.uid where a.id = $id";

        $player = $this -> db -> query($sql) -> result_object();

        //查询充值
       $this -> db -> select_db($this->db_base);
       $sql = "select yuanbao as yuanbaototal,yuanbaonum,rightcode from $this->table_base where aountid = $player->account_id";
       $base = $this -> db -> query($sql) -> result_object();
       $player -> yuanbaototal  = $base -> yuanbaototal/10;
       $player -> yuanbaonum = $base -> yuanbaonum;
       $player -> rightcode =  empty($this->_rightcode[$base->rightcode]) ? '/' : $this->_rightcode[$base->rightcode];
       $player -> jingyingname = empty($this->jingyingfuben[$player->CurLayer]) ? '/' : $this->jingyingfuben[$player->CurLayer];


       $this -> db -> select_db($this->db_static);

       //查询军衔
       $player->rankpt = '';
       $player->rankname='';
       $player->ranklimit = '';
       if(!empty($player->rankid)){
           $sql = "select a.name,a.rankpt as ranklimit from $this->table_rank a where a.rankid = $player->rankid";
           $rank = $this -> db -> query($sql) -> result_object();
           if(!empty($rank -> name)){
               $player -> rankname = $rank -> name;
               $player -> ranklimit = $rank -> ranklimit;
           }
       }

       //查询地图
       if(!empty($player->recordmap_id)){
           $sql = "select name from $this->table_map where id= $player->recordmap_id";
           $map = $this -> db -> query($sql) -> result_object();
           if(!empty($map->name))
           $player->map = $this -> _map_location[$map -> name];
       }


        if($player->defencecap == 1 && $player->state == 0){
           $player -> login_status = '在线';
       }else{
           $player -> login_status = '离线';
       }


       $player -> partyjob = $this -> _partyjob[$player->partyjob];
       $player -> camp = $this -> _camp[$player->camp];
       $player -> profession = $this -> _profession[$player->profession];
       $player -> vip = $player->mask0 % 100;
       $player -> jingli = intval($player->mask0 % 1000000 / 100);

        //查询IP地理位置
        $QQWry = new QQWry;
        $QQWry->QQWry($player->last_ip);
        $player -> location =  $QQWry->Country.$QQWry->Local;

       return $player;
    }

    /**
     * @param $servers Array
     */
    public function playerlist($servers,$online){
        if(count($servers) > 0){
           $cond = '';
           if($online == 1)$cond = ' where defencecap = 1 and state=0';
           $list = array();
           foreach($servers as $server){
               $this -> dbConnect($server,$server->dynamic_dbname);
               $sql = "select id,name from $this->table_player $cond";
               $templist  =  $this -> db -> query($sql) -> result_objects();
               foreach($templist as $temp){
                   $temp->server = $server;
                   $temp->sid = $server->id;
                   $temp->displayName = $temp->name.'_'.$server->name;
                   $list[] = $temp;
               }
           }
            return $list;
        }
    }




    public function playerSearch($servers,$condition){
           $list = array();
           $playername_or_id = $condition->playername_or_id;
           $online = $condition->online;

           $cond = " name like '%$playername_or_id%'";
           if(is_numeric($playername_or_id))$cond = " id = $playername_or_id";

           $condonline = '';
           if($online)$condonline = ' and defencecap = 1 and state=0';
           foreach ($servers as $server){
               $this->dbConnect($server,$server->dynamic_dbname);
               $sql = "select id,name from $this->table_player where $cond $condonline";
               $results = $this->db->query($sql)->result_objects();
               foreach($results as $result){
                   $result  -> name .= '___'.$server->name;
                   $result  -> server = $server;
                   $list[] = $result;
               }
               $this->dbClose();
           }
           return $list;
    }

    public function GMOperation($players,$server,$code){
          if(empty($code) && $code!=0)return -1;//操作失败
          $this->dbConnect($server,$server->dynamic_dbname);

        $update_ids = array();
        $insert_ids = array();
        foreach($players as $player){
                //判断PID有没有 然后判断是执行insert 还是 update
                $flag = $this -> db -> select("count(pid) as num") -> from($this->table_limit) -> where("pid = $player->id") ->get()->result_object()->num;
                if($flag > 0)$update_ids[]=$player->id;
                else $insert_ids[]=$player->id;
          }

        $this -> db -> trans_begin();

        try{

             if(count($update_ids) > 0){
                    $update_ids = implode(',',$update_ids);
                    if(!$this -> db ->query("update $this->table_limit set state=$code where pid in ($update_ids)")->queryState)
                    throw new Exception('更新数据失败');
             }

            if(count($insert_ids) > 0){
                $values = '';
                foreach($insert_ids as $pid){
                    $values .= "($pid,$code),";
                }
                $values = substr($values,0,strlen($values)-1);

                if(!$this -> db ->query("insert into  $this->table_limit (pid,state) values  $values")->queryState)
                    throw new Exception('写入数据失败');
            }
            $this->db->commit();
            return 1;
        }catch (Exception $e){
            $this -> db -> rollback();
            return -1;
        }
    }

}
