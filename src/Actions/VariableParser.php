<?php

namespace LaraDumps\LaraDumpsCore\Actions;

class VariableParser
{
    public static function parse(string $file, int $callLine, int $argCount): array
    {
        if (!is_file($file) || !is_readable($file)) {
            return self::fallback($argCount, $callLine);
        }

        $source   = file_get_contents($file);
        if ($source === false) {
            return self::fallback($argCount, $callLine);
        }
        $tokens   = token_get_all($source);
        $argNames = self::extractArgNames($tokens, $callLine, $argCount);

        $result = [];

        foreach ($argNames as $i => $name) {
            $line = $name !== null
                ? self::findDeclarationLine($tokens, $name, $callLine)
                : $callLine;

            $result[] = [
                'name' => $name ?? 'arg' . $i,
                'line' => $line,
            ];
        }

        if (empty($result)) {
            return self::fallback($argCount, $callLine);
        }

        return $result;
    }

    private static function extractArgNames(array $tokens, int $callLine, int $argCount): array
    {
        $count       = count($tokens);
        $dsStart     = null;
        $currentLine = 1;

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                $currentLine = $token[2];
            }

            if (
                is_array($token)
                && $token[0] === T_STRING
                && in_array($token[1], ['ds', 'dsd', 'dsq'], true)
                && $currentLine === $callLine
            ) {
                $j = $i + 1;

                while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $j++;
                }

                if (isset($tokens[$j]) && $tokens[$j] === '(') {
                    $dsStart = $j;

                    break;
                }
            }
        }

        if ($dsStart === null) {
            return array_fill(0, $argCount, null);
        }

        return self::extractArgsFromParens($tokens, $dsStart, $argCount);
    }

    private static function extractArgsFromParens(array $tokens, int $parenOpen, int $argCount): array
    {
        $names     = [];
        $depth     = 0;
        $count     = count($tokens);
        $argTokens = [];
        $i         = $parenOpen;

        while ($i < $count) {
            $token = $tokens[$i];

            if ($token === '(') {
                $depth++;

                if ($depth > 1) {
                    $argTokens[] = $token;
                }

                $i++;

                continue;
            }

            if ($token === ')') {
                $depth--;

                if ($depth === 0) {
                    $names[] = self::resolveArgName($argTokens);

                    break;
                }

                $argTokens[] = $token;
                $i++;

                continue;
            }

            if ($token === ',' && $depth === 1) {
                $names[]   = self::resolveArgName($argTokens);
                $argTokens = [];
                $i++;

                continue;
            }

            if (is_array($token) && $token[0] === T_WHITESPACE) {
                $i++;

                continue;
            }

            $argTokens[] = $token;
            $i++;
        }

        while (count($names) < $argCount) {
            $names[] = null;
        }

        return array_slice($names, 0, $argCount);
    }

    private static function resolveArgName(array $argTokens): ?string
    {
        $significant = array_filter($argTokens, fn ($t) => !(is_array($t) && $t[0] === T_WHITESPACE));
        $significant = array_values($significant);

        if (count($significant) === 1 && is_array($significant[0]) && $significant[0][0] === T_VARIABLE) {
            return ltrim($significant[0][1], '$');
        }

        return null;
    }

    private static function findDeclarationLine(array $tokens, string $varName, int $callLine): int
    {
        $varToken = '$' . $varName;
        $lastLine = $callLine;
        $count    = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if ($token[2] >= $callLine) {
                break;
            }

            if ($token[0] !== T_VARIABLE || $token[1] !== $varToken) {
                continue;
            }

            $j = $i + 1;

            while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                $j++;
            }

            if (isset($tokens[$j]) && $tokens[$j] === '=') {
                $next = $tokens[$j + 1] ?? null;

                if ($next !== '=') {
                    $lastLine = $token[2];
                }
            }
        }

        return $lastLine;
    }

    private static function fallback(int $argCount, int $callLine): array
    {
        $result = [];

        for ($i = 0; $i < $argCount; $i++) {
            $result[] = ['name' => 'arg' . $i, 'line' => $callLine];
        }

        return $result;
    }
}
