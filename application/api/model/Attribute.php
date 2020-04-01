<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/1 
 * @Time: 13:52
 * @Created by PhpStorm
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;

class Attribute extends Model
{
    protected $pk = 'attr_id';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}