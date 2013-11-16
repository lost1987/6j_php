<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-10-8
 * Time: 下午4:26
 * To change this template use File | Settings | File Templates.
 * 六界之门API接口
 */

ini_set('display_errors','On');
define('API_KEY','bdijltvwxzBCDEFGIJLMOQTVWXZ12357');
define('BASEPATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
require BASEPATH.'Conf/db.inc.php';
require BASEPATH.'Common/common.php';
require BASEPATH.'DB/mysql.class.php';
require BASEPATH.'DB/engine.class.php';
require BASEPATH.'Lib/input.class.php';
require BASEPATH.'Lib/security.class.php';
require BASEPATH.'Lib/utf8.class.php';
require BASEPATH.'Api/baseapi.class.php';

Engine::createDBClass();

$actions = require BASEPATH.'Api/api.inc.php';
$actiontype = $_GET['at'];
if(!empty($actions[$actiontype])){
          list($clazz,$method) = explode('|',$actions[$actiontype]);
          require BASEPATH.'Api/'.$clazz.'.class.php';
          $clazz = ucfirst($clazz);
          $cls = new $clazz;
          echo call_user_func(array($cls,$method));
}

exit;

