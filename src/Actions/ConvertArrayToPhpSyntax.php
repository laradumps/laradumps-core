<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use WeakMap;

class ConvertArrayToPhpSyntax
{
    private const MAX_DEPTH = 10;

    private const MAX_ITEMS_PER_LEVEL = 100;

    /**
     * @var WeakMap<object, true>
     */
    private static WeakMap $visitedObjects;

    public static function convert(mixed $value): mixed
    {
        self::$visitedObjects = new WeakMap();

        return self::convertValue($value);
    }

    private static function convertValue(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            if (isset(self::$visitedObjects[$value])) {
                return '(circular reference)';
            }

            self::$visitedObjects[$value] = true;

            try {
                $value = $value->toArray();

                return self::convertArrayToPhpSyntax($value);
            } catch (\Throwable $e) {
                return '(conversion error)';
            }
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

    private static function convertArrayToPhpSyntax(array $var, int $indentLevel = 0, int $depth = 0): string
    {
        if ($depth > self::MAX_DEPTH) {
            return "'(max depth exceeded)'";
        }

        $indent      = str_repeat('    ', $indentLevel);
        $innerIndent = str_repeat('    ', $indentLevel + 1);

        $result = "[\n";

        $itemCount  = 0;
        $totalItems = count($var);

        foreach ($var as $key => $value) {
            if ($itemCount >= self::MAX_ITEMS_PER_LEVEL) {
                $remaining = $totalItems - self::MAX_ITEMS_PER_LEVEL;
                $result .= $innerIndent . "// ... and {$remaining} more items\n";

                break;
            }
            $itemCount++;

            $result .= $innerIndent;
            $result .= is_int($key) ? $key : self::safeVarExport($key);
            $result .= ' => ';

            if (is_object($value) && method_exists($value, 'toArray')) {
                if (isset(self::$visitedObjects[$value])) {
                    $result .= self::safeVarExport('(circular reference)');
                    $result .= ",\n";

                    continue;
                }

                self::$visitedObjects[$value] = true;

                if ($value instanceof CarbonInterface) {
                    $utcCarbon = $value->copy()->setTimezone(new DateTimeZone('UTC'));
                    $result .= self::safeVarExport($utcCarbon->toIso8601String());
                    $result .= ",\n";

                    continue;
                }

                if ($value instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) { // @phpstan-ignore-line
                    $value = $value->toArray(request()); // @phpstan-ignore-line
                } else {
                    try {
                        $value = $value->toArray();
                    } catch (\Throwable $e) {
                        $result .= self::safeVarExport('(conversion error)');
                        $result .= ",\n";

                        continue;
                    }
                }
            }

            if (is_array($value)) {
                $result .= self::convertArrayToPhpSyntax($value, $indentLevel + 1, $depth + 1);
            } elseif ($value instanceof DateTimeInterface) {
                $immutable = \DateTimeImmutable::createFromInterface($value)
                    ->setTimezone(new DateTimeZone('UTC'));

                $result .= self::safeVarExport($immutable->format(DateTimeInterface::ATOM));
            } elseif (is_resource($value)) {
                $result .= "'(resource)'";
            } elseif (is_string($value)) {
                $result .= self::safeVarExport($value);
            } elseif (is_object($value)) {
                if (isset(self::$visitedObjects[$value])) {
                    $result .= self::safeVarExport('(circular reference)');
                } else {
                    self::$visitedObjects[$value] = true;
                    $result .= self::safeVarExport($value);
                }
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

    private static function safeVarExport(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_object($value)) {
            return "'" . addslashes(get_class($value)) . "'";
        }

        return "'(unknown type)'";
    }
}
