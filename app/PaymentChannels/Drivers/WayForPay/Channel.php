<?php

namespace App\PaymentChannels\Drivers\WayForPay;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;

class Channel implements IChannel
{
    protected $paymentChannel;

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->paymentChannel = $paymentChannel;
    }

    public function paymentRequest(Order $order)
    {
        // Логика для отправки запроса на оплату через WayForPay
        // Верните результаты запроса или выполните необходимые действия
    }

    public function verify(Request $request)
    {
        // Логика для верификации результата оплаты от WayForPay
        // Верните результаты верификации или выполните необходимые действия
    }
}
