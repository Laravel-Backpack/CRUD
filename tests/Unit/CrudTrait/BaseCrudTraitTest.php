<?php

namespace Backpack\CRUD\Tests\Unit\CrudTrait;

use Backpack\CRUD\Tests\BaseTest;

abstract class BaseCrudTraitTest extends BaseTest
{
    protected function getPackageAliases($app)
    {
        return [
            '\App' => \Illuminate\Support\Facades\App::class,
            '\Request' => \Illuminate\Support\Facades\Request::class,
        ];
    }
}
