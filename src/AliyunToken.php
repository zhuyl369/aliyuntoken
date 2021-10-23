<?php

namespace Zhuyl;
/*
 * 获取阿里云token.
 *
 * @author    zhuyl369 <zhuyl369@qq.com>
 * @copyright 2021 zhuyl369 <zhuyl369@qq.com>
 *
 * @link      https://github.com/zhuyl369/aliyuntoken
 * @link      http://www.blesslin.com
 */
use Exception;
class AliyunToken
{
    public  $baseUrl = 'http://nls-meta.cn-shanghai.aliyuncs.com/';         //接口地址
    private $accessKeyId;                                                   //阿里云账号AccessKey ID
    private $accessKeySecret;                                               //阿里云账号AccessKey Secret
    public  $Action = 'CreateToken';                                        //POP API名称：CreateToken
    public  $Version = '2019-02-28';                                        //POP API版本：2019-02-28
    public  $Format = 'JSON';                                               //响应返回的类型：JSON
    public  $RegionId = 'cn-shanghai';                                      //服务所在的地域ID：cn-shanghai
    private $Timestamp;                                                     //请求的时间戳。日期格式按照ISO 8601标准表示，且使用UTC时间，时区：+0。格式：YYYY-MM-DDThh:mm:ssZ。如2019-04-03T06:15:03Z为UTC时间2019年4月3日6点15分03秒。
    private $SignatureMethod = 'HMAC-SHA1';                                 //签名算法：HMAC-SHA1
    private $SignatureVersion = '1.0';                                      //签名算法版本：1.0
    private $SignatureNonce;                                                //唯一随机数uuid，用于请求的防重放攻击，每次请求唯一，不能重复使用。格式为A-B-C-D-E（A、B、C、D、E的字符位数分别为8、4、4、4、12）。例如，8d1e6a7a-f44e-40d5-aedb-fe4a1c80f434。
    private $Signature;                                                     //由所有请求参数计算出的签名结果

    public function __construct($accessKeyId, $accessKeySecret)
    {
        $this->accessKeyId = trim($accessKeyId);
        $this->accessKeySecret = trim($accessKeySecret);
        $this->Timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $this->SignatureNonce = $this->uuid();
    }

    public function getToken($method='get')
    {
        if(empty($method) || $method=='get'){
            $result = $this->curl_get_ssl($this->baseUrl, $this->createString('get'));
        }else{
            $result = $this->curl_post_ssl($this->baseUrl, $this->createString('post'));
        }
        if ($result) {
            $result = (array)json_decode($result, true);
            if(empty($result['ErrMsg']) && isset($result['Token'])){
                return $result['Token'];
            }else{
                throw new \Exception($result['Message']);
            }
        } else {
            return false;
        }
    }

    /*
     * 创建报文
     */
    private function createString($method)
    {
        $argument = array(
            'AccessKeyId' => $this->accessKeyId,
            'Action' => $this->Action,
            'Version' => $this->Version,
            'Format' => $this->Format,
            'RegionId' => $this->RegionId,
            'Timestamp' => $this->Timestamp,
            'SignatureMethod' => $this->SignatureMethod,
            'SignatureVersion' => $this->SignatureVersion,
            'SignatureNonce' => $this->SignatureNonce,
        );
        $this->Signature=$this->createStringToSign($method, $argument);
        $argument['Signature'] = $this->Signature;
        return $argument;
    }

    /*
     * 创建签名
     */
    private function createStringToSign($method, $argument)
    {
        $string = '';
        ksort($argument);
        foreach ($argument as $key => $value) {
            $string .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = strtoupper($method) . '&%2F&' . $this->percentencode(substr($string, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true));
        return $signature;
    }

    private function percentEncode($string)
    {
        $string = urlencode($string);
        $string = preg_replace('/\+/', '%20', $string);
        $string = preg_replace('/\*/', '%2A', $string);
        $string = preg_replace('/%7E/', '~', $string);
        return $string;
    }

    /**
     * 获取全球唯一标识uuid
     * @return string
     */
    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    //获取UTC格式的时间
    private function utc_time()
    {
        date_default_timezone_set('UTC');
        $timestamp = new \DateTime();
        $timeStr = $timestamp->format("Y-m-d\TH:i:s\Z");
        return $timeStr;
    }

    private function curl_get_ssl($url, $params,$second = 30, $aHeader = array())
    {
        $uri = $url . '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $uri);
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $second);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return $data;
        } else {
            $error = curl_errno($ch);
            return false;
        }
    }

    private function curl_post_ssl($url, $params, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return $data;
        } else {
            $error = curl_errno($ch);
            return false;
        }
    }
}