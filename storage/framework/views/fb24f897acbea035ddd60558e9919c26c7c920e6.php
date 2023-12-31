<?php $__env->startSection('content'); ?>
    <section>
        <h2 class="section-title"><?php echo e(trans('financial.account_summary')); ?></h2>

        <?php if(!$authUser->financial_approval): ?>
            <div class="p-15 mt-30 p-lg-25 not-verified-alert font-weight-500 text-dark-blue rounded-sm shadow">
                <?php echo e(trans('panel.not_verified_alert')); ?>

                <a href="/panel/setting" class="text-decoration-underline"><?php echo e(trans('panel.this_link')); ?></a>.
            </div>
        <?php endif; ?>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/36.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5"><?php echo e($accountCharge ? addCurrencyToPrice($accountCharge) : 0); ?></strong>
                        <span class="font-16 text-gray font-weight-500"><?php echo e(trans('financial.account_charge')); ?></span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/37.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5"><?php echo e(addCurrencyToPrice($readyPayout ?? 0)); ?></strong>
                        <span class="font-16 text-gray font-weight-500"><?php echo e(trans('financial.ready_to_payout')); ?></span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="/assets/default/img/activity/38.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5"><?php echo e(addCurrencyToPrice($totalIncome ?? 0)); ?></strong>
                        <span class="font-16 text-gray font-weight-500"><?php echo e(trans('financial.total_income')); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="mt-45">
        <button type="button" <?php if(!$authUser->financial_approval): ?> disabled <?php endif; ?> class="request-payout btn btn-sm btn-primary"><?php echo e(trans('financial.request_payout')); ?></button>
    </div>

    <?php if($payouts->count() > 0): ?>
        <section class="mt-35">
            <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class="section-title"><?php echo e(trans('financial.payouts_history')); ?></h2>
            </div>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th><?php echo e(trans('financial.account')); ?></th>
                                    <th class="text-center"><?php echo e(trans('public.type')); ?></th>
                                    <th class="text-center"><?php echo e(trans('panel.amount')); ?> (<?php echo e($currency); ?>)</th>
                                    <th class="text-center"><?php echo e(trans('public.status')); ?></th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="text-left">
                                                <span class="d-block font-weight-500 text-dark-blue"><?php echo e($payout->account_name); ?></span>
                                                <span class="d-block font-12 text-gray mt-1"><?php echo e(dateTimeFormat($payout->created_at, 'j M Y | H:i')); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span><?php echo e(trans('public.manual')); ?></span>
                                        </td>
                                        <td>
                                            <span class="text-primary font-weight-bold"><?php echo e($payout->amount); ?></span>
                                        </td>
                                        <td>
                                            <?php switch($payout->status):
                                                case (\App\Models\Payout::$waiting): ?>
                                                <span class="text-warning font-weight-bold"><?php echo e(trans('public.waiting')); ?></span>
                                                <?php break; ?>;
                                                <?php case (\App\Models\Payout::$reject): ?>
                                                <span class="text-danger font-weight-bold"><?php echo e(trans('public.rejected')); ?></span>
                                                <?php break; ?>;
                                                <?php case (\App\Models\Payout::$done): ?>
                                                <span class=""><?php echo e(trans('public.done')); ?></span>
                                                <?php break; ?>;
                                            <?php endswitch; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="my-30">
                <?php echo e($payouts->appends(request()->input())->links('vendor.pagination.panel')); ?>

            </div>
        </section>
    <?php else: ?>
        <?php echo $__env->make(getTemplate() . '.includes.no-result',[
            'file_name' => 'payout.png',
            'title' => trans('financial.payout_no_result'),
            'hint' => nl2br(trans('financial.payout_no_result_hint')),
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php endif; ?>


    <div id="requestPayoutModal" class="d-none">
        <h3 class="section-title after-line font-20 text-dark-blue mb-25"><?php echo e(trans('financial.payout_confirmation')); ?></h3>
        <p class="text-gray mt-15"><?php echo e(trans('financial.payout_confirmation_hint')); ?></p>
        <form method="post" action="/panel/financial/request-payout">
            <?php echo e(csrf_field()); ?>

            <div class="row justify-content-center">
                <div class="w-75 mt-50">
                    <div class="d-flex align-items-center justify-content-between text-gray">
                        <span class="font-weight-bold"><?php echo e(trans('financial.ready_to_payout')); ?></span>
                        <span><?php echo e(addCurrencyToPrice($readyPayout ?? 0)); ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-gray mt-20">
                        <span class="font-weight-bold"><?php echo e(trans('financial.account_type')); ?></span>
                        <span><?php echo e($authUser->account_type); ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-gray mt-20">
                        <span class="font-weight-bold"><?php echo e(trans('financial.account_id')); ?></span>
                        <span><?php echo e($authUser->account_id); ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-gray mt-20">
                        <span class="font-weight-bold"><?php echo e(trans('financial.iban')); ?></span>
                        <span><?php echo e($authUser->iban); ?></span>
                    </div>
                </div>
            </div>

            <div class="mt-50 d-flex align-items-center justify-content-end">
                <button type="button" class="js-submit-payout btn btn-sm btn-primary"><?php echo e(trans('financial.request_payout')); ?></button>
                <button type="button" class="btn btn-sm btn-danger ml-10 close-swl"><?php echo e(trans('public.close')); ?></button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts_bottom'); ?>
    <script src="/assets/default/js/panel/financial/payout.min.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(getTemplate() .'.panel.layouts.panel_layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/as453212/ed-libary.online/www/resources/views/web/default/panel/financial/payout.blade.php ENDPATH**/ ?>