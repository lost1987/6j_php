<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-11
 * Time: 下午12:43
 * To change this template use File | Settings | File Templates.
 */
interface  IService
{
    public   function lists($page,$condition=null);

    public  function save($obj);

    public   function edit($id);

    public   function num_rows($null);

    public  function del($ids);


}
