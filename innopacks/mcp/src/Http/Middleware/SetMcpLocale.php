<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use InnoShop\Mcp\McpAccess;
use Symfony\Component\HttpFoundation\Response;

/**
 * Negotiates the MCP welcome page locale from its own language packs only —
 * the store's frontend locale settings don't apply here. Precedence:
 * ?lang override, then session, then browser Accept-Language, then en.
 */
class SetMcpLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request, array_keys(McpAccess::locales()));

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return $next($request);
    }

    private function resolve(Request $request, array $codes): string
    {
        $lang = $request->query('lang');
        if ($lang && in_array($lang, $codes, true)) {
            return $lang;
        }

        $session = session('locale');
        if ($session && in_array($session, $codes, true)) {
            return $session;
        }

        return $this->detectBrowserLocale($request, $codes) ?? 'en';
    }

    private function detectBrowserLocale(Request $request, array $codes): ?string
    {
        $acceptLanguage = $request->header('Accept-Language', '');
        if (! $acceptLanguage) {
            return null;
        }

        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(\d+(?:\.\d+)?))?/i', $acceptLanguage, $matches, PREG_SET_ORDER);
        $browserLocales = [];
        foreach ($matches as $match) {
            $lang                  = strtolower($match[1]);
            $qual                  = isset($match[2]) ? (float) $match[2] : 1.0;
            $browserLocales[$lang] = $qual;
        }
        arsort($browserLocales);

        foreach ($browserLocales as $browserLang => $q) {
            if (in_array($browserLang, $codes, true)) {
                return $browserLang;
            }
            $primary = explode('-', $browserLang)[0];
            foreach ($codes as $code) {
                if (str_starts_with($code, $primary)) {
                    return $code;
                }
            }
        }

        return null;
    }
}
