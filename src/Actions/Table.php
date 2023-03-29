<?php

namespace LaraDumps\LaraDumpsCore\Actions;

class Table
{
    public function __construct(
        private mixed $data = [],
        private string $name = '',
    ) {
    }

    public function make(): array
    {
        $values  = [];
        $columns = [];
        $data    = $this->data;

        if (class_exists('Illuminate\Support\Collection')) {
            /** @var object|string $data */
            $data = $this->data;

            if (method_exists($data, 'toArray')) {
                /** @phpstan-ignore-next-line */
                $data = $data->toArray();
            }
        }

        /** @var array $data */
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
            'label'  => $this->name,
        ];
    }
}
