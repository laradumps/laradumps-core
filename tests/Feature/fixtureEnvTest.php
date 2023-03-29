<?php

it('changes the env on the fly', function () {
    $name = 'Luan';

    fixtureEnv('ds_env', ['name' => $name]);

    expect(getenv('name'))->toBe($name);
});
