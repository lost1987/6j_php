<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-9-4
 * Time: 下午1:37
 * To change this template use File | Settings | File Templates.
 * 与AS3通用的DES加密解密
 */
class DesSecurity
{
      private $_key = null;//长度必须是8位
      private $_iv = null;//长度必须是8位

      private function DesSecurity(){}

      public static function instance(){
            static $_instance;
            if(empty($_instance)){
                $_instance = new DesSecurity();
                $_instance -> _key = 'aaabbbcc';
                $_instance -> _iv = 'aaabbbcc';
            }
            return $_instance;
     }

    /**
     * 加密
     * @param $str
     */
    public function encrypt($str){
        $tb=mcrypt_module_open(MCRYPT_3DES,'','cbc',''); //创建加密环境 256位 128/8 = 16 字节 表示IV的长度

        mcrypt_generic_init($tb,$this->_key,$this->_iv); //初始化加密算法
        $str=$this->PaddingPKCS7($str);//这个函数非常关键,其作用是对明文进行补位填充
        $cipher=mcrypt_generic($tb,$str); //对数据执行加密
        $cipher=base64_encode($cipher);//同意进行base64编码
        mcrypt_generic_deinit($tb); //释放加密算法资源
        mcrypt_module_close($tb); //关闭加密环境

        return $cipher;
     }

    /**
     * 解密
     * @param $str
     */
    public function decrypt($str){
        $tb=mcrypt_module_open(MCRYPT_3DES,'','cbc','');
        mcrypt_generic_init($tb,$this->_key,$this->_iv);
        $str=base64_decode($str);
        $source=mdecrypt_generic($tb,$str);
        mcrypt_generic_deinit($tb);
        mcrypt_module_close($tb);
        return $source;
    }

    //补位填充函数
    private function PaddingPKCS7 ($data)
    {
        /* 获取加密算法的区块所需空间,MCRYPT_3DES表示加密算法,cbc表示加密模式,要和mcrypt_module_open(MCRYPT_3DES,'','cbc','')的一致*/
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, 'cbc');
        echo ' block_size = '.$block_size.' = ';
        $padding_char = $block_size - (strlen($data) % $block_size); // 计算需要补位的空间
        $data .= str_repeat(chr($padding_char), $padding_char);        // 补位操作
        return $data;
    }
}
