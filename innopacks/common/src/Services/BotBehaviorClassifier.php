<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Behavioural bot detection — the missing half of bot classification.
 *
 * BotClassifier only looks at the User-Agent string. A crawler that sends a
 * real browser UA (Chrome/153, Edge/155, ...) therefore matches no pattern
 * and is written into the visits table as a genuine session. That is how
 * the 2026-10-25~27 wave polluted the panel: ~124k fake sessions were
 * counted as visitors while every conversion stayed at zero.
 *
 * This classifier adds the missing behavioural dimension, evaluated per
 * request before anything is written:
 *
 *   1. A client that never sends the session cookie creates a brand-new
 *      session on every single request. Real browsers always send the
 *      cookie back, so "no cookie" reliably marks "not a returning
 *      visitor". (The session cookie is encrypted by EncryptCookies, so a
 *      crawler cannot forge a valid one either — an invalid cookie is
 *      dropped and the request counts as cookie-less.)
 *   2. One IP doing that many times in a day, with every one of its
 *      sessions a single page, is a burst, not a person.
 *
 * The third condition — "every session is a single page" — is what keeps
 * shared egress IPs out of the blast radius. A mobile carrier CGNAT pool or
 * an office behind one IP produces many sessions, but a human browsing
 * opens several pages per session, while a cookie-less crawler fetches one
 * page and moves on. Verified against production data: the busiest single
 * IP (the store owner's admin machine, referrers pointing at /panel/*)
 * created 124 sessions with ~18 page views each, and is never flagged.
 *
 * The 2026-10-30 wave forced two more rules into this second pass. It
 * rotated ~28k residential-proxy IPs (one or two requests each, so the
 * per-IP counter never moved) while serving a uniform library of stale
 * Chrome/Firefox versions, and half of it entered through the site's
 * e-mail-tracking alias vhosts instead of www. Hence:
 *
 *   3. Entry-host rule — a guest with no session cookie arriving through a
 *      vhost outside the configured allowlist is a crawler. Real humans
 *      only click e-mail links onto www; the alias hosts exist for click
 *      tracking and their traffic is 99%+ crawls. The cookie condition
 *      keeps a real clicker's follow-up pages (it sends the cookie back
 *      from page two) out of the blast radius.
 *   4. Stale-UA burst rule — cookie-less first hits sharing one outdated
 *      browser major version are counted per hour in the cache; past a
 *      threshold the version is a rotating crawler library. A real
 *      population never concentrates 60 first hits on one exact old
 *      version in one hour — the 10-30 flood served ~2,000 per version
 *      per hour on 14 different stale versions.
 *
 * The verdict is memoised on the Request so the visit writer and the event
 * writer always agree and the per-IP checks run exactly once per request.
 */
class BotBehaviorClassifier
{
    /**
     * Brand code used in bot_daily for behaviour-detected bots.
     */
    public const BRAND = 'behavior';

    /**
     * Brand code for crawlers entering through a non-allowlisted vhost.
     */
    public const BRAND_HOST = 'host';

    /**
     * Brand code for stale-UA version bursts.
     */
    public const BRAND_UA_BURST = 'ua_burst';

    /**
     * Reason recorded for a client that exceeded the cookie-less burst limit.
     */
    public const REASON_COOKIELESS_BURST = 'cookieless_burst';

    /**
     * Reason recorded for an entry through a non-allowlisted host.
     */
    public const REASON_INVALID_HOST = 'invalid_host';

    /**
     * Reason recorded for a stale-UA version burst.
     */
    public const REASON_STALE_UA_BURST = 'stale_ua_burst';

    /**
     * Request attribute holding the memoised verdict.
     */
    private const ATTRIBUTE = 'inno.bot_behavior_verdict';

    /**
     * UA version tokens to fingerprint, in priority order. Chromium forks
     * (Edge, Opera, ...) carry the Chrome/ token, so they group under the
     * Chromium major version naturally.
     *
     * @var array<string, string>
     */
    private const UA_VERSION_TOKENS = [
        'Chrome/'  => 'chrome',
        'Firefox/' => 'firefox',
        'Version/' => 'safari',
        'Edg/'     => 'edge',
    ];

