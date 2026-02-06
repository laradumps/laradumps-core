<?php

use LaraDumps\LaraDumpsCore\Actions\ConvertArrayToPhpSyntax;

describe('ConvertArrayToPhpSyntax', function () {
    describe('circular reference detection', function () {
        it('handles simple objects with circular references', function () {
            $obj = new class () {
                public string $name = 'Object 1';

                public function toArray()
                {
                    return [
                        'name' => $this->name,
                        'self' => $this,
                    ];
                }
            };

            $result = ConvertArrayToPhpSyntax::convert($obj);

            expect($result)->toBeString();
        });

        it('handles array with objects containing circular references', function () {
            $data = [
                'item1' => new class () {
                    public function toArray()
                    {
                        return [
                            'id'   => 1,
                            'self' => $this,
                        ];
                    }
                },
            ];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->not->toThrow(\Throwable::class);
        });

        it('prevents memory exhaustion with deeply nested circular structures', function () {
            $parent = new class () {
                public function toArray()
                {
                    return [
                        'id'       => 'parent',
                        'children' => [
                            new class ($this) {
                                public function __construct(private $parent)
                                {
                                }

                                public function toArray()
                                {
                                    return [
                                        'id'     => 'child',
                                        'parent' => $this->parent,
                                    ];
                                }
                            },
                        ],
                    ];
                }
            };

            $result = ConvertArrayToPhpSyntax::convert($parent);

            expect($result)->toBeString();
        });
    });

    describe('normal object conversion', function () {
        it('converts simple arrays', function () {
            $data = ['name' => 'John', 'age' => 30];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('name')
                ->and($result)->toContain('John')
                ->and($result)->toContain('age')
                ->and($result)->toContain('30');
        });

        it('converts nested arrays', function () {
            $data = [
                'user' => [
                    'name'  => 'John',
                    'email' => 'john@example.com',
                ],
                'active' => true,
            ];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('user')
                ->and($result)->toContain('name')
                ->and($result)->toContain('email');
        });

        it('converts objects with toArray method', function () {
            $obj = new class () {
                public function toArray()
                {
                    return ['key' => 'value', 'number' => 42];
                }
            };

            $result = ConvertArrayToPhpSyntax::convert($obj);

            expect($result)->toBeString()
                ->and($result)->toContain('key')
                ->and($result)->toContain('value');
        });

        it('handles scalar values', function () {
            $result = ConvertArrayToPhpSyntax::convert('string value');
            expect($result)->toBe('string value');

            $result = ConvertArrayToPhpSyntax::convert(42);
            expect($result)->toBe(42);

            $result = ConvertArrayToPhpSyntax::convert(true);
            expect($result)->toBe(true);

            $result = ConvertArrayToPhpSyntax::convert(null);
            expect($result)->toBeNull();
        });

        it('handles mixed array with different types', function () {
            $obj = new class () {
                public function toArray()
                {
                    return ['converted' => true];
                }
            };

            $data = [
                'string' => 'value',
                'number' => 123,
                'bool'   => false,
                'null'   => null,
                'object' => $obj,
                'nested' => ['inner' => 'data'],
            ];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('string')
                ->and($result)->toContain('number');
        });
    });

    describe('error handling', function () {
        it('handles objects that throw exceptions in toArray', function () {
            $obj = new class () {
                public function toArray()
                {
                    throw new \Exception('toArray failed');
                }
            };

            $result = ConvertArrayToPhpSyntax::convert($obj);

            expect($result)->toBe('(conversion error)');
        });

        it('handles objects without toArray method', function () {
            $obj      = new \stdClass();
            $obj->key = 'value';

            $result = ConvertArrayToPhpSyntax::convert($obj);

            expect($result)->not->toThrow(\Throwable::class);
        });
    });

    describe('edge cases', function () {
        it('handles empty arrays', function () {
            $result = ConvertArrayToPhpSyntax::convert([]);

            expect($result)->toBeString()
                ->and($result)->toContain('[')
                ->and($result)->toContain(']');
        });

        it('handles array with numeric keys', function () {
            $data = [1 => 'first', 2 => 'second', 0 => 'zero'];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('first')
                ->and($result)->toContain('second');
        });

        it('handles boolean values correctly', function () {
            $data = ['true' => true, 'false' => false];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('true')
                ->and($result)->toContain('false');
        });

        it('handles null values', function () {
            $data = ['nullable' => null];

            $result = ConvertArrayToPhpSyntax::convert($data);

            expect($result)->toBeString()
                ->and($result)->toContain('null');
        });

        it('handles resources gracefully', function () {
            $data = ['resource' => fopen('php://memory', 'r')];

            try {
                $result = ConvertArrayToPhpSyntax::convert($data);
                expect($result)->toBeString()
                    ->and($result)->toContain('resource');
            } finally {
                fclose($data['resource']);
            }
        });
    });
});
