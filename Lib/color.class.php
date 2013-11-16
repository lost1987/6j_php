<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-4-16
 * Time: 下午4:13
 * To change this template use File | Settings | File Templates.
 */
class Color
{
          const green= 0x009966;
		  const blue = 0x0066CC;
		  const yellow = 0xFFFF33;
		  const orange = 0xFF6600;
		  const pink = 0xFF00CC;
		  const gold = 0xFFCC00;
		  const purple = 0x9900CC;
		  const black = 0x000000;
		  const white = 0xFFFFFF;
		  const red = 0xFF0000;
		  const darkgold = 0xCC6600;
		  const lightgreen = 0x33FF33;


        public static function getColor($color){
            $_color = 'green';
            switch($color){
                case 1: $_color = 'green';break;
                case 2: $_color = 'blue';break;
                case 3: $_color = 'purple';break;
                case 4: $_color = 'orange';break;
            }
            return $_color;
        }
}
