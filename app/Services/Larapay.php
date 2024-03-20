<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Str;

class Larapay implements PaymentService
{
    const PAYMENT_URL = 'https://payhere.com'; //mocking key, should comes from config set in env file

    const MERCHANT_ID = 'merchant id'; //mocking key, should comes from config set in env file

    const KEY = 'key'; //mocking key, should comes from config set in env file

    public function __construct(
        public $referenceId,
        public $id,
        public $amount,
        public $paidAt,
        public $status,
        public $sign,
    ) {
    }

    public function requestPaymentUrl(): string
    {
        //prepare request $data
        $data = [
            'merchant_id' => static::MERCHANT_ID,
            'amount' => $this->amount,
            'merchant_order_id' => $this->referenceId,
            'notify_url' => route('payment_notification_url', ['payment' => $this->referenceId]),
            //and other data needed for request
        ];

        $sign = $this->getSign($data);

        $data['sign'] = $sign;
        Log::info('Larapay payment requested payment url from "'.static::PAYMENT_URL.'" with the following data');
        Log::info($data);
        // $response = Http::post(static::PAYMENT_URL, $data)->body();
        // the respons should contain a payment url so user can pay securely enforced by the payment service
        $response = ['pay_url' => 'https://payhere.larapay.com?data=data'];
        Log::info($response);

        return $response['pay_url'];
    }

    public static function createForRequest($referenceId, $amount): self
    {
        return new self($referenceId, null, $amount, null, null, null);
    }

    public function getSign(array $data): string
    {
        ksort($data);

        $sign = md5(http_build_query($data).'&key='.static::KEY);

        return $sign;
    }

    public static function mockResponse(Payment $payment): string
    {
        $data = [
            'reference_id' => $payment->id,
            'id' => time().Str::random(6),
            'amount' => $payment->amount,
            'paid_at' => time() * 1000,
            'status' => 1,
        ];
        ksort($data);
        $sign = md5(http_build_query($data).'&key='.static::KEY);
        $data['sign'] = $sign;

        return json_encode($data);
    }

    public function getStatus(): PaymentStatus
    {
        switch ($this->status) {
            case 1:
                return PaymentStatus::Completed;
            case 2:
                return PaymentStatus::Processing;
            case 3:
                return PaymentStatus::Canceled;
            default:
                return PaymentStatus::Pending;
        }
    }

    public function verifySign(): bool
    {
        $data = [
            'reference_id' => $this->referenceId,
            'id' => $this->id,
            'amount' => $this->amount,
            'paid_at' => $this->paidAt,
            'status' => $this->status,
        ];

        return $this->getSign($data) == $this->sign;
    }
}
