<?php

namespace YiHaiTao\WangDianTong;

class Helper
{
    /**
     * 将给定的字符串以给定的值结尾返回（如果它尚未以给定值结尾）.
     *
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * 将给定值添加到给定字符串的开始位置（如果字符串尚未以给定值开始）.
     *
     * @return string
     */
    public static function start($value, $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
    }
}
