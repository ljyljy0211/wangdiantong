<?php

namespace YiHaiTao\WangDianTong;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use YiHaiTao\WangDianTong\Basic\Basic;
use YiHaiTao\WangDianTong\Goods\Goods;
use YiHaiTao\WangDianTong\Purchase\Purchase;
use YiHaiTao\WangDianTong\Refund\Refund;
use YiHaiTao\WangDianTong\Stock\Stock;
use YiHaiTao\WangDianTong\Trade\Trade;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        // 基础接口
        $pimple['basic'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Basic($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };

        // 货品接口
        $pimple['goods'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Goods($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };

        // 采购接口
        $pimple['purchase'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Purchase($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };

        // 售后接口
        $pimple['refund'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Refund($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };

        // 库存接口
        $pimple['stock'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Stock($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };

        // 订单接口
        $pimple['trade'] = function ($pimple) {
            $config = $pimple->getConfig();
            return new Trade($config['appkey'], $config['appsecret'], $config['sid'], $config['baseUrl']);
        };
    }
}
