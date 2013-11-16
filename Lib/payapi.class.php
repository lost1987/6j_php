<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-5-10
 * Time: 下午1:58
 * To change this template use File | Settings | File Templates.
 */
class Payapi
{

    private  $baseUrl;
    private  $host; //支付的主机地址
    private  $uname; //账号
    private  $ukey;
    private  $utime; //时间戳
    private  $aid;  //运营商ID
    private  $sid;  //服务器索引server_index
    private  $goldmoney; //元宝数
    private  $eventid;  //订单号

    function Payapi($host,$uname,$ukey,$utime,$aid,$sid,$goldmoney,$eventid){
        $this -> host = $host;
        $this -> uname = $uname;
        $this -> ukey = $ukey;
        $this -> utime = $utime;
        $this -> aid = $aid;
        $this -> sid = $sid;
        $this -> goldmoney = $goldmoney;
        $this -> eventid = $eventid;

        $this->baseUrl =  $this->host . 'api_pay.php?';
    }


    public function pay(){
        $params = http_build_query(
            array(
                'uname' => $this->uname,
                'ukey' => $this -> ukey,
                'utime' => $this -> utime,
                'aid' => $this->aid,
                'sid' => $this -> sid,
                'goldmoney' => $this -> goldmoney,
                'eventid' => $this -> eventid
            )
        );

        $url = $this->baseUrl.$params;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
