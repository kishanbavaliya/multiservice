<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Models\SubscriptionVendor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

trait FlutterwaveTrait
{


    public function createFlutterwavePaymentReference($order)
    {

        //
        if ($order->payment == null || $order->payment->status != "pending") {

            //
            $ref = Str::random(14);
            $payLink = $this->getPaymentLink(
                $ref,
                $order->payable_total,
                $order->getCurrencyCode(),
                route('payment.callback', ["code" => $order->code, "status" => "success"]),
                __("Order payment"),
                //user info
                $order->user->name,
                $order->user->email,
                $order->user->phone,
            );
            //
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->session_id = $payLink;
            $payment->ref = $ref;
            $payment->amount = $order->payable_total;
            $payment->save();

            return $payment->session_id;
        } else {
            return $order->payment->session_id;
        }
    }

    public function createFlutterwaveTopupReference($walletTransaction, $paymentMethod)
    {

        //
        if (empty($walletTransaction->session_id) && $walletTransaction->status == "pending") {

            //
            $payLink = $this->getPaymentLink(
                $walletTransaction->ref,
                $walletTransaction->amount,
                setting('currencyCode', 'USD'),
                route('wallet.topup.callback', ["code" => $walletTransaction->ref, "status" => "success"]),
                __("Wallet topup payment"),
                //user info
                $walletTransaction->wallet->user->name,
                $walletTransaction->wallet->user->email,
                $walletTransaction->wallet->user->phone,
            );
            //
            $walletTransaction->session_id = $payLink;
            $walletTransaction->payment_method_id = $paymentMethod->id;
            $walletTransaction->save();

            return $walletTransaction->session_id;
        } else {
            return $walletTransaction->session_id;
        }
    }

    public function createFlutterwaveSubscribeReference($subscription, $paymentMethod)
    {

        $vendorSubscription = new SubscriptionVendor();
        $vendorSubscription->code = \Str::random(12);
        //payment status
        $vendorSubscription->status = "pending";
        $vendorSubscription->payment_method_id = $paymentMethod->id;
        $vendorSubscription->subscription_id = $subscription->id;
        $vendorSubscription->vendor_id = \Auth::user()->vendor_id;
        $vendorSubscription->save();
        //
        $payLink = $this->getPaymentLink(
            $vendorSubscription->code,
            $subscription->amount,
            setting('currencyCode', 'USD'),
            route('subscription.callback', ["code" => $vendorSubscription->code, "status" => "success"]),
            __("Subscription payment"),
            //user info
            \Auth::user()->name,
            \Auth::user()->email,
            \Auth::user()->phone,
        );
        $vendorSubscription->transaction_id = $payLink;
        $vendorSubscription->save();

        return $vendorSubscription->transaction_id;
    }



    protected function getPaymentLink(
        $ref,
        $amount,
        $currecnyCode,
        $returnUrl,
        $title,
        //
        $name = null,
        $email = null,
        $phone = null,
    ) {
        $paymentMethod = PaymentMethod::where("slug", "flutterwave")->first();
        $paymentResponse = Http::withToken($paymentMethod->secret_key)
            ->post(
                'https://api.flutterwave.com/v3/payments',
                [
                    "tx_ref" => $ref,
                    "amount" => $amount,
                    "currency" => $currecnyCode,
                    "redirect_url" => $returnUrl,
                    "customer" => [
                        "email" => $email,
                        "name" => $name,
                        "phonenumber" => $phone,
                    ],
                    "customizations" => [
                        "title" => $title ?? __("Payment"),
                    ],
                ],
            );

        if ($paymentResponse->successful() && $paymentResponse->json('status') == "success") {
            return $paymentResponse->json('data')["link"];
        }

        throw new \Exception(__("Payment Initialized Failed"), 1);
    }

    //
    protected function verifyFlutterwaveTransaction($order, $transactionId)
    {

        $paymentMethod = $order->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "successful") {

            $payment = Payment::where('session_id', $order->payment->session_id)->first();

            //has order been paided for before
            if (empty($order)) {
                throw new \Exception("Order is invalid");
            } else if (!$order->isDirty('payment_status') && $order->payment_status  == "successful") {
                //throw new \Exception("Order is has already been paid");
                return;
            }


            try {

                DB::beginTransaction();
                $payment->status = "successful";
                $payment->save();

                $order->payment_status = "successful";
                $order->save();
                DB::commit();
                return;
            } catch (\Exception $ex) {
                throw $ex;
            }
        } else {
            throw new \Exception("Order is invalid or has already been paid");
        }
    }


    protected function verifyFlutterwaveTopupTransaction($walletTransaction, $transactionId)
    {

        $paymentMethod = $walletTransaction->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "successful") {

            //has order been paided for before
            if (empty($walletTransaction)) {
                throw new \Exception("Wallet Topup is invalid");
            } else if (!$walletTransaction->isDirty('status') && $walletTransaction->status == "successful") {
                // throw new \Exception("Wallet Topup is has already been paid");
                return;
            }


            try {

                DB::beginTransaction();
                $walletTransaction->status = "successful";
                $walletTransaction->save();

                //
                $wallet = Wallet::find($walletTransaction->wallet->id);
                $wallet->balance += $walletTransaction->amount;
                $wallet->save();
                DB::commit();
                return;
            } catch (\Exception $ex) {
                throw $ex;
            }
        } else {
            throw new \Exception("Wallet Topup is invalid or has already been paid");
        }
    }

    protected function verifyFlutterwaveSubscriptionTransaction($vendorSubscription, $transactionId)
    {

        $paymentMethod = $vendorSubscription->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "successful") {

            //has order been paided for before
            if (empty($vendorSubscription) || $vendorSubscription->status == "successful") {
                throw new \Exception("Subscription payment is invalid or has already been paid");
            }


            try {

                DB::beginTransaction();
                $vendorSubscription->status = "successful";
                $vendorSubscription->save();
                DB::commit();
                return;
            } catch (\Exception $ex) {
                throw $ex;
            }
        } else {
            throw new \Exception("Subscription payment is invalid or has already been paid");
        }
    }
}