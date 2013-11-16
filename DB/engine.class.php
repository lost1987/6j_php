<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-7-10
 * Time: 上午9:47
 * To change this template use File | Settings | File Templates.
 */
class Engine
{
    public static function createDBClass(){
        switch(DB_TYPE){
            case 'Mysql': eval("class DB extends Mysql{}");
                        break;

            case 'Mssql': eval("class DB extends Mssql{}");
                        break;
        }
    }
}
