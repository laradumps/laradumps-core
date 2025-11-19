<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;

class ConvertArrayToPhpSyntax
{
    public static function convert(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();

            return self::convertArrayToPhpSyntax($value);
        }

        if (
            is_null($value)
            || is_string($value)
            || (is_object($value) && !$value instanceof DateTimeInterface)
        ) {
            return $value;
        }

        if (is_array($value)) {
            return self::convertArrayToPhpSyntax($value);
        }

        return $value;
    }

    private static function convertArrayToPhpSyntax(array $var, int $indentLevel = 0): string
    {
        $indent      = str_repeat('    ', $indentLevel);
        $innerIndent = str_repeat('    ', $indentLevel + 1);

        $result = "[\n";

        foreach ($var as $key => $value) {
            $result .= $innerIndent;
            $result .= is_int($key) ? $key : var_export($key, true);
            $result .= ' => ';

            if (is_object($value) && method_exists($value, 'toArray')) {
                if ($value instanceof CarbonInterface) {
                    $utcCarbon = $value->copy()->setTimezone(new DateTimeZone('UTC'));
                    $result .= var_export($utcCarbon->toIso8601String(), true);
                    $result .= ",\n";

                    continue;
                }

                if ($value instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) { // @phpstan-ignore-line
                    $value = $value->toArray(request()); // @phpstan-ignore-line
                } else {
                    $value = $value->toArray();
                }
            }

            if (is_array($value)) {
                $result .= self::convertArrayToPhpSyntax($value, $indentLevel + 1);
            } elseif ($value instanceof DateTimeInterface) {
                $immutable = \DateTimeImmutable::createFromInterface($value)
                    ->setTimezone(new DateTimeZone('UTC'));

                $result .= var_export($immutable->format(DateTimeInterface::ATOM), true);
            } elseif (is_resource($value)) {
                $result .= '(resource)';
            } elseif (is_string($value)) {
                $result .= var_export($value, true);
            } elseif (is_object($value)) {
                $result .= var_export($value, true);
            } elseif (is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $result .= 'null';
            } else {
                $result .= $value;
            }

            $result .= ",\n";
        }

        $result .= $indent . ']';

        return $result;
    }
}
