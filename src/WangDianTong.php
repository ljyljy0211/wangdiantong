<?php

namespace YiHaiTao\WangDianTong;

use Hanson\Foundation\Foundation;

/**
 * 旺店通 SDK
 *
 * 企业版接口（使用 openapi2 路径）：
 *
 * @property \YiHaiTao\WangDianTong\Basic\Basic $basic
 * @property \YiHaiTao\WangDianTong\Goods\Goods $goods
 * @property \YiHaiTao\WangDianTong\Purchase\Purchase $purchase
 * @property \YiHaiTao\WangDianTong\Refund\Refund $refund
 * @property \YiHaiTao\WangDianTong\Stock\Stock $stock
 * @property \YiHaiTao\WangDianTong\Trade\Trade $trade
 *
 * 旗舰版接口（使用 openapi 路径，新的签名算法）：
 * @property \YiHaiTao\WangDianTong\OpenApi $qijianApi
 *
 * Class WangDianTong
 */
class WangDianTong extends Foundation
{
    protected $providers = [
        ServiceProvider::class,
    ];

    public function __construct($config)
    {
        $config['debug'] = $config['debug'] ?? false;
        parent::__construct($config);
    }
}
