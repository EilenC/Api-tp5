<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/25 
 * @Time: 5:47
 * @Created by PhpStorm
 */
return [
    //JWT 加密key
    'jwt_key' => 'Eilen',

    // JWT token有效期限
    'jwt_exp' => 216000,
    // JWT 签名人
    'jwt_iss' => 'http://eilen.top',
    'pwd_key_header' => '%%',
    'pwd_key_bottom' => '##',
    //上传图片临时存放目录,默认在public下
    'tmp_save_path' => 'tmp_uploads',
    //确定保存图片存放目录,默认在public下
    'save_path' => 'uploads',
    //水印图片,默认在public下
    'logo' => [
        "16" => 'logo_16x16.jpg',
        "32" => 'logo_32x32.jpg',
        "64" => 'logo_64x64.jpg'
    ],
    //水印文字
    'text' => 'Eilen'
];