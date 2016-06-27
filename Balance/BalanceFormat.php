<?php

namespace PrivateDev\Utils\Balance;

class BalanceFormat
{
    /**
     * @param int $isoBalance
     *
     * @return float
     */
    static public function toNative(int $isoBalance) : float
    {
        return round($isoBalance / 100, 2);
    }

    /**
     * @param $balance
     *
     * @return int
     */
    static function toISO(float $balance) : int
    {
        return (int) ($balance * 100);
    }
}