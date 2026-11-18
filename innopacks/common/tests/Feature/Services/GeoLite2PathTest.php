<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Support\Facades\File;
use InnoShop\Common\Services\GeoLite2Service;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The official download is served as `GeoLite2-City.mmdb`, while the config
 * default used to be the lower-case `geolite2-city.mmdb`. On a case-sensitive
 * filesystem a manually downloaded database was never picked up, so the
 * service falls back to a case-insensitive scan of the storage directory.
 */
class GeoLite2PathTest extends TestCase
{
    private string $dbDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbDir = storage_path('framework/testing/geolite2-path-'.uniqid());
        File::ensureDirectoryExists($this->dbDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dbDir);
        parent::tearDown();
    }

    #[Test]
    public function test_default_config_path_uses_the_official_file_name(): void
    {
        $config = require dirname(__DIR__, 3).'/config/innoshop.php';

        $this->assertSame(
            'GeoLite2-City.mmdb',
            basename($config['geo_lite_path']),
            'the default must match the file name served by the download URL'
        );
    }

    #[Test]
    public function test_existing_configured_database_is_used(): void
    {
        $path = $this->dbDir.'/GeoLite2-City.mmdb';
        File::put($path, 'DB');
        config(['innoshop.geo_lite_path' => $path]);

        $service = new GeoLite2Service;

        $this->assertSame($path, $service->getDatabasePath());
        $this->assertTrue($service->isAvailable());
    }

    #[Test]
    public function test_lower_case_database_is_found_by_the_fallback_scan(): void
    {
        $legacyPath = $this->dbDir.'/geolite2-city.mmdb';
        File::put($legacyPath, 'DB');
        config(['innoshop.geo_lite_path' => $this->dbDir.'/GeoLite2-City.mmdb']);

        $service  = new GeoLite2Service;
        $resolved = $service->getDatabasePath();

        $this->assertTrue($service->isAvailable(), 'the legacy file name must still be found');
        $this->assertTrue(File::exists($resolved));
        $this->assertStringEndsWith('city.mmdb', strtolower($resolved));
    }

    #[Test]
    public function test_database_with_another_name_is_found_by_the_fallback_scan(): void
    {
        // The configured file does not exist under any name, so the service
        // has to fall back to the city database in the storage directory.
        $legacyPath = $this->dbDir.'/legacy-city.mmdb';
        File::put($legacyPath, 'DB');
        config(['innoshop.geo_lite_path' => $this->dbDir.'/GeoLite2-City.mmdb']);

        $service = new GeoLite2Service;

        $this->assertSame($legacyPath, $service->getDatabasePath());
        $this->assertTrue($service->isAvailable());
    }

    #[Test]
    public function test_missing_database_keeps_the_configured_path(): void
    {
        $configured = $this->dbDir.'/GeoLite2-City.mmdb';
        config(['innoshop.geo_lite_path' => $configured]);

        $service = new GeoLite2Service;

        $this->assertSame($configured, $service->getDatabasePath());
        $this->assertFalse($service->isAvailable());
    }
}
