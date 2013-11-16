<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-6-26
 * Time: 上午11:47
 * To change this template use File | Settings | File Templates.
 */
class ExcelMaker extends Input
{

    private static $instance;
    private $params;
    private $results;
    private $cellNamesChar = array(
        'A','B','C','D','E','F','G','H','I','J',
        'K','L','M','N','O','P','Q','R','S','T',
        'U','V','W','X','Y','Z'
    );

    private $table_servers;
    private $dbname;

    private function ExcelMaker(){
        parent::__construct();
        $this -> table_servers =  DB_PREFIX.'servers';
        $this -> dbname = 'mmo2d_admin';//管理服务器等信息的后台数据库名
    }


    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function register_auto_load(){
        spl_autoload_register(array(__CLASS__,'_autoload'));
    }

    public static function _autoload($className){
        $folders = $GLOBALS['autoload_folders'];
        foreach($folders as $foldername){
            if(strpos($foldername,'Services') > -1){
                $classname = lcfirst($className);
                $classfile = $classname.'.php';
            }
            else{
                $classname = strtolower($className);
                $classfile = $classname.'.class.php';
            }

            $path = BASEPATH.$foldername.DIRECTORY_SEPARATOR.$classfile;
            if(file_exists($path)){
                require_once $path;
                break;
            }
        }
    }

    private function fetchData(){
        $this->params = array();
        $excel_module = $GLOBALS['excel_keys'][$this->post('module_key')];
        list($this->params['service'],$this->params['method'],$this->params['type']) = explode('|',$excel_module);
        if(empty($this->params['service']) || empty($this->params['method']))throw new Exception('服务和方法不能为空');
        $this->params['start_time'] = $this->post('start_time');
        $this->params['end_time'] = $this->post('end_time');
        $this->params['server_id'] = $this->post('server_id');
        $this->params['columnNames'] = explode('|',$this->post('columnNames'));
        $this->params['columnKeys'] = explode('|',$this->post('columnKeys'));
        $this->params['excel_name'] = $this->post('excel_name');
        return $this;
    }

    private function callMethod(){
        $condition = new stdClass();
        $condition -> starttime = $this->params['start_time'];
        $condition -> endtime = $this->params['end_time'];
        $condition -> server_ids = $this->params['server_id'];

        $page = new stdClass();
        $page -> start = 0;
        $page -> limit = 3000;

        $service = new $this->params['service']();

        switch($this->params['type']){
            case 0:
                $this -> results = call_user_func(array($service,$this->params['method']),$page,$condition);
                break;
            case 1:
                //查询服务器 组成数组
                $db =  new DB();
                $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
                $db -> select_db($this->dbname);
                $servers = $db -> query("select * from $this->table_servers where id in ($condition->server_ids)")->result_objects();
                $condition->servers = $servers;
                $this -> results = call_user_func(array($service,$this->params['method']),$page,$condition);
                break;
            case 2:
                $db =  new DB();
                $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
                $db -> select_db($this->dbname);
                $servers = $db -> query("select * from $this->table_servers where id in ($condition->server_ids)")->result_objects();
                $condition->servers = $servers;
                $this -> results = call_user_func(array($service,$this->params['method']),$condition);
                break;
            case 3:
                $this -> results = call_user_func(array($service,$this->params['method']),$condition);
                break;
            case 4:
                $db = new DB();
                $db -> connect(DB_HOST.':'.DB_PORT,DB_USER,DB_PWD);
                $db -> select_db($this->dbname);
                $server = $db -> query("select id,dynamic_dbname,name from $this->table_servers where id = $condition->server_ids")->result_object();
                $condition -> server = $server;
                $condition -> account_name = $this->post('account_name');
                $condition -> level_start = $this->post('level_start');
                $condition -> level_limit = $this->post('level_limit');
                $condition -> vip_start = $this->post('vip_start');
                $condition -> vip_limit = $this->post('vip_limit');
                $condition -> type = $this->post('type');
                $condition -> child_type = $this->post('child_type');

                $this->results = call_user_func(array($service,$this->params['method']),$page,$condition);
                break;
        }
    }

    private function onReadyExcel(){

        $excel = new PHPExcel();
        $excel -> getProperties() -> setCreator("yilong");
        $excel->getProperties()->setLastModifiedBy("yilong");
        $excel->getProperties()->setTitle("Office 2007 XLS Test Document");
        $excel->getProperties()->setSubject("Office 2007 XLS Test Document");
        $excel->getProperties()->setDescription("Test document for Office 2007 XLS, generated using PHP classes.");
        $excel->getProperties()->setKeywords("office 2007 openxml php");
        $excel->getProperties()->setCategory($this->params['excel_name']);

        $excel->setActiveSheetIndex(0);
        $activeSheet  =  $excel->getActiveSheet();
        $activeSheet->setTitle($this->params['excel_name']);

        $columns_names = $this->params['columnNames'];
        $columns_keys = $this->params['columnKeys'];
        $columns_num = count($columns_names);

        //设置列名
        for($i=0;$i<$columns_num;$i++){
            $activeSheet -> setCellValue($this->cellNamesChar[$i].'1',$columns_names[$i]);
            $activeSheet -> getStyle($this->cellNamesChar[$i].'1') -> getFill() -> setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $activeSheet -> getStyle($this->cellNamesChar[$i].'1') -> getFill() -> getStartColor() -> setARGB(PHPExcel_Style_Color::COLOR_GREEN);
            $activeSheet -> getColumnDimension($this->cellNamesChar[$i]) -> setWidth(30);
            $activeSheet -> getStyle($this->cellNamesChar[$i].'1') -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //设置值
        $rows = count($this->results);
        if($rows > 0){
            for($i=0;$i<$rows;$i++){
                $index = $i+2;
                $object = $this->results[$i];

                for($k=0;$k<$columns_num;$k++){
                    if(!isset($object->{$columns_keys[$k]})) //如果没有属性的话 跳过 进行下个属性的计算
                    {
                        break 1;
                    }
                    $activeSheet -> setCellValue($this->cellNamesChar[$k].$index,$object->{$columns_keys[$k]});
                    //设置文字水平居中
                    $activeSheet -> getStyle($this->cellNamesChar[$k].$index) ->getAlignment() ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

            }
        }

        // 设置页方向和规模
        $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $excel->setActiveSheetIndex(0);
        $date = date('Y-m-d H:i:s');

        /*2007*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$this->params['excel_name'].'_'.$date.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        /**office 2003
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$this->params['excel_name'].'_'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
         **/
        exit;
    }

    public function output(){
        $this->fetchData();
        $this->callMethod();
        $this->onReadyExcel();
    }

}
