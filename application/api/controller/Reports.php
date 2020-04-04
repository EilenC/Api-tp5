<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/4 
 * @Time: 15:29
 * @Created by PhpStorm
 */

namespace app\api\controller;


class Reports extends Common
{
    public function index($id)
    {
        //硬编码数据统计数据
        $result = '{"legend":{"data":["华东","华南","华北","西部","其他"]},"yAxis":[{"type":"value"}],"xAxis":[{"data":["2017-12-27","2017-12-28","2017-12-29","2017-12-30","2017-12-31","2018-1-1"]}],"series":[{"name":"华东","type":"line","stack":"总量","areaStyle":{"normal":{}},"data":[2999,3111,4100,3565,3528,6000]},{"name":"华南","type":"line","stack":"总量","areaStyle":{"normal":{}},"data":[5090,2500,3400,6000,6400,7800]},{"name":"华北","type":"line","stack":"总量","areaStyle":{"normal":{}},"data":[6888,4000,8010,12321,13928,12984]},{"name":"西部","type":"line","stack":"总量","areaStyle":{"normal":{}},"data":[9991,4130,7777,12903,13098,14028]},{"name":"其他","type":"line","stack":"总量","areaStyle":{"normal":{}},"data":[15212,5800,10241,14821,15982,14091]}]}';
        $result = json_decode($result);
        self::return_msg(200, '获取报表成功!', $result);
    }
}