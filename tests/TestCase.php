<?php

namespace Tests;

use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LocaleSeeder;
use Database\Seeders\StateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run necessary seeders for testing
        $this->seed([
            LocaleSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
        ]);
    }
}
