# PHP简单获取阿里云接口token


## 概述

在做一个语音合成项目(使用阿里云tts语音合成接口)时发现阿里云官方提供的SDK包太大，而自己的项目也就几百kb的大小，于是自己动手封装了一个简单的获取阿里云接口时token类。

## 运行环境
- PHP 5.6+
- cURL extension


## 安装方法
1. 通过composer安装依赖，可以在你的项目根目录运行：

        $ composer require zhuyl/aliyuntoken

   或者在你的`composer.json`中声明依赖：

        "require": {
            "zhuyl/aliyuntoken": "~1.0"
        }

   然后通过`composer install`安装依赖。composer安装完成后，在您的PHP代码中引入依赖即可：

        require_once __DIR__ . '/vendor/autoload.php';

2. 使用 git clone 或者直接下载源码，在您的代码中引入src目录下的`AliyunToken.php`文件：

        require_once '/path/src/AliyunToken.php';

## 快速使用

### 常用类

| 类名 | 解释 |
|:------------------|:------------------------------------|
|zhuyl\AliyunToken | 通过AliyunToken的实例调用接口 |
|zhuyl\Cache | 一个简单的文件缓存类|

### 使用方法

```php
<?php
require_once (__DIR__.DIRECTORY_SEPARATOR.'AliyunToken.php');
require_once (__DIR__.DIRECTORY_SEPARATOR.'Cache.php');

$AccessKey ='<您从阿里云获得的AccessKeyId>';
$AccessKeySecret ='<您从阿里云获得的AccessKeySecret>';
try{
    $obj=new \Zhuyl\AliyunToken($AccessKey,$AccessKeySecret);
    $token=$obj->getToken();        //接口获取最新token
    $cache=new \Zhuyl\Cache();      //实例化缓存类（如果不使用缓存也去除以下3行代码）
    $cache->put('token',$token);    //写入缓存
    $token=$cache->get('token');    //获取缓存信息
    print_r($token);
    print_r($token['Id']);
}catch (Exception $e){
    die($e->getMessage());
}
```

### 返回结果
```php
array (
  'UserId' => 'xxxxxxxxxxxxxxxxxx',                 //阿里云账号ID
  'Id' => 'cc0c8b52a86440e5ae70ac75da1d4d35',       //请求后分配的Token值
  'ExpireTime' => 1635094339,                       //Token的有效期时间戳（单位：秒。例如1553825814换算为北京时间为：2019/3/29 10:16:54，即Token在该时间之前有效。）
)
```

### 缓存使用
```php
/*
 * 实例化缓存
 * @var $exp_time   int     缓存过期时间（默认3600秒）
 * @var $path       string  存储缓存文件的路径
 */
$cache=new \Zhuyl\Cache($exp_time = 3600, $path = __DIR__.DS."runtime".DS."cache".DS);
$cache->put('token',$token);    //写入缓存
$token=$cache->get('token');    //获取缓存信息,成功返回数据,失败返回false
$token=$cache->del('token');    //删除缓存信息,成功返回true,失败返回false
```
