<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Console\Commands;

use Illuminate\Console\Command;
use InnoShop\Common\Models\Admin;

class ChangeRootPassword extends Command
{
    protected $signature = 'root:password';

    protected $description = 'Change the backend root account password';

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $admin = Admin::query()->first();
        if (empty($admin)) {
            $this->info('Empty admin users, forget run `php artisan db:seed`?');

            return;
        }

        $newPassword = $this->ask("Please set new password for {$admin->email}");
        if (! $newPassword) {
            $this->info('Please type new password:');

            return;
        }

        $admin->password = bcrypt($newPassword);
        $admin->saveOrFail();
        $this->info('The password has been set successfully!');
    }
}
