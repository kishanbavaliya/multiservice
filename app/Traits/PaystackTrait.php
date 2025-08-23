<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SubscriptionVendor;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

trait PaystackTrait
{



    public function createPaystackPaymentReference($order)
    {

        //
        if ($order->payment == null || $order->payment->status != "pending") {

            //
            $ref = Str::random(14);
            $payLink = $this->getPaystackPaymentLink(
                $ref,
                $order->payable_total,
                $order->getCurrencyCode(),
                route('payment.callback', ["code" => $order->code, "status" => "success"]),
                $order->user->email,
            );

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

    public function createPaystackTopupReference($walletTransaction, $paymentMethod)
    {

        //
        if (empty($walletTransaction->session_id) && $walletTransaction->status == "pending") {

            //
            $payLink = $this->getPaystackPaymentLink(
                $walletTransaction->ref,
                $walletTransaction->amount,
                setting('currencyCode', 'USD'),
                route('wallet.topup.callback', ["code" => $walletTransaction->ref, "status" => "success"]),
                $walletTransaction->wallet->user->email,
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
    public function createPaystackSubscribeReference($subscription, $paymentMethod)
    {
        //
        $vendorSubscription = new SubscriptionVendor();
        $vendorSubscription->code = \Str::random(12);
        //payment status
        $vendorSubscription->status = "pending";
        $vendorSubscription->payment_method_id = $paymentMethod->id;
        $vendorSubscription->subscription_id = $subscription->id;
        $vendorSubscription->vendor_id = \Auth::user()->vendor_id;
        $vendorSubscription->save();
        //
        $payLink = $this->getPaystackPaymentLink(
            $vendorSubscription->code,
            $subscription->amount,
            setting('currencyCode', 'USD'),
            route('subscription.callback', ["code" => $vendorSubscription->code, "status" => "success"]),
            \Auth::user()->email,
        );
        $vendorSubscription->transaction_id = $payLink;
        $vendorSubscription->save();

        return $vendorSubscription->transaction_id;
    }



    protected function verifyPaystackTransaction($order)
    {

        $paymentMethod = $order->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.paystack.co/transaction/verify/' . $order->payment->ref . '')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "success") {

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

    protected function verifyPaystackTopupTransaction($walletTransaction)
    {

        $paymentMethod = $walletTransaction->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.paystack.co/transaction/verify/' . $walletTransaction->session_id . '')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "success") {

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

    protected function verifyPaystackSubscriptionTransaction($vendorSubscription)
    {

        $paymentMethod = $vendorSubscription->payment_method;
        $paystackPayment = Http::withToken($paymentMethod->secret_key)
            ->get('https://api.paystack.co/transaction/verify/' . $vendorSubscription->code . '')
            ->throw()->json();

        if ($paystackPayment['data']['status'] == "success") {

            //has order been paided for before
            if (empty($vendorSubscription) || $vendorSubscription->status == "successful") {
                throw new \Exception("Subscription Payment is invalid or has already been paid");
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
            throw new \Exception("Subscription Payment is invalid or has already been paid");
        }
    }



    protected function getPaystackPaymentLink(
        $ref,
        $amount,
        $currency,
        $callbackUrl,
        $email,
    ) {
        $paymentMethod = PaymentMethod::where("slug", "paystack")->first();
        $api =  "https://api.paystack.co/transaction/initialize";
        $amount = $amount * 100;
        $paymentResponse = Http::withToken($paymentMethod->secret_key)
            ->post($api, [
                'email' => $email,
                'amount' => $amount,
                "currency" => $currency,
                "reference" => $ref,
                "callback_url" => $callbackUrl,
            ]);

        if ($paymentResponse->successful() && $paymentResponse->json('status') == true) {
            return $paymentResponse->json('data')["authorization_url"];
        }

        throw new \Exception(__("Payment Initialized Failed"), 1);
    }
}