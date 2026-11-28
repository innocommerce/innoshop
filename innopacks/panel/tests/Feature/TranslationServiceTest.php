<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use Illuminate\Support\Facades\DB;
use InnoShop\Panel\Services\TranslationService;
use RuntimeException;
use Tests\TestCase;

/**
 * Installing a locale must copy the default-locale description rows through
 * server-side INSERT ... SELECT (not a PHP-side full-table load) and stay
 * all-or-nothing with the locale record itself. The copy only happens when
 * the "Autofill language" panel switch is enabled.
 */
class TranslationServiceTest extends TestCase
{
    public function test_create_locale_copies_default_locale_translations_server_side(): void
    {
        $this->enableAutofill();
        $from = setting_locale_code();
        $this->seedSourceRows($from);

        // The copy writes CURRENT_TIMESTAMP from the database, so the
        // comparison has to use the database clock as well. Reading the PHP
        // clock breaks whenever the app timezone and the database timezone
        // differ (APP_TIMEZONE=PRC against a UTC MySQL, for instance).
        $startedAt = DB::selectOne('SELECT CURRENT_TIMESTAMP as ts')->ts;

        $locale = TranslationService::getInstance()->createLocale([
            'name'     => 'Testish',
            'code'     => 'zz',
            'image'    => 'images/flags/zz.svg',
            'position' => 99,
            'active'   => 1,
        ]);

        $this->assertSame('zz', $locale->code);
        $this->assertDatabaseHas('locales', ['code' => 'zz']);

        $copiedArticles = DB::table('article_translations')->where('locale', 'zz')->orderBy('title')->get();
        $this->assertCount(2, $copiedArticles);
        $this->assertSame(['Alpha', 'Beta'], $copiedArticles->pluck('title')->all());
        $this->assertSame([11, 12], $copiedArticles->pluck('article_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame('<p>body</p>', $copiedArticles[0]->content);

        $copiedGroups = DB::table('attribute_group_translations')->where('locale', 'zz')->get();
        $this->assertCount(1, $copiedGroups);
        $this->assertSame('Group', $copiedGroups[0]->name);
        $this->assertSame(21, (int) $copiedGroups[0]->attribute_group_id);

        // New ids for every copied row, refreshed timestamps, source untouched.
        $this->assertNotSame(
            DB::table('article_translations')->where('locale', $from)->orderBy('id')->pluck('id')->all(),
            $copiedArticles->pluck('id')->all()
        );
        foreach ($copiedArticles->merge($copiedGroups) as $row) {
            $this->assertGreaterThanOrEqual($startedAt, $row->created_at);
            $this->assertGreaterThanOrEqual($startedAt, $row->updated_at);
        }

        $source = DB::table('article_translations')->where('locale', $from)->orderBy('id')->get();
        $this->assertCount(2, $source);
        foreach ($source as $row) {
            $this->assertSame('2020-01-01 00:00:00', $row->created_at);
        }
    }

    public function test_create_locale_does_not_copy_content_when_autofill_is_disabled(): void
    {
        config(['inno.system.auto_fill_lang' => '0']);

        $from = setting_locale_code();
        $this->seedSourceRows($from);

        $locale = TranslationService::getInstance()->createLocale([
            'name'     => 'Testish',
            'code'     => 'zz',
            'image'    => 'images/flags/zz.svg',
            'position' => 99,
            'active'   => 1,
        ]);

        // The locale itself is installed, but no default-locale content is copied.
        $this->assertSame('zz', $locale->code);
        $this->assertDatabaseHas('locales', ['code' => 'zz']);
        $this->assertSame(0, DB::table('article_translations')->where('locale', 'zz')->count());
        $this->assertSame(0, DB::table('attribute_group_translations')->where('locale', 'zz')->count());
        $this->assertSame(0, DB::table('product_translations')->where('locale', 'zz')->count());

        // Source content stays untouched.
        $this->assertSame(2, DB::table('article_translations')->where('locale', $from)->count());
    }

    public function test_create_locale_rolls_back_everything_when_a_copy_fails(): void
    {
        $this->enableAutofill();
        $from = setting_locale_code();
        $this->seedSourceRows($from);

        $fired = false;
        DB::beforeExecuting(function (string $sql) use (&$fired): void {
            if (! $fired && str_contains($sql, 'attribute_group_translations')) {
                $fired = true;
                throw new RuntimeException('simulated copy failure');
            }
        });

        try {
            TranslationService::getInstance()->createLocale([
                'name'     => 'Testish',
                'code'     => 'zz',
                'image'    => 'images/flags/zz.svg',
                'position' => 99,
                'active'   => 1,
            ]);
            $this->fail('Expected the simulated copy failure to bubble up.');
        } catch (RuntimeException) {
        }

        // Neither the locale record nor a partial copy may survive.
        $this->assertDatabaseMissing('locales', ['code' => 'zz']);
        $this->assertSame(0, DB::table('article_translations')->where('locale', 'zz')->count());
        $this->assertSame(0, DB::table('attribute_group_translations')->where('locale', 'zz')->count());
    }

    private function enableAutofill(): void
    {
        config(['inno.system.auto_fill_lang' => '1']);
    }

    private function seedSourceRows(string $locale): void
    {
        $stale = '2020-01-01 00:00:00';

        DB::table('article_translations')->insert([
            [
                'article_id'       => 11, 'locale' => $locale, 'title' => 'Alpha', 'summary' => 'first',
                'image'            => null, 'content' => '<p>body</p>', 'meta_title' => null,
                'meta_description' => null, 'meta_keywords' => null,
                'created_at'       => $stale, 'updated_at' => $stale,
            ],
            [
                'article_id'       => 12, 'locale' => $locale, 'title' => 'Beta', 'summary' => null,
                'image'            => null, 'content' => null, 'meta_title' => null,
                'meta_description' => null, 'meta_keywords' => null,
                'created_at'       => $stale, 'updated_at' => $stale,
            ],
        ]);

        DB::table('attribute_group_translations')->insert([
            'attribute_group_id' => 21, 'locale' => $locale, 'name' => 'Group',
            'created_at'         => $stale, 'updated_at' => $stale,
        ]);
    }
}
