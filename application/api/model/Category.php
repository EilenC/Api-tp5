<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/31 
 * @Time: 11:19
 * @Created by PhpStorm
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;

class Category extends Model
{
    protected $pk = 'cat_id';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    public function getCatDeletedAttr($value){
        $deleted = [
            0 => false,
            1 => true
        ];

        return $deleted[$value];
    }
}