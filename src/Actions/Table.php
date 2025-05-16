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

        foreach ($data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }

            foreach ($row as $key => $item) {
                if (!in_array($key, $columns)) {
                    $columns[] = $key;
                }
            }

            $value = [];

            foreach ($columns as $column) {
                $value[$column] = (string) gettype($row[$column]) === 'string'
                    ? $row[$column]
                    : json_encode($row[$column]);
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
}
