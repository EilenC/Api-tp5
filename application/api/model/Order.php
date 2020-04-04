<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/3 
 * @Time: 22:19
 * @Created by PhpStorm
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{
    protected $pk = 'order_id';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}