<?php

namespace YiHaiTao\WangDianTong;

use Exception;
use Hanson\Foundation\AbstractAPI;
use Psr\Http\Message\ResponseInterface;

/**
 * 旺店通旗舰版 OpenApi
 * 与企业版（Api）使用不同的签名算法和接口路径
 * - 企业版：使用 openapi2 路径，签名算法为 pack() 方法
 * - 旗舰版：使用 openapi 路径，签名算法为 secret + 排序键值对 + secret，然后 MD5
 */
class OpenApi extends AbstractAPI
{
    /**
     * 基准时间戳：2012-01-01 00:00:00
     */
    const BASE_TIMESTAMP = 1325347200;

    protected $appKey;

    protected $appSecret;

    protected $secret;

    protected $salt;

    protected $sid;

    protected $baseUrl;

    /**
     * OpenApi constructor.
     *
     * @param  string  $appKey  appkey
     * @param  string  $appSecret  appsecret，格式为 secret:salt，例如：testsecret:testsalt
     * @param  string  $sid  卖家账号
     * @param  string  $baseUrl 基础url
     */
    public function __construct($appKey, $appSecret, $sid, $baseUrl)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->sid = $sid;

        $this->baseUrl = rtrim($baseUrl, '/').'/openapi';

        // 解析 appsecret，格式为 secret:salt
        if (empty($appSecret)) {
            throw new Exception('appsecret cannot be empty, should be in format: secret:salt');
        }

        $parts = explode(':', $appSecret, 2);
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            throw new Exception('appsecret format error, should be secret:salt (e.g., "testsecret:testsalt"), got: '.(is_string($appSecret) ? substr($appSecret, 0, 20) : gettype($appSecret)));
        }
        $this->secret = $parts[0];
        $this->salt = $parts[1];
    }

    /**
     * 调用接口
     *
     * @param  string  $method  接口名称，例如：wms.stockout.Sales.weighingExt
     * @param  array  $params  参数数组，包含 body 参数和可选的分页参数
     *                         分页参数：page_size（分页大小）、page_no（分页编号）、calc_total（是否计算总数，1或0）
     *                         分页参数会自动添加到 URL 中，不会放入 body
     *                         示例：
     *                         - 非分页：call("method", ["arg1", "arg2", 1.2])
     *                         - 分页：call("method", ["arg1", "arg2", "page_size" => 20, "page_no" => 1, "calc_total" => 1])
     * @return array
     */
    public function call($method, $params = [])
    {
        $http = $this->getHttp();

        // 分离分页参数和 body 参数
        $pagerParams = [];
        $bodyParams = [];

        // 分页参数的键名
        $pagerKeys = ['page_size', 'page_no', 'calc_total'];

        // 检查是否有分页参数
        $hasPagerParams = false;
        foreach ($pagerKeys as $pagerKey) {
            if (isset($params[$pagerKey])) {
                $hasPagerParams = true;
                $pagerParams[$pagerKey] = $params[$pagerKey];
            }
        }

        // 构建 body 参数（排除分页参数）
        foreach ($params as $key => $value) {
            if (! in_array($key, $pagerKeys)) {
                if (is_int($key)) {
                    // 数字索引的参数，按顺序添加到 body 参数
                    $bodyParams[] = $value;
                } else {
                    // 字符串键的参数，添加到 body 参数
                    $bodyParams[$key] = $value;
                }
            }
        }

        // 构建请求参数
        $requestParams = $this->buildParams($method, $bodyParams, $pagerParams);

        // 计算签名（注意：sign 方法会修改 $requestParams 数组）
        $requestParams['sign'] = $this->sign($requestParams);

        // 构建URL（包含查询参数，但不包含body）
        $urlParams = $requestParams;
        $bodyJson = $urlParams['body'];
        unset($urlParams['body']); // body 不放在 URL 中
        $url = $this->baseUrl.'?'.http_build_query($urlParams);

        // json 选项会自动设置 Content-Type: application/json 并编码 JSON
        $bodyData = json_decode($bodyJson, true);
        $options = [
            'json' => $bodyData,
        ];

        /** @var ResponseInterface $response */
        $response = $http->request('POST', $url, $options);

        $responseBody = strval($response->getBody());

        // 如果响应不是 JSON，记录错误信息
        $result = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // 如果不是 JSON，返回原始响应
            return [
                'status' => -1,
                'message' => '响应解析失败: '.json_last_error_msg(),
                'raw_response' => $responseBody,
            ];
        }

        return $result;
    }

    /**
     * 构建请求参数
     *
     * @param  string  $method  接口名称
     * @param  array  $bodyParams  body 参数数组
     * @param  array  $pagerParams  分页参数
     * @return array
     */
    protected function buildParams($method, $bodyParams = [], $pagerParams = [])
    {
        if (empty($bodyParams)) {
            $body = json_encode([]);
        } else {
            // 如果 bodyParams 是关联数组，直接包装；如果是数字索引数组，也包装
            $body = json_encode([$bodyParams]);
        }

        $params = [
            'sid' => $this->sid,
            'key' => $this->appKey,
            'salt' => $this->salt,
            'method' => $method,
            'v' => '1.0',
            'timestamp' => $this->getTimestamp(),
            'body' => $body,
        ];

        // 如果有分页参数，添加到 params 中（与官方 SDK 保持一致）
        if (! empty($pagerParams)) {
            if (isset($pagerParams['page_size'])) {
                $params['page_size'] = $pagerParams['page_size'];
            }
            if (isset($pagerParams['page_no'])) {
                $params['page_no'] = $pagerParams['page_no'];
            }
            if (isset($pagerParams['calc_total'])) {
                // 与官方 SDK 一致：布尔值转换为 1 或 0
                $params['calc_total'] = $pagerParams['calc_total'] ? 1 : 0;
            }
        }

        return $params;
    }

    /**
     * 计算签名
     * 签名规则（与官方 SDK 完全一致）：
     * 1. 按照键名做正序排序（ksort）
     * 2. 构建数组：[secret, key1, val1, key2, val2, ..., secret]（跳过 sign 字段）
     * 3. implode('') 拼接成字符串
     * 4. 对字符串做 MD5
     *
     * @param  array  $params  请求参数（引用传递，会修改原数组）
     * @return string
     */
    protected function sign(array &$params)
    {
        // 按照键名做正序排序（与官方一致，先排序再处理 sign）
        ksort($params);

        // 构建数组，与官方 SDK 完全一致
        $arr = [];
        $arr[] = $this->secret;
        foreach ($params as $key => $val) {
            if ($key == 'sign') {
                continue; // 跳过 sign 字段
            }
            $arr[] = $key;
            $arr[] = $val;
        }
        $arr[] = $this->secret;

        // MD5 加密
        return md5(implode('', $arr));
    }

    /**
     * 获取时间戳（当前时间戳减去基准时间戳）
     *
     * @return int
     */
    protected function getTimestamp()
    {
        return time() - self::BASE_TIMESTAMP;
    }

    /**
     * 获取 appKey
     *
     * @return string
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * 获取 sid
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * 获取 baseUrl
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
