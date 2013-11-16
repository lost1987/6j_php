<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-10
 * Time: 下午2:02
 * To change this template use File | Settings | File Templates.
 */
abstract class Autoload
{
      public static function _autoload($className){
          $filename = strtolower($className);

          $path = BASEPATH.'/DB/'.$filename.'.class.php';
          if(file_exists($path)){
              require_once $path;
              return;
          }

          $path = BASEPATH.'/Common/'.$filename.'.class.php';
          if(file_exists($path)){
              require_once $path;
              return;
          }

          $path = BASEPATH.'/Lib/'.$filename.'.class.php';
          if(file_exists($path)){
              require_once $path;
              return;
          }

      }
}
