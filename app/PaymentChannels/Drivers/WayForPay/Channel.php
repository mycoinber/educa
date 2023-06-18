<?php

namespace App\PaymentChannels\Drivers\WayForPay;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use WayForPay\SDK\Collection\ProductCollection;
use WayForPay\SDK\Credential\AccountSecretCredential;
use WayForPay\SDK\Domain\Client;
use WayForPay\SDK\Domain\Product;
use WayForPay\SDK\Wizard\PurchaseWizard;
use WayForPay\SDK\Domain\Response\Reason;
use WayForPay\SDK\Handler\ServiceUrlHandler;


class Channel implements IChannel
{
    protected $credential;

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {   
        $this->account = env('ACCOUNT');
        $this->secret = env('SECRET');
        // $this->credential = new AccountSecretTestCredential();
        $this->credential = new AccountSecretCredential($this->account, $this->secret);
    }

    public function paymentRequest(Order $order)
    {
        $user = auth()->user();
        // die(json_encode($user));
        // if ($orderItem->subscribe_id)
        // {
        //     die('test');
        // }

        $form = PurchaseWizard::get($this->credential)
            ->setOrderReference(sha1(microtime(true)))
            ->setAmount($order->total_amount)
            ->setCurrency('USD')
            ->setOrderDate(new \DateTime())
            ->setMerchantDomainName('https://ed-libary.online')
            ->setClient(new Client(
                $user->mobile ? $user->mobile : "",
                $user->full_name ? $user->full_name : "",
                $user->mobile ? $user->mobile : "",
                'USA'
            ))
            ->setProducts(new ProductCollection(array(
                new Product('Course', 0.01, 1)
            )))
            ->setReturnUrl($this->makeCallbackUrl($order))
            // ->setServiceUrl($this->makeCallbackUrl($order))
            ->getForm()
            ->getAsString();  

        return('<html><body><div style="display:none;">' . $form . '</div></body><script>
        window.addEventListener("load", function() {
          var form = document.querySelector("form");
          form.submit();
        });
      </script>
      </html>');
    }

    private function makeCallbackUrl($order)
    {   
        // $callbackUrl = url("/payments/postverify/Wayforpay?order_id=$order->id");
        $callbackUrl = route('payment_verify_post', [
            'gateway' => 'Wayforpay',
            'order_id' => $order->id
        ]);

        // $logPath = storage_path('logs/laravel.log');
        // $logData = $callbackUrl . "\n";
        // file_put_contents($logPath, $logData, FILE_APPEND);

        return $callbackUrl;
    }

    
    public function verify(Request $request)
    {   
        $data = $request->all();

        error_log(print_r($data, true), 3, 'error.log');

        $order_id = $data['order_id'];

        // Получите заказ по идентификатору
        $order = Order::find($order_id);

        // Обработка обратного вызова WayForPay
        $handler = new ServiceUrlHandler($this->credential);
        $response = $handler->parseRequestFromGlobals();
        $cc = $response->getReason();
        $logPath = storage_path('logs/laravel.log');
        $logData =  "\n" . print_r($cc, true) . "\n";
        file_put_contents($logPath, $logData, FILE_APPEND);

        if ($response->getReason()->isOK()) {
            // Обновите статус заказа как оплачиваемый
            $order->update([
                'status' => Order::$paying
            ]);
    
            // Верните обновленный заказ
            return $order;
        } else {
            // Обновите статус заказа как неудачный платеж
            $order->update([
                'status' => Order::$fail
            ]);
            return $order;
        }
    }

}
