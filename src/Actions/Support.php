<?php

namespace LaraDumps\LaraDumpsCore\Actions;

class Support
{
    // extracted from Illuminate\Support\Str
    public static function isJson(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    public static function cut(string $str, string $start, string $end): string
    {
        /** @phpstan-ignore-next-line */
        $arr = explode($start, $str);

        if (isset($arr[1])) {
            /** @phpstan-ignore-next-line */
            $arr = explode($end, $arr[1]);

            return '<pre ' . $arr[0] . '</pre>';
        }

        return '';
    }

    // extracted from Illuminate\Support\Str
    public static function between(string $subject, string $from, string $to): string
    {
        return static::beforeLast(static::after($subject, $from), $to);
    }

    // extracted from Illuminate\Support\Str=
    public static function beforeLast(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return mb_substr($subject, 0, $pos, 'UTF-8');
    }

    // extracted from Illuminate\Support\Str
    public static function after(string $subject, string $search): string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }
}
