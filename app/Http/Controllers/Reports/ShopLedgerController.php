<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Company;
use App\Models\Delivery;
use App\Models\DeliveryPayment;
use App\Models\ShopReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopLedgerController extends Controller
{
    public function index(Request $request)
    {
        $company = $this->getCompany();

        $shops = Shop::where('company_code', $company->company_code ?? 'C01')
            ->orderBy('name')
            ->get();

        $data = [
            'shops' => $shops,
            'company' => $company,
        ];

        // If shop is selected, fetch transactions
        if ($request->shop_id) {
            $shop = Shop::where('id', $request->shop_id)
                ->where('company_code', $company->company_code ?? 'C01')
                ->first();
            
            if ($shop) {
                $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
                $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : Carbon::now()->endOfDay();

                // Get opening balance - deliveries minus payments minus shop returns before fromDate
                $deliveriesBeforeDate = Delivery::where('shop_id', $shop->id)
                    ->where('delivery_date', '<', $fromDate->format('Y-m-d'))
                    ->get()
                    ->sum('total_amount');
                
                $paymentsBeforeDate = DeliveryPayment::whereHas('delivery', function ($q) use ($shop) {
                        $q->where('shop_id', $shop->id);
                    })
                    ->where('payment_date', '<', $fromDate->format('Y-m-d'))
                    ->where('status', '!=', 'bounced')
                    ->where('amount', '>', 0)
                    ->sum('amount');

                // For shop returns, compute the total amount from items if possible
                $allShopReturns = ShopReturn::with('items')
                    ->where('shop_id', $shop->id)
                    ->get();

                $shopReturnsBeforeDate = $allShopReturns->filter(function ($return) use ($fromDate) {
                        return $return->status !== 'cancelled' && Carbon::parse($return->return_date)->startOfDay()->lt($fromDate);
                    })
                    ->sum(function ($return) {
                        return $return->items->sum(function ($item) {
                            return $item->quantity * $item->unit_price;
                        });
                    });
                
                $openingBalance = $deliveriesBeforeDate - $paymentsBeforeDate - $shopReturnsBeforeDate;

                // Get deliveries for the period
                $deliveries = Delivery::with(['items', 'payments'])
                    ->where('shop_id', $shop->id)
                    ->whereBetween('delivery_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                    ->get()
                    ->map(function($delivery) {
                        return [
                            'date' => $delivery->delivery_date,
                            'type' => 'delivery',
                            'sort_key' => Carbon::parse($delivery->delivery_date)->format('Y-m-d H:i:s') . '_1',
                            'description' => 'Delivery - #' . $delivery->delivery_number,
                            'invoice_no' => $delivery->delivery_number,
                            'debit' => $delivery->total_amount,
                            'credit' => 0,
                        ];
                    });

                // Get payment transactions for the period
                $payments = DeliveryPayment::with('delivery')
                    ->whereHas('delivery', function ($q) use ($shop) {
                        $q->where('shop_id', $shop->id);
                    })
                    ->whereBetween('payment_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                    ->where('status', '!=', 'bounced')
                    ->where('amount', '>', 0)
                    ->get()
                    ->map(function($payment) {
                        $description = 'Payment';
                        if ($payment->method) {
                            $description .= ' - ' . ucfirst($payment->method);
                        }
                        if ($payment->cheque_no) {
                            $description .= ' (Cheque: ' . $payment->cheque_no . ')';
                        }

                        return [
                            'date' => $payment->payment_date,
                            'type' => 'payment',
                            'sort_key' => Carbon::parse($payment->payment_date)->format('Y-m-d H:i:s') . '_2',
                            'description' => $description,
                            'invoice_no' => $payment->delivery ? $payment->delivery->delivery_number : '-',
                            'debit' => 0,
                            'credit' => $payment->amount,
                        ];
                    });

                // Get shop return transactions for the period
                $shopReturns = $allShopReturns->filter(function ($ret) use ($fromDate, $toDate) {
                        if ($ret->status === 'cancelled') return false;
                        $retDate = Carbon::parse($ret->return_date)->startOfDay();
                        return $retDate->gte($fromDate) && $retDate->lte($toDate);
                    })
                    ->map(function ($ret) {
                        $total = $ret->items->sum(function ($item) {
                            return $item->quantity * $item->unit_price;
                        });

                        return [
                            'date' => $ret->return_date,
                            'type' => 'return',
                            'sort_key' => Carbon::parse($ret->return_date)->format('Y-m-d H:i:s') . '_3',
                            'description' => 'Shop Return - #SR-' . str_pad($ret->id, 6, '0', STR_PAD_LEFT) . ($ret->notes ? ' (' . $ret->notes . ')' : ''),
                            'invoice_no' => $ret->delivery ? $ret->delivery->delivery_number : '-',
                            'debit' => 0,
                            'credit' => $total,
                        ];
                    });
                
                // Merge and sort all transactions
                $allTransactions = $deliveries->concat($payments)->concat($shopReturns)->sortBy('sort_key')->values();

                $balance = $openingBalance;
                $ledgerTransactions = [];

                foreach ($allTransactions as $index => $trn) {
                    $balance += $trn['debit'] - $trn['credit'];

                    $ledgerTransactions[] = [
                        'id' => $index + 1,
                        'date' => $trn['date'],
                        'description' => $trn['description'],
                        'invoice_no' => $trn['invoice_no'],
                        'debit' => $trn['debit'],
                        'credit' => $trn['credit'],
                        'balance' => $balance,
                    ];
                }

                $data['selectedShop'] = $shop;
                $data['transactions'] = $ledgerTransactions;
                $data['fromDate'] = $fromDate->format('Y-m-d');
                $data['toDate'] = $toDate->format('Y-m-d');
                $data['openingBalance'] = $openingBalance;
                $data['closingBalance'] = $balance;
            }
        }

        return Inertia::render('Reports/ShopLedger', $data);
    }

    private function getCompany()
    {
        $company = null;
        if (auth('company')->check()) {
            $company = auth('company')->user();
        } else {
            $user = auth('web')->user();
            $company = $user ? $user->company : Company::first();
        }

        if (!$company) {
            $company = (object) [
                'company_code' => 'C01',
                'name' => 'VISION COPIER',
                'primary_color' => '#00aeef',
                'secondary_color' => '#737578'
            ];
        }

        return $company;
    }
}
