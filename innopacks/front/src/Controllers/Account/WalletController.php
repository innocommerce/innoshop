<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\Customer\TransactionRepo;
use InnoShop\Common\Repositories\Customer\WithdrawalRepo;
use InnoShop\Front\Controllers\BaseController;

class WalletController extends BaseController
{
    /**
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        $customer        = current_customer();
        $transactionRepo = new TransactionRepo;
        $withdrawalRepo  = new WithdrawalRepo;

        // Sync and get correct balance information
        $customer->syncBalance();
        $balance          = $customer->balance;
        $freezeBalance    = $withdrawalRepo->getFrozenAmount($customer->id);
        $availableBalance = $balance - $freezeBalance;

        // Get recent transaction records
        $recentTransactions = $transactionRepo->builder(['customer_id' => $customer->id])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Get withdrawal statistics
        $withdrawalStats = [
            'pending'  => $withdrawalRepo->builder(['customer_id' => $customer->id, 'status' => 'pending'])->count(),
            'approved' => $withdrawalRepo->builder(['customer_id' => $customer->id, 'status' => 'approved'])->count(),
            'rejected' => $withdrawalRepo->builder(['customer_id' => $customer->id, 'status' => 'rejected'])->count(),
            'paid'     => $withdrawalRepo->builder(['customer_id' => $customer->id, 'status' => 'paid'])->count(),
        ];

        // Check if there are pending withdrawal requests
        $hasPendingWithdrawal = $withdrawalRepo->hasPendingWithdrawal($customer->id);

        $data = [
            'customer'               => $customer,
            'balance'                => $balance,
            'freeze_balance'         => $freezeBalance,
            'available_balance'      => $availableBalance,
            'recent_transactions'    => $recentTransactions,
            'withdrawal_stats'       => $withdrawalStats,
            'has_pending_withdrawal' => $hasPendingWithdrawal,
        ];

        return view('account.wallet_index', $data);
    }
}
