<?php

namespace YiHaiTao\WangDianTong;

use Hanson\Foundation\Foundation;

/**
 * @property \YiHaiTao\WangDianTong\Basic\Basic $basic
 * @property \YiHaiTao\WangDianTong\Goods\Goods $goods
 * @property \YiHaiTao\WangDianTong\Purchase\Purchase $purchase
 * @property \YiHaiTao\WangDianTong\Refund\Refund $refund
 * @property \YiHaiTao\WangDianTong\Stock\Stock $stock
 * @property \YiHaiTao\WangDianTong\Trade\Trade $trade
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
