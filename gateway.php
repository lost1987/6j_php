<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp
 */

/**
 * entrance define
 */
define('BASEPATH',dirname(__FILE__));

ini_set('memcache.chunk_size',1024*1024*2);//2M

/**
*  includes
*  */
require BASEPATH . '/ClassLoader.php';
require BASEPATH . '/Conf/db.inc.php';
require BASEPATH . '/Conf/config.inc.php';
require BASEPATH . '/Lib/autoload.class.php';
require BASEPATH . '/Common/common.php';

if(ENVIRMENT == 'develop'){
    ini_set('display_errors','On');
    ini_set('log_errors','On');
}else{
    ini_set('display_errors','Off');
    ini_set('log_errors','Off');
}

spl_autoload_register(array('Autoload','_autoload'));

Engine::createDBClass();
/**
 * config
 */
$config = new Amfphp_Core_Config();
$config -> serviceFolderPaths = $service_paths;
//$config -> pluginsConfig['AmfphpCustomClassConverter'] = array(BASEPATH.'/Services/vo');
/* 
 * main entry point (gateway) for service calls. instanciates the gateway class and uses it to handle the call.
 * 
 * @package Amfphp
 * @author Ariel Sommeria-klein
 */
$gateway = Amfphp_Core_HttpRequestGatewayFactory::createGateway($config);

//use this to change the current folder to the services folder. Be careful of the case.
//This was done in 1.9 and can be used to support relative includes, and should be used when upgrading from 1.9 to 2.0 if you use relative includes
//chdir(dirname(__FILE__) . '/Services');

$gateway->service();
$gateway->output();


?>