    /**
     * Classify a request by behaviour.
     *
     * @param  Request  $request
     * @return array{is_bot: bool, brand: string, category: string, reason: string}|null
     *                                                                                   null for a human, a bot_daily-shaped row for a bot.
     */
    public function classify(Request $request): ?array
    {
        if ($request->attributes->has(self::ATTRIBUTE)) {
            return $request->attributes->get(self::ATTRIBUTE);
        }

        $verdict = $this->evaluate($request);

        $request->attributes->set(self::ATTRIBUTE, $verdict);

        return $verdict;
    }

    /**
     * Evaluate the behavioural signals for one request.
     */
    protected function evaluate(Request $request): ?array
    {
        if (! config('innoshop.visit_bot.enabled', true)) {
            return null;
        }

        // A logged-in customer is never a bot. The 2026-10 cleanup had to
        // keep `customer_id IS NOT NULL` rows for the same reason: a burst
        // IP can sit behind a NAT that also carries real shoppers.
        if (current_customer()?->id) {
            return null;
        }

        // A client that sends the session cookie back is a returning
        // visitor, however many pages it looks at. Not a burst, and not an
        // alias-host crawler either (a real e-mail clicker sends the cookie
        // back from its second page onwards).
        if ($this->hasSessionCookie($request)) {
            return null;
        }

        // Rules below only apply to cookie-less first hits.
        if (($verdict = $this->invalidHostVerdict($request)) !== null) {
            return $verdict;
        }

        if (($verdict = $this->staleUaBurstVerdict($request)) !== null) {
            return $verdict;
        }

        $ip = $request->ip();
        if (! $ip) {
            return null;
        }

        $sessionCount = $this->sessionsToday($ip);
        if ($sessionCount === null) {
            // Counting unavailable — fail open. Losing a bot hit is cheap,
            // losing a real visitor is not.
            return null;
        }

        $limit = (int) config('innoshop.visit_bot.max_sessions_per_ip_day', 8);
        if ($sessionCount < $limit) {
            return null;
        }

        if ($this->looksLikeHuman($ip)) {
            return null;
        }

        return [
            'is_bot'   => true,
            'brand'    => self::BRAND,
            'category' => BotClassifier::categoryOf(self::BRAND),
            'reason'   => self::REASON_COOKIELESS_BURST,
        ];
    }

    /**
     * Rule: entry through a vhost outside the configured allowlist.
     *
     * The alias vhosts (e-mail click tracking, legacy hosts) share the main
     * site's server block, so crawlers can walk in through them for free.
     * Real users always land on the configured hosts.
     *
     * Inactive while no allowlist is configured — the default keeps the
     * rule off on installs that never opted in.
     */
    protected function invalidHostVerdict(Request $request): ?array
    {
        $allowed = $this->allowedHosts();
        if ($allowed === []) {
            return null;
        }

        $host = strtolower($request->getHost());
        if ($host === '' || in_array($host, $allowed, true)) {
            return null;
        }

        return [
            'is_bot'   => true,
            'brand'    => self::BRAND_HOST,
            'category' => BotClassifier::categoryOf(self::BRAND_HOST),
            'reason'   => self::REASON_INVALID_HOST,
        ];
    }

