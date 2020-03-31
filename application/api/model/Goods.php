<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/30 
 * @Time: 18:26
 * @Created by PhpStorm
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;


class Goods extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $pk = 'goods_id';

}