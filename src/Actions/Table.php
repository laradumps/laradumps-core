<?php

namespace LaraDumps\LaraDumpsCore\Actions;

class Table
{
    public static function make(iterable|object $data, string $name = ''): array
    {
        $values  = [];
        $columns = [];

        if (class_exists('Illuminate\Support\Collection') && $data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        $data = self::convertObjectsToArray($data);

        foreach ($data as $row) {
            foreach ($row as $key => $item) {
                if (!in_array($key, $columns)) {
                    $columns[] = $key;
                }
            }

            $value = [];

            foreach ($columns as $column) {
                $value[$column] = (string) $row[$column];
            }

            $values[] = $value;
        }

        return [
            'fields' => $columns,
            'values' => $values,
            'header' => $columns,
            'label'  => $name,
        ];
    }

    private static function convertObjectsToArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data[$key] = (array) $value;
            } elseif (is_array($value)) {
                $data[$key] = self::convertObjectsToArray($value);
            }
        }

        return $data;
    }
}
