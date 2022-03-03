<?php


namespace Lostcontrols\PHPtools\BaiDuFanYi;


define("TIMEOUT", 10);


/**
 * Class Translation
 * Author Cyw
 * DateTime 2022/3/1 17:16
 * Api Doc Address : http://api.fanyi.baidu.com/api/trans/product/apidoc#joinFile
 * @package Lostcontrols\PHPtools\BaiDuFanYi
 */
class Translation
{
    /**
     * @var string 请求地址
     */
    private static $requestUrl = 'http://fanyi-api.baidu.com/api/trans/vip/translate';

    private static $appId = '20200201000379212';

    private static $secretKey = 'KOjOrdMcm3F3nZbk9XhY';

    /**
     * @param string $query 请求翻译的内容
     * @param string $from 翻译源语言
     * @param string $to 译文语言
     * @return mixed
     * @author Cyw
     * @dateTime 2022/3/3 15:54
     */
    public function translate(string $query, string $from, string $to)
    {
        $args = [
            'q' => $query,
            'appid' => self::$appId,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,
        ];
        $args['sign'] = $this->getSign($query, self::$appId, $args['salt'], self::$secretKey);
        $ret = $this->curlRequest(self::$requestUrl, $args);

        return json_decode($ret, true);
    }

    /**
     * 获取签名
     * @param string $query
     * @param string $appId
     * @param int $salt
     * @param string $secretKey
     * @return string
     * @author Cyw
     * @dateTime 2022/3/2 10:23
     */
    private function getSign(string $query, string $appId, int $salt, string $secretKey): string
    {
        $str = $appId . $query . $salt . $secretKey;

        return md5($str);
    }

    /**
     * 发起网络请求
     * @param string $url
     * @param array|null $args
     * @param string $method
     * @param int $testflag
     * @param int $timeout
     * @param array $headers
     * @return bool|mixed|string
     * @author Cyw
     * @dateTime 2022/3/3 15:48
     */
    private function callNetwork(string $url, array $args = null, string $method="post", int $testflag = 0, int $timeout = TIMEOUT, array $headers=array())
    {
        $ret = false;
        $i = 0;
        while($ret === false) {
            if($i > 1) break;
            if($i > 0) sleep(1);
            $ret = $this->curlRequest($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    /**
     * @param string $url
     * @param array $args
     * @param string $method
     * @param bool $withCookie
     * @param string|int $timeout
     * @param array $headers
     * @return bool|string
     * @author Cyw
     * @dateTime 2022/3/2 15:24
     */
    protected function curlRequest(string $url, array $args, string $method = 'post', bool $withCookie = false, string $timeout = TIMEOUT, array $headers = [])
    {
        $ch = curl_init();
        $data = $this->convert($args);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            if ($data) $url .= stripos($url, '?') > 0 ? "&$data" : "?$data";
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($withCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }

    /**
     * @param $args
     * @return mixed|string
     * @author Cyw
     * @dateTime 2022/3/2 10:36
     */
    protected function convert(&$args)
    {
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                } else {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }

        return $args;
    }
}