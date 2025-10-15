<?php

namespace app\helpers;

class PostFormatter
{
    public static function maskIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $arParts = explode('.', $ip);
            $arParts[2] = '**';
            $arParts[3] = '**';
            return implode('.', $arParts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $arParts = explode(':', $ip);
            $arParts[6] = '****';
            $arParts[7] = '****';
            return implode('.', $arParts);
        }
        return $ip;
    }
}