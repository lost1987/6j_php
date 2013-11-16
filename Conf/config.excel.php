<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lost
 * Date: 13-6-26
 * Time: 上午11:30
 * To change this template use File | Settings | File Templates.
 */

$GLOBALS['excel_keys'] = array(
     /***
      * 0表示service继承Service(传入$condition对象[只需要传入按逗号分割的server_id],$page对象),
      * 1表示service继承ServerDBChooser(传入$condition对象[需传入server对象数组],$page对象),
      * 2表示service继承ServerDBChooser(只需传入$condition对象)
      * 3表示service继承Service(只需传入$condition对象)
      * 4表示service继承ServerDBChooser($page对象,传入$condition对象[需传入server对象数组]),
      */
    /***综合数据****/
    'synthesis_list' => 'complexDataService|lists|0',
    'singleArpuData_list' => 'singleArpuService|lists|1',
    'rechargeOrderData_list'=> 'rechargeOrderService|lists|2',
    'userTurnOverData_list' => 'userTurnOverDataService|lists|0',
    'levelData_list' => 'levelDataService|lists|3',
    'turnoverData_list' => 'turnoverDataService|lists|0',
    'userStayData_list' => 'userStayDataService|lists|0',
    'dailyTaskData_list' => 'dailyTaskDataService|lists|0',
    'dailyCopyData_list' => 'dailyCopyDataService|lists|0',
    'dailyActivityData_list' => 'dailyActivityDataService|lists|0',
    'userAnalysisData_list' => 'userAnalysisDataService|lists|2',

    /*****日志*******/
    'yuanbao_list' => 'yuanbaoService|lists|4',
    'pay_list' => 'payService|lists|4',
);

$GLOBALS['autoload_folders'] = array(
    'Excel',
    'DB',
    'Lib',
    'Services',
    'Services/adminfunc',
    'Services/log',
    'Services/managefunc',
    'Services/player',
    'Services/sys',
);