    /**
     * Rule: cookie-less first hits concentrated on one stale browser major
     * version. This is the fingerprint of a UA library rotating behind a
     * residential proxy pool — each IP moves once or twice, so per-IP
     * counters stay flat while the per-version counter explodes.
     *
     * The counter lives in the cache, one bucket per (browser, major
     * version, window). Current-version UAs and empty thresholds skip the
     * rule entirely; cache failures fail open.
     */
    protected function staleUaBurstVerdict(Request $request): ?array
    {
        $threshold = (int) config('innoshop.visit_bot.ua_burst_threshold', 60);
        if ($threshold <= 0) {
            return null;
        }

        $fingerprint = $this->uaVersionFingerprint((string) $request->userAgent());
        if ($fingerprint === null) {
            return null;
        }

        [$browser, $major] = $fingerprint;

        $staleBelow = (int) config('innoshop.visit_bot.ua_burst_max_version', 147);
        if ($major >= $staleBelow) {
            return null;
        }

        $windowMinutes = max(1, (int) config('innoshop.visit_bot.ua_burst_window_minutes', 60));
        $bucket        = (int) floor(now()->timestamp / ($windowMinutes * 60));
        $key           = sprintf('inno:ua_burst:%s:%d:%d', $browser, $major, $bucket);

        try {
            Cache::add($key, 0, now()->addMinutes($windowMinutes * 2));
            $count = (int) Cache::increment($key);
        } catch (Throwable $e) {
            Log::warning('BotBehaviorClassifier: burst counter unavailable, failing open', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($count < $threshold) {
            return null;
        }

        return [
            'is_bot'   => true,
            'brand'    => self::BRAND_UA_BURST,
            'category' => BotClassifier::categoryOf(self::BRAND_UA_BURST),
            'reason'   => self::REASON_STALE_UA_BURST,
        ];
    }

    /**
     * The configured entry-host allowlist, lowercased. Accepts a comma
     * separated string (env-friendly) or an array.
     *
     * @return string[]
     */
    protected function allowedHosts(): array
    {
        $raw   = config('innoshop.visit_bot.allowed_hosts', '');
        $hosts = is_array($raw) ? $raw : preg_split('/[,\s]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);

        return collect($hosts)
            ->map(fn ($host) => strtolower(trim((string) $host)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Fingerprint a UA as [group, major version]. Chromium forks fold into
     * their Chrome/ token. Returns null when no known version token is
     * present — those UAs simply cannot be fingerprinted.
     *
     * @return array{0: string, 1: int}|null
     */
    protected function uaVersionFingerprint(string $userAgent): ?array
    {
        foreach (self::UA_VERSION_TOKENS as $token => $group) {
            $position = strpos($userAgent, $token);
            if ($position === false) {
                continue;
            }

            $digits = '';
            for ($i = $position + strlen($token), $length = strlen($userAgent); $i < $length; $i++) {
                if (! ctype_digit($userAgent[$i])) {
                    break;
                }
                $digits .= $userAgent[$i];
            }

            if ($digits !== '') {
                return [$group, (int) $digits];
            }
        }

        return null;
    }

    /**
     * Whether the request carries the session cookie.
     */
    protected function hasSessionCookie(Request $request): bool
    {
        $cookieName = config('session.cookie');

        return $cookieName ? $request->cookies->has($cookieName) : false;
    }

    /**
     * How many sessions this IP has opened today.
     *
     * Returns null when the count cannot be read, so callers can fail open.
     */
    protected function sessionsToday(string $ip): ?int
    {
        try {
            return DB::table('visits')
                ->where('ip_address', $ip)
                ->where('first_visited_at', '>=', now()->startOfDay())
                ->count();
        } catch (Throwable $e) {
            Log::warning('BotBehaviorClassifier: session count unavailable, failing open', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Whether this IP shows signs of carrying a real browser.
     *
     * A crawler that does not keep its session cookie fetches exactly one
     * page per session. A human — even one sharing an egress IP with
     * dozens of colleagues — opens several pages in a session. If any of
     * this IP's recent sessions saw more than one page, treat the IP as
     * human and leave it alone.
     */
    protected function looksLikeHuman(string $ip): bool
    {
        try {
            $sessions = DB::table('visits')
                ->where('ip_address', $ip)
                ->where('first_visited_at', '>=', now()->startOfDay())
                ->orderByDesc('id')
                ->limit((int) config('innoshop.visit_bot.session_sample_size', 20))
                ->pluck('session_id');

            if ($sessions->isEmpty()) {
                return true;
            }

            $rows = DB::table('visit_events')
                ->whereIn('session_id', $sessions)
                ->selectRaw('session_id, COUNT(DISTINCT page_url) AS urls')
                ->groupBy('session_id')
                ->get();

            return $rows->contains(fn ($row) => (int) $row->urls > 1);
        } catch (Throwable $e) {
            Log::warning('BotBehaviorClassifier: session sample unavailable, failing open', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }
}
