<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Notification;

/**
 * Marker for queue jobs whose failures must not push a notification on their
 * own. Implement it when failures are already surfaced elsewhere (e.g. a
 * per-item ledger visible and retryable in the admin panel) — otherwise a
 * bulk job would send one channel message per failed unit.
 *
 * The notification layer stays generic: it only checks for this interface and
 * never references concrete job classes from plugins.
 */
interface SuppressesFailureNotification {}
