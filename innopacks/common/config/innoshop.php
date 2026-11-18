<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'edition' => 'community',
    'version' => '1.0.0',
    'build'   => '20261118',
    'api_url' => env('INNOSHOP_API_URL', 'https://www.innoshop.cn'),

    // Override GeoLite2-City.mmdb path via GEOLITE2_PATH; defaults to storage/app/geolite2/GeoLite2-City.mmdb.
    // The file name matches the official download (res.innoshop.net/GeoLite2-City.mmdb);
    // installs carrying the old lower-case name are still picked up by a
    // case-insensitive fallback scan in Services\GeoLite2Service.
    'geo_lite_path' => env('GEOLITE2_PATH', storage_path('app/geolite2/GeoLite2-City.mmdb')),

    // Behavioural bot detection — see Services\BotBehaviorClassifier.
    //
    // BotClassifier only reads the User-Agent string, so any crawler that
    // sends a real browser UA is counted as a human visitor. The
    // 2026-10-25~27 wave did exactly that: ~124k fake sessions landed in
    // the visits table and inflated the panel UV by 20~50x.
    //
    // The behavioural rule closes that gap: a browser always sends the
    // session cookie back, so a client that never sends it creates a
    // brand-new session on every request. When one IP does that often in a
    // day AND every one of its sessions is a single page, it is a burst,
    // not a visitor. The single-page condition is what keeps shared egress
    // IPs (CGNAT pools, offices) safe — a human opens several pages per
    // session.
    //
    // The threshold is deliberately generous — it was calibrated against the
    // 2026-10 cleanup window, where no real browser session came close.
    // Lower it to tighten blocking, raise it to be more forgiving.
    'visit_bot' => [
        'enabled' => env('VISIT_BOT_BEHAVIOR_ENABLED', true),

        // How many sessions one IP may open per day before a cookie-less
        // request from it is treated as a bot. 8 matches the ">=8 sessions
        // per IP per day" rule used for the 2026-10 cleanup.
        'max_sessions_per_ip_day' => (int) env('VISIT_BOT_MAX_SESSIONS_PER_IP_DAY', 8),

        // How many of the IP's recent sessions are inspected when deciding
        // whether it carries a real browser. 20 is enough to see a shared
        // egress IP's human traffic without reading the whole day.
        'session_sample_size' => (int) env('VISIT_BOT_SESSION_SAMPLE_SIZE', 20),

        // Entry-host allowlist, comma separated. A cookie-less request
        // arriving through any other vhost is treated as a crawler (the
        // e-mail-tracking alias vhosts share the main server block, so
        // crawlers can walk in through them for free). Leave empty to keep
        // the rule off — installs must opt in, e.g.
        // VISIT_BOT_ALLOWED_HOSTS="www.example.com,example.com".
        'allowed_hosts' => env('VISIT_BOT_ALLOWED_HOSTS', ''),

        // Stale-UA burst rule: cookie-less first hits sharing one browser
        // major version below ua_burst_max_version are counted per window
        // in the cache; at ua_burst_threshold the version is treated as a
        // rotating crawler library. Calibrated against the 2026-10-30
        // flood, which served ~2,000 first hits per stale version per hour
        // while real traffic never exceeds a couple per stale version.
        // Set ua_burst_threshold to 0 to disable the rule.
        'ua_burst_max_version' => (int) env('VISIT_BOT_UA_STALE_MAX_VERSION', 147),

        'ua_burst_threshold' => (int) env('VISIT_BOT_UA_BURST_THRESHOLD', 60),

        'ua_burst_window_minutes' => (int) env('VISIT_BOT_UA_BURST_WINDOW_MINUTES', 60),
    ],
];
