<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-28
 * Time: 下午5:24
 * To change this template use File | Settings | File Templates.
 * 角色
 */
class RoleService extends ServerDBChooser
{

    function RoleService(){
        $this -> table_player = $this->prefix_1.'user';
        $this -> table_role = $this->prefix_3.'user';
    }

    public function detail($pid,$server){
        $this -> dbConnect($server,$server->dynamic_dbname);
        $sql = "select b.shengming,a.mask8,b.fali,a.mask9,a.exp,a.xingji,
         b.liliang,b.jingu,b.shenfa,b.lingli,b.conghui,b.wugong,b.wufang,
         b.fagong,b.fafang,b.shanbi,b.mingzhong,b.chuantou,b.baoji,b.jianren,
         b.gedang,a.fightcap from $this->table_player a left join $this->table_role b
         on a.id = b.pid   where a.id = $pid";

        $role = $this -> db -> query($sql) -> result_object();
        $role->mask8 = $role->mask8>60000000 ? 60000000 : $role->mask8;
        $role->mask9 = $role->mask9>60000000 ? 60000000 : $role->mask9;

        return $role;
    }

    protected function getCondition($condition)
    {
        // TODO: Implement getCondition() method.
    }

}
