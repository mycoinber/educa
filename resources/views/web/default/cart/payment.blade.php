@extends(getTemplate().'.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        <span class="payment-hint font-20 text-white d-block">{{$currency . $total . ' ' .  trans('cart.for_items',['count' => $count]) }}</span>
    </section>

    <section class="container mt-45">
        <h2 class="section-title">{{ trans('financial.select_a_payment_gateway') }}</h2>

        <form action="/payments/payment-request" method="post" class=" mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="row">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" class="payment-channel">
                            <label for="{{ $paymentChannel->title }}" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $paymentChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                </p>
                            </label>
                        </div>
                    @endforeach
                @endif

                <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" class="payment-channel">
                    <label for="offline" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                            <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class="mt-5">{{ addCurrencyToPrice($userCharge) }}</span>
                    </label>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-45">
                <label for="oneTimePayment" class="font-16 font-weight-500 text-gray mr-3" style="display: inline-block;">Total Amount {{ addCurrencyToPrice($total) }}</label>
                <div class="d-flex align-items-center">
                    @if ($subscribe)
                        <div id="oneTimePaymentWrapper" style="display: none;">
                            <label>
                                <input type="checkbox" name="oneTimePayment" id="oneTimePayment" value="1" style="margin-right: 5px;">
                                <span>One time payment</span>
                            </label>
                        </div>
                    @endif
                    <button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
                </div>
            </div>

        </form>

        @if(!empty($razorpay) and $razorpay)
            <form action="/payments/verify/Razorpay" method="get">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ env('RAZORPAY_API_KEY') }}"
                        data-amount="{{ (int)($order->total_amount * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name="{{ $order->user->full_name }}"
                        data-prefill.email="{{ $order->user->email }}"
                        data-theme.color="#43d477">
                </script>
            </form>
        @endif
    </section>

@endsection

@push('scripts_bottom')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var paymentChannels = document.querySelectorAll('.payment-channel');
            var oneTimePaymentWrapper = document.getElementById('oneTimePaymentWrapper');
            var oneTimePaymentCheckbox = document.getElementById('oneTimePayment');
            var hiddenOneTimePayment = document.getElementById('hiddenOneTimePayment');
            
            for (var i = 0; i < paymentChannels.length; i++) {
                paymentChannels[i].addEventListener('change', function() {
                    var selectedPaymentChannel = document.querySelector('.payment-channel:checked');
                    console.log(selectedPaymentChannel.dataset.class)
                    if (selectedPaymentChannel && selectedPaymentChannel.dataset.class === 'Wayforpay') {
                        oneTimePaymentWrapper.style.display = 'block';
                    } else {
                        oneTimePaymentWrapper.style.display = 'none';
                    }
                });
            }
            
            oneTimePaymentCheckbox.addEventListener('change', function() {
                hiddenOneTimePayment.value = oneTimePaymentCheckbox.checked ? '1' : '';
            });
        });
    </script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
