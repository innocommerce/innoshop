<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * InstallerTest - Placeholder for installer tests.
 *
 * Note: HTTP tests for the installer are in innopacks/install/tests/
 * This test file exists for compatibility but actual installer testing
 * should be done via the Install module's test suite which properly
 * handles the pre-installation state (no database tables).
 *
 * @see \InnoShop\Install\Tests\InstallerTest
 */
class InstallerTest extends TestCase
{
    /**
     * Test that the installer test suite exists.
     * Actual HTTP testing of /install/ route requires special handling
     * because the route may be accessed before database tables exist.
     */
    public function test_installer_test_suite_exists(): void
    {
        $this->assertTrue(
            class_exists(\InnoShop\Install\Tests\InstallerTest::class),
            'Install module test suite should exist'
        );
    }
}
