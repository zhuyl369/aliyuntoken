<?php
namespace Zhuyl;
/*
 * 文件缓存类.
 *
 * @author    zhuyl369 <zhuyl369@qq.com>
 * @copyright 2021 zhuyl369 <zhuyl369@qq.com>
 *
 * @link      https://github.com/zhuyl369/aliyuntoken
 * @link      http://www.blesslin.com
 */
defined("DS") or define("DS",DIRECTORY_SEPARATOR);
class Cache
{
    private $cache_path;
    private $cache_expire;

    public function __construct($exp_time = 3600, $path = __DIR__.DS."runtime".DS."cache".DS)
    {
        $this->cache_expire = $exp_time;
        $this->cache_path = $path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true) ?: die('创建缓存目录失败');
        }
    }

    private function fileName($key)
    {
        return $this->cache_path . md5($key);
    }

    public function put($key, $data)
    {
        $values = serialize($data);
        $filename = $this->fileName($key);
        $file = fopen($filename, 'w');
        if ($file) {
            fwrite($file, $values);
            fclose($file);
        } else return false;
    }

    public function get($key)
    {
        $filename = $this->fileName($key);
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        if (time() < (filemtime($filename) + $this->cache_expire)) {
            $file = fopen($filename, "r");
            if ($file) {
                $data = fread($file, filesize($filename));
                fclose($file);
                return unserialize($data);
            } else return false;
        } else{
            @unlink($filename);
            return false;
        }
    }
    public function del($key){
        $filename = $this->fileName($key);
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }else{
            @unlink($filename);
            return true;
        }
    }
}
