<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Check whether a business-table row references a media://{id} reference or
 * a raw storage_key.
 *
 * Bare `LIKE '%media://123%'` is unsafe: it matches `media://1234` too because
 * the latter contains the former as a substring. For media refs we verify the
 * digit boundary in PHP — anything but another digit at the next character
 * counts as a real reference. Storage-key paths are uniquely keyed, so a
 * plain exact match is enough for them.
 *
 * Shared between MediaFile::isInUse() (PruneVideoPosters, cleanupReplacedPoster)
 * and the unique-index migration's pre-flight guard, so the "safe to delete"
 * semantic in both places agrees.
 */
class MediaUsageScanner
{
    /**
     * Whether $table.$column has ANY row that references $ref.
     */
    public static function rowReferences(string $table, string $column, string $ref): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        if (str_starts_with($ref, 'media://')) {
            $rows = DB::table($table)
                ->select($column)
                ->where($column, 'like', '%'.$ref.'%')
                ->limit(500)
                ->get();
            foreach ($rows as $row) {
                if (self::matchRefBoundary((string) ($row->{$column} ?? ''), $ref)) {
                    return true;
                }
            }

            return false;
        }

        // storage_key paths are unique; exact match is enough and avoids the
        // prefix hazard (e.g. "static/media/videos/foo.mp4" must not match
        // a row that legitimately references "static/media/videos/foo.mp4.bak").
        return DB::table($table)->where($column, '=', $ref)->exists();
    }

    /**
     * Return the set of row IDs in $table that reference $ref via $column.
     * Used by usageCount() so a row referencing the same media via multiple
     * fields only counts once.
     *
     * @return array<int, true>
     */
    public static function referencedIds(string $table, string $column, string $ref): array
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return [];
        }

        $matches = [];

        if (str_starts_with($ref, 'media://')) {
            $rows = DB::table($table)
                ->select('id', $column)
                ->where($column, 'like', '%'.$ref.'%')
                ->limit(500)
                ->get();
            foreach ($rows as $row) {
                if (self::matchRefBoundary((string) ($row->{$column} ?? ''), $ref)) {
                    $matches[(int) $row->id] = true;
                }
            }
        } else {
            $rows = DB::table($table)
                ->select('id')
                ->where($column, '=', $ref)
                ->limit(500)
                ->get();
            foreach ($rows as $row) {
                $matches[(int) $row->id] = true;
            }
        }

        return $matches;
    }

    /**
     * Rewrite every "media://{oldId}" reference in $table.$column to
     * "media://{newId}".
     *
     * Used when two media rows share one storage_key and the loser is about
     * to be dropped (the unique-index migration). Both rows point at the
     * same stored object, so re-pointing the reference keeps the rendered
     * image identical — leaving it on the deleted row would not.
     *
     * The edit is a raw text replacement on the stored value: a JSON column
     * keeps its stored form apart from the id, and no decode/re-encode
     * round trip re-escapes the whole value. Both slash forms are matched
     * ("media://1" and the JSON-escaped "media:\/\/1") and the original form
     * is kept — MySQL normalizes JSON columns to the plain form, while a
     * plain TEXT column (the SQLite test driver) keeps whatever the writer
     * produced.
     *
     * @param  string  $table  Business table holding the reference.
     * @param  string  $column  Column holding the reference (JSON array or plain string).
     * @param  int  $oldId  Media id being replaced.
     * @param  int  $newId  Media id to point at instead.
     * @return int Number of rows rewritten.
     */
    public static function relinkReference(string $table, string $column, int $oldId, int $newId): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        $oldRef = 'media://'.$oldId;

        // (?<!\d) / (?!\d) keeps the digit boundary: media://123 must not
        // touch media://1234. (?:\\?/){2} matches "://" as well as the
        // JSON-escaped ":\/\/".
        $pattern = '~(?<!\d)media:(?:\\\\?/){2}'.$oldId.'(?!\d)~';

        $rewritten = 0;

        DB::table($table)
            ->select('id', $column)
            ->where(function ($query) use ($column, $oldRef, $oldId) {
                // Both slash forms: MySQL's JSON columns normalize to the
                // plain one, a TEXT column keeps the escaped one.
                $query->where($column, 'like', '%'.$oldRef.'%')
                    ->orWhere($column, 'like', '%media:\\/\\/'.$oldId.'%');
            })
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table, $column, $pattern, $newId, &$rewritten) {
                foreach ($rows as $row) {
                    $value = (string) ($row->{$column} ?? '');

                    $updated = preg_replace_callback($pattern, function (array $matches) use ($newId) {
                        // Keep the stored slash form: "media:\/\/1" stays
                        // escaped, "media://1" stays plain.
                        $slashes = str_contains($matches[0], '\\') ? '\\/\\/' : '//';

                        return 'media:'.$slashes.$newId;
                    }, $value);

                    if ($updated === null || $updated === $value) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([$column => $updated]);
                    $rewritten++;
                }
            }, 'id');

        return $rewritten;
    }

    /**
     * Find $needle in $haystack and verify it sits on a digit boundary.
     *
     * Media refs are digit-suffixed (`media://123`); a genuine reference ends
     * at the next non-digit character (or end of string). The boundary check
     * rules out the `media://123` vs `media://1234` false positive that a
     * naive substring LIKE would produce.
     */
    public static function matchRefBoundary(string $haystack, string $needle): bool
    {
        $offset    = 0;
        $needleLen = strlen($needle);
        while (($pos = strpos($haystack, $needle, $offset)) !== false) {
            $next = $haystack[$pos + $needleLen] ?? '';
            if ($next === '' || ! ctype_digit($next)) {
                return true;
            }
            $offset = $pos + 1;
        }

        return false;
    }
}
