<?php

namespace quarkblockchain;

/**
 * Class BcMath
 */
class BcMath
{
    /**
     * @param string $hex
     * @return string
     */
    public static function HexDec(string $hex): string
    {
        $dec = 0;
        $hex = str_replace('0x','',$hex);
        $len = strlen($hex);
        for ($i = 3; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }

    /**
     * @param $dec
     * @return string
     */
    public static function DecHex($dec): string
    {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last), 16);

        if ($remain == 0) {
            return dechex($last);
        } else {
            return self::DecHex($remain) . dechex($last);
        }
    }
}