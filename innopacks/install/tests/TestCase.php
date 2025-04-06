<?php

namespace InnoShop\Install\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Orchestra\Testbench\Concerns\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize translator
        $loader = new FileLoader(
            app('files'),
            base_path('lang')
        );

        $translator = new Translator($loader, 'zh-cn');
        App::instance('translator', $translator);
    }
}
