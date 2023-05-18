<?php

namespace LaraDumps\LaraDumpsCore\Support;

use LaraDumps\LaraDumpsCore\Actions\Support;
use Ramsey\Uuid\Uuid;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Dumper
{
    public static function dump(mixed $arguments, int $maxDepth = null): array
    {
        $id = Uuid::uuid4()->toString();

        if (is_null($arguments)) {
            return [null, $id];
        }

        if (is_string($arguments)) {
            return [$arguments, $id];
        }

        if (is_int($arguments)) {
            return [$arguments, $id];
        }

        if (is_bool($arguments)) {
            return [$arguments, $id];
        }

        $varCloner = new VarCloner();

        $dumper = new HtmlDumper();

        if (!empty($maxDepth)) {
            $dumper->setDisplayOptions([
                'maxDepth' => $maxDepth,
            ]);
        }

        $htmlDumper = (string) $dumper->dump($varCloner->cloneVar($arguments), true);

        $pre = Support::cut($htmlDumper, '<pre ', '</pre>');

        $id = Support::between($pre, 'class=sf-dump id=sf-dump-', ' data-indent-pad="  "');

        return [
            $pre,
            $id,
        ];
    }
}
