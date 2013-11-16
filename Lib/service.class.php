<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-3-6
 * Time: 上午11:47
 * To change this template use File | Settings | File Templates.
 */
class Service
{

     public  $db;

     function service(){
         //初始化DB
         $db = null;

         $db = new DB();
         $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);

         $this -> db = $db;
     }

}
