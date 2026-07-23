<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MediaUrlResolverIntegrationTest extends TestCase
{
    #[Test]
    public function resolve_returns_empty_url_for_missing_media_reference(): void
    {
        $url = MediaUrlResolver::getInstance()->resolve('media://999999');
        $this->assertNotSame('media://999999', $url);
    }

    #[Test]
    public function relocate_updates_storage_key_for_existing_media(): void
    {
        $media = MediaFile::create([
            'disk'        => 'local',
            'storage_key' => 'static/media/old/path.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->relocate($media->id, 'static/media/new/path.jpg');
        $this->assertTrue($ok);

        $media->refresh();
        $this->assertSame('static/media/new/path.jpg', $media->storage_key);
    }

    #[Test]
    public function relocate_returns_false_for_missing_media_id(): void
    {
        $this->assertFalse(MediaUrlResolver::getInstance()->relocate(999999, 'static/media/whatever.jpg'));
    }

    #[Test]
    public function relocate_by_key_finds_media_via_old_storage_key(): void
    {
        $media = MediaFile::create([
            'disk'        => 'local',
            'storage_key' => 'static/media/before.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->relocateByKey('static/media/before.jpg', 'static/media/after.jpg');
        $this->assertTrue($ok);

        $media->refresh();
        $this->assertSame('static/media/after.jpg', $media->storage_key);
    }

    #[Test]
    public function remove_by_key_soft_deletes_media(): void
    {
        $media = MediaFile::create([
            'disk'        => 'local',
            'storage_key' => 'static/media/trash.jpg',
            'source'      => 'upload',
        ]);

        $ok = MediaUrlResolver::getInstance()->removeByKey('static/media/trash.jpg');
        $this->assertTrue($ok);

        // Soft-deleted: not found in default query, but still in DB with deleted_at set.
        $this->assertNull(MediaFile::find($media->id));
        $this->assertNotNull(MediaFile::withTrashed()->find($media->id));
    }

    #[Test]
    public function register_dedups_by_checksum(): void
    {
        $first = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/dup1.jpg',
            'checksum'    => str_repeat('a', 64),
        ]);

        $second = MediaUrlResolver::getInstance()->register([
            'disk'        => 'local',
            'storage_key' => 'static/media/dup2.jpg',
            'checksum'    => str_repeat('a', 64),
        ]);

        $this->assertSame($first->id, $second->id);
    }

    #[Test]
    public function register_copy_creates_independent_record_even_with_same_checksum(): void
    {
        $first = MediaUrlResolver::getInstance()->registerCopy(
            'static/media/copy1.jpg',
            'local',
            ['checksum' => str_repeat('b', 64)]
        );

        $second = MediaUrlResolver::getInstance()->registerCopy(
            'static/media/copy2.jpg',
            'local',
            ['checksum' => str_repeat('b', 64)]
        );

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('copy', $second->source);
    }

    #[Test]
    public function usage_count_finds_media_reference_in_business_table(): void
    {
        $media = MediaFile::create([
            'disk'        => 'local',
            'storage_key' => 'static/media/used.jpg',
            'source'      => 'upload',
        ]);

        // Insert a brand row that references this media via media:// link.
        DB::table('brands')->insert([
            'name'       => 'Test Brand',
            'slug'       => 'test-brand',
            'first'      => 'T',
            'logo'       => MediaUrlResolver::buildReference($media->id),
            'active'     => 1,
            'position'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usage = $media->usageCount();
        $this->assertArrayHasKey('brands', $usage);
        $this->assertGreaterThanOrEqual(1, $usage['brands']);
        $this->assertGreaterThanOrEqual(1, $media->totalUsage());
    }
}
