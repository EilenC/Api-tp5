<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/25 
 * @Time: 7:10
 * @Created by PhpStorm
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;


class Manager extends Model
{
    use SoftDelete;
    protected $pk = 'mg_id';
    protected $deleteTime = 'delete_time';
}