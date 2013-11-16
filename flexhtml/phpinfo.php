<?
define('INFO_PWD','bdijltvwxzBCDEFGIJLMOQTVWXZ12357');

$clinetKey = $_GET['key'];
list($key,$time) = explode('|',$clinetKey);
$time = substr($time,0,10);
$myKey = md5($time.INFO_PWD);
if($myKey != $key){
    exit('no access!');
}

require '../Conf/db.inc.php';
$link = mysql_connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);

$system = array(
    '系统' => php_uname(),
    'PHP运行' => php_sapi_name(),
    '客户端IP' => $_SERVER['REMOTE_ADDR'],
    'mysql版本' => mysql_get_server_info($link)
);

//如果PHP没有禁用exec函数
if(function_exists('exec')){
    if(strpos(php_uname(),'mac') > -1){
        $systype = 'mac';
    }else{
        $systype = 'linux';
    }

    switch($systype){
        case 'mac':   $cpu_name = exec('sysctl -n machdep.cpu.brand_string');
            $mem_usage = exec('echo $(top -l 1 | awk /PhysMem/;)');
            break;
        case 'linux':  $cpu_name = exec('cat /proc/cpuinfo | grep name | cut -f2 -d: | uniq -c ');
            $mem_usage = exec('cat /proc/meminfo | grep Mem');
            break;
    }

    $system['CPU'] = $cpu_name;
    $system['内存使用'] = $mem_usage;
    $system['开机时间'] = exec('uptime');
    $system['磁盘状态'] = exec('df -lh');
}



$table ='<center><table class="systemTab">';

foreach($system as $k=>$v){
    $table .= "<tr><td class='title'>$k</td><td class='val'>$v</td></tr>";
}

$table.='</table></center><br/><br/>';
echo $table;
phpinfo();
?>

<style>
    .systemTab{
        line-height:40px;
        font-size:14px;
        width: 600px;
    }

    td.title{
        font-weight: bold;
        width: 100px;
        background:RGB(204,204,255);
    }
    td.val{
        background: #d3d3d3;
    }
</style>