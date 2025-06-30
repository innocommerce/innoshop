<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;

class FileSecurityValidator
{
    /**
     * List of dangerous file extensions
     */
    private const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'php7', 'php8',
        'asp', 'aspx', 'jsp', 'pl', 'py', 'rb', 'sh', 'cgi',
        'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar',
        'htaccess', 'htpasswd',
    ];

    /**
     * File extensions that may contain malicious code
     */
    private const POTENTIALLY_DANGEROUS_EXTENSIONS = [
        'svg', 'html', 'htm', 'xml',
    ];

    /**
     * Validate file extension security
     *
     * @param  string  $fileName  File name
     * @param  bool  $allowSvg  Whether to allow SVG files (requires additional processing)
     * @throws Exception
     */
    public static function validateFileExtension(string $fileName, bool $allowSvg = false): void
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check absolutely dangerous extensions
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            throw new Exception("Dangerous file extension '{$extension}' is not allowed");
        }

        // Check potentially dangerous extensions (like SVG)
        if (! $allowSvg && in_array($extension, self::POTENTIALLY_DANGEROUS_EXTENSIONS)) {
            throw new Exception("Potentially dangerous file extension '{$extension}' is not allowed. Use allowSvg=true if you want to enable SVG with proper sanitization.");
        }
    }

    /**
     * Validate and sanitize SVG file content (remove scripts and event handlers)
     *
     * @param  string  $svgContent  SVG file content
     * @return string Sanitized SVG content
     * @throws Exception
     */
    public static function sanitizeSvgContent(string $svgContent): string
    {
        // Remove script tags
        $svgContent = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $svgContent);

        // Remove event handler attributes
        $dangerousAttributes = [
            'onload', 'onerror', 'onmouseover', 'onmouseout', 'onclick', 'onmousemove',
            'onmousedown', 'onmouseup', 'onfocus', 'onblur', 'onkeydown', 'onkeyup',
            'onsubmit', 'onreset', 'onchange', 'onselect', 'javascript:',
        ];

        foreach ($dangerousAttributes as $attr) {
            $svgContent = preg_replace('/'.$attr.'\s*=\s*["\'][^"\']*["\']/i', '', $svgContent);
        }

        // Remove external resource references (prevent external script loading)
        $svgContent = preg_replace('/href\s*=\s*["\'](?!#)[^"\']*["\']/i', '', $svgContent);

        // Check if it's still valid SVG
        if (! preg_match('/<svg\b/i', $svgContent)) {
            throw new Exception('Invalid SVG content after sanitization');
        }

        return $svgContent;
    }

    /**
     * Check if file MIME type is safe
     *
     * @param  string  $mimeType  MIME type
     * @throws Exception
     */
    public static function validateMimeType(string $mimeType): void
    {
        $dangerousMimeTypes = [
            'application/x-php',
            'application/x-httpd-php',
            'text/x-php',
            'application/php',
            'application/x-sh',
            'application/x-csh',
            'text/x-shellscript',
        ];

        if (in_array(strtolower($mimeType), $dangerousMimeTypes)) {
            throw new Exception("Dangerous MIME type '{$mimeType}' is not allowed");
        }
    }

    /**
     * Check if file name contains path traversal attacks
     *
     * @param  string  $fileName  File name
     * @throws Exception
     */
    public static function validateFileName(string $fileName): void
    {
        // Check path traversal
        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            throw new Exception('Invalid file name: path traversal detected');
        }

        // Check file name length
        if (strlen($fileName) > 255) {
            throw new Exception('File name too long');
        }

        // Check empty file name
        if (empty(trim($fileName))) {
            throw new Exception('File name cannot be empty');
        }
    }

    /**
     * Comprehensive file security validation
     *
     * @param  string  $fileName  File name
     * @param  string|null  $mimeType  MIME type
     * @param  bool  $allowSvg  Whether to allow SVG
     * @throws Exception
     */
    public static function validateFile(string $fileName, ?string $mimeType = null, bool $allowSvg = false): void
    {
        self::validateFileName($fileName);
        self::validateFileExtension($fileName, $allowSvg);

        if ($mimeType) {
            self::validateMimeType($mimeType);
        }
    }

    /**
     * Get list of safe image file extensions
     */
    public static function getSafeImageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    }

    /**
     * Get list of safe document file extensions
     */
    public static function getSafeDocumentExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
    }

    /**
     * Validate and sanitize directory path to prevent path traversal attacks
     *
     * @param  string  $path  Directory path to validate
     * @return string Sanitized safe path
     * @throws Exception If path contains dangerous patterns
     */
    public static function validateDirectoryPath(string $path): string
    {
        // URL decode the path first
        $decodedPath = urldecode($path);

        // Check for path traversal attacks BEFORE any normalization
        if (str_contains($decodedPath, '..')) {
            throw new Exception('Path traversal attack detected');
        }

        // Check for null bytes and other dangerous characters
        if (str_contains($decodedPath, "\0") || str_contains($decodedPath, "\x00")) {
            throw new Exception('Null byte attack detected');
        }

        // Normalize path separators
        $normalizedPath = str_replace('\\', '/', $decodedPath);

        // Remove multiple consecutive slashes
        $normalizedPath = preg_replace('#/+#', '/', $normalizedPath);

        // Handle root directory case
        if ($normalizedPath === '' || $normalizedPath === '/') {
            return '/';
        }

        // Handle relative paths - convert to absolute paths with leading slash
        if (! str_starts_with($normalizedPath, '/')) {
            $normalizedPath = '/'.$normalizedPath;
        }

        // Remove trailing slash (except for root)
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        // Final check after normalization to prevent any remaining traversal attempts
        if (str_contains($normalizedPath, '..')) {
            throw new Exception('Path traversal attack detected after normalization');
        }

        return $normalizedPath;
    }
}
