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
use Illuminate\Support\Facades\Http;
use InnoShop\Common\Services\GeoLite2Service;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GeoLite2ServiceTest extends TestCase
{
    private string $dbDir;

    private string $dbPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbDir  = storage_path('framework/testing/geolite2-'.uniqid());
        $this->dbPath = $this->dbDir.'/geolite2-city.mmdb';
        config(['innoshop.geo_lite_path' => $this->dbPath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dbDir);
        parent::tearDown();
    }

    private function seedExistingDatabase(string $content = 'EXISTING-DB'): void
    {
        File::ensureDirectoryExists($this->dbDir);
        File::put($this->dbPath, $content);
    }

    #[Test]
    public function test_http_failure_keeps_existing_database(): void
    {
        $this->seedExistingDatabase();
        Http::fake(['*' => Http::response('', 500)]);

        $result = (new GeoLite2Service)->downloadDatabase();

        $this->assertFalse($result['success']);
        $this->assertSame('EXISTING-DB', File::get($this->dbPath), 'old database must survive a failed download');
        $this->assertFileDoesNotExist($this->dbPath.'.tmp');
    }

    #[Test]
    public function test_empty_body_is_rejected(): void
    {
        $this->seedExistingDatabase();
        Http::fake(['*' => Http::response('')]);

        $result = (new GeoLite2Service)->downloadDatabase();

        $this->assertFalse($result['success']);
        $this->assertSame('EXISTING-DB', File::get($this->dbPath));
        $this->assertFileDoesNotExist($this->dbPath.'.tmp');
    }

    #[Test]
    public function test_undersized_body_is_rejected_without_touching_live_database(): void
    {
        $this->seedExistingDatabase();
        Http::fake(['*' => Http::response(str_repeat('x', 1024))]);

        $result = (new GeoLite2Service)->downloadDatabase();

        $this->assertFalse($result['success']);
        $this->assertSame('EXISTING-DB', File::get($this->dbPath), 'old database must survive an undersized download');
        $this->assertFileDoesNotExist($this->dbPath.'.tmp');
    }

    #[Test]
    public function test_corrupt_content_is_rejected_and_tmp_cleaned(): void
    {
        $this->seedExistingDatabase();
        Http::fake(['*' => Http::response(str_repeat('junk', 512 * 1024))]);

        $result = (new GeoLite2Service)->downloadDatabase();

        $this->assertFalse($result['success']);
        $this->assertSame('EXISTING-DB', File::get($this->dbPath), 'old database must survive a corrupt download');
        $this->assertFileDoesNotExist($this->dbPath.'.tmp', 'temp file must be cleaned up after failed verification');
    }

    #[Test]
    public function test_failed_download_without_existing_database_stays_absent(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $result = (new GeoLite2Service)->downloadDatabase();

        $this->assertFalse($result['success']);
        $this->assertFileDoesNotExist($this->dbPath);
    }
}
