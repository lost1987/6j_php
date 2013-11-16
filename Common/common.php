<?php

function make_rand_str( $length = 18 )
{
    // 密码字符集，可任意添加你需要的字符
    $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    // 在 $chars 中随机取 $length 个数组元素键名
    $keys = array_rand($chars, $length);
    $password = '';
    for($i = 0; $i < $length; $i++)
    {
        // 将 $length 个数组元素连接成字符串
        $password .= $chars[$keys[$i]];
    }
    return $password;
}

//对象数组 通过其中的一个属性名来取出拥有属性的对象
/***
 * @param $key 对象数组中的其中一个对象的属性名
 * @param $value  属性值
 * @param $object_array 要遍历的对象数组
 * @return object|null
 */
function fetch_object_by_key($key,$value,$object_array){
        $return = null;
        foreach($object_array as $object){
            if($object -> $key == $value){
                $return = $object;
                break;
            }
        }
        return $return;
}

function isNan($param){
     if(empty($param) || $param == 'N/A')
     return TRUE;
     return FALSE;
}

/**
 * @param $start  开始时间  毫秒数
 * @param $end     结束时间  毫秒数
 * @param $tick    时间间隔  毫秒数
 * @param $format
 * @param $desc   TRUE:按时间倒序排列 FALSE : 正常排序
 * @return array   返回包含开始时间和结束时间的间隔时间数组的时间点
 */
function timeTickArrayPoint($start,$end,$tick,$format=null,$desc=FALSE){
    if(gettype($start) == 'string')
        $start = strtotime($start);
    if(gettype($end) == 'string')
        $end = strtotime($end);

    $timepoint = array();
    if(!$desc){
        while($start < $end){
            if(empty($format))
                $timepoint[] = $start;
            else{
                $format_time = date($format,$start);
                $timepoint[] = $format_time;
            }
            $start += $tick;
        }
        $timepoint[] = $end;
    }else{
        while($start < $end){
            if(empty($format))
                $timepoint[] = $end;
            else{
                $format_time = date($format,$end);
                $timepoint[] = $format_time;
            }
            $end -= $tick;
        }
        $timepoint[] = $start;
    }
    return $timepoint;
}

/**
 * 读取CSV
 * @param $filename
 * @return array
 */
function getCSVdata($filename)
{
    $row = 1;//第一行开始
    if(($handle = fopen($filename, "r")) !== false)
    {
        while(($dataSrc = fgetcsv($handle)) !== false)
        {
            $num = count($dataSrc);
            for ($c=0; $c < $num; $c++)//列 column
            {
                if($row === 1)//第一行作为字段
                {
                    $dataName[] = $dataSrc[$c];//字段名称
                }
                else
                {
                    foreach ($dataName as $k=>$v)
                    {
                        if($k == $c)//对应的字段
                        {
                            $data[$v] = $dataSrc[$c];
                        }
                    }
                }
            }
            if(!empty($data))
            {
                $dataRtn[] = $data;
                unset($data);
            }
            $row++;
        }
        fclose($handle);
        return $dataRtn;
    }
}

?>