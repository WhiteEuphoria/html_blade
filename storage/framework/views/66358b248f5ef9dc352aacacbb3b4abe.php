<?php
    $formatMoney = fn ($amount, $currency) => $amount === null
        ? '—'
        : number_format((float) $amount, 2, '.', ' ') . ' ' . ($currency ?: (config('currencies.default') ?? 'EUR'));

    $verificationColor = fn (?string $status) => match (strtolower((string) $status)) {
        'approved', 'active', 'verified', 'verificated' => 'text-emerald-600 bg-emerald-100',
        'rejected', 'blocked' => 'text-rose-600 bg-rose-100',
        default => 'text-amber-600 bg-amber-100',
    };

    $statusBadge = static function (?string $status): string {
        $status = (string) $status;
        return match (strtolower($status)) {
            'active' => 'bg-emerald-100 text-emerald-700',
            'hold', 'on hold' => 'bg-amber-100 text-amber-700',
            'blocked', 'blocked by client', 'blocked by admin' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-600',
        };
    };

    $txStatus = static function (?string $status): array {
        return match (strtolower((string) $status)) {
            'success', 'completed' => ['✅', 'text-emerald-600'],
            'failed', 'declined', 'rejected' => ['⛔', 'text-rose-600'],
            default => ['•', 'text-slate-500'],
        };
    };
?>

<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 text-slate-500">
                    <img src="<?php echo e(asset('personal-acc/img/logo.svg')); ?>" alt="Brand" class="h-10 hidden sm:block">
                    <div>
                        <div class="text-xs uppercase tracking-wider">Admin panel</div>
                        <h1 class="text-2xl font-semibold text-slate-800">Unified client dashboard</h1>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-sm font-medium text-slate-600" for="dashboard-client">Client</label>
                    <!--[if BLOCK]><![endif]--><?php if($clientOptions): ?>
                        <select
                            wire:model.live="selectedUserId"
                            id="dashboard-client"
                            class="fi-input block rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/50"
                        >
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $clientOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                    <?php else: ?>
                        <div class="text-sm text-slate-500">No clients yet</div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php if($selectedUser): ?>
                        <a
                            href="<?php echo e(route('filament.admin.resources.users.edit', $selectedUser)); ?>"
                            class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                        >
                            Manage profile
                        </a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <?php
                $supportAction = $this->getAction('support');
                $withdrawalAction = $this->getAction('withdrawal');
            ?>

            <div class="flex flex-wrap items-center gap-3">
                <!--[if BLOCK]><![endif]--><?php if($supportAction): ?>
                    <?php if (isset($component)) { $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-actions::components.action','data' => ['action' => $supportAction,'class' => 'fi-btn fi-btn--light']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-actions::action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supportAction),'class' => 'fi-btn fi-btn--light']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $attributes = $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $component = $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($withdrawalAction): ?>
                    <?php if (isset($component)) { $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-actions::components.action','data' => ['action' => $withdrawalAction,'class' => 'fi-btn']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-actions::action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($withdrawalAction),'class' => 'fi-btn']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $attributes = $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $component = $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!--[if BLOCK]><![endif]--><?php if(!$selectedUser): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                <div class="text-lg font-semibold text-slate-700">Select a client to see their dashboard</div>
                <p class="mt-2 text-sm">Create a client account first or choose one from the list above.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <div class="space-y-6">
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-2">
                                <div class="text-sm font-medium text-slate-400">Welcome</div>
                                <div class="text-2xl font-semibold text-slate-900"><?php echo e($selectedUser->name); ?></div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-600">
                                    <span><?php echo e($selectedUser->email); ?></span>
                                    <span class="hidden sm:inline">•</span>
                                    <span>Joined <?php echo e(optional($selectedUser->created_at)->format('d.m.Y')); ?></span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold <?php echo e($verificationColor($selectedUser->verification_status)); ?>">
                                    <?php echo e(ucfirst($selectedUser->verification_status ?? 'pending')); ?>

                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-2 text-sm font-medium text-slate-500">
                                    Balance
                                    <img src="<?php echo e(asset('personal-acc/img/icons/wallet.svg')); ?>" alt="Wallet" class="h-5">
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-slate-900">
                                    <?php echo e($formatMoney($selectedUser->main_balance, $selectedUser->currency)); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Accounts</h2>
                            <a
                                href="<?php echo e(route('filament.admin.resources.accounts.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]])); ?>"
                                class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                            >
                                View all
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Company / Broker</th>
                                        <th class="px-6 py-3">Bank / Account no.</th>
                                        <th class="px-6 py-3">Owner</th>
                                        <th class="px-6 py-3">Type</th>
                                        <th class="px-6 py-3">Expiry date</th>
                                        <th class="px-6 py-3">Balance</th>
                                        <th class="px-6 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800"><?php echo e($account->organization ?? '—'); ?></div>
                                                <div class="text-xs text-slate-500"><?php echo e($account->broker_initials ?? '—'); ?></div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800"><?php echo e($account->bank ?? '—'); ?></div>
                                                <div class="text-xs text-slate-500"><?php echo e($account->number); ?></div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800"><?php echo e($selectedUser->name); ?></div>
                                                <div class="text-xs text-slate-500"><?php echo e($account->client_initials ?? 'Client'); ?></div>
                                            </td>
                                            <td class="px-6 py-4 align-top text-slate-700"><?php echo e($account->type); ?></td>
                                            <td class="px-6 py-4 align-top text-slate-700"><?php echo e(optional($account->term)->format('d/m/y') ?? '—'); ?></td>
                                            <td class="px-6 py-4 align-top font-semibold text-slate-900">
                                                <?php echo e($formatMoney($account->balance, $account->currency ?? $selectedUser->currency)); ?>

                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusBadge($account->status)); ?>">
                                                    <?php echo e(strtoupper($account->status ?? '—')); ?>

                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">No accounts yet</td>
                                        </tr>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold text-slate-900">Documents</h3>
                                <a
                                    href="<?php echo e(route('filament.admin.resources.documents.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]])); ?>"
                                    class="text-xs font-semibold text-primary-600"
                                >View</a>
                            </div>
                            <ul class="mt-4 space-y-3 text-sm">
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <li class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800"><?php echo e($document->original_name); ?></span>
                                            <span class="text-xs text-slate-500"><?php echo e(optional($document->created_at)->format('d.m.Y H:i')); ?></span>
                                        </div>
                                        <span class="text-xs font-semibold <?php echo e($statusBadge($document->status)); ?> rounded-full px-3 py-1">
                                            <?php echo e(strtoupper($document->status ?? 'PENDING')); ?>

                                        </span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No documents uploaded</li>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </ul>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-slate-900">Fraud claims</h3>
                                    <a
                                        href="<?php echo e(route('filament.admin.resources.fraud-claims.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]])); ?>"
                                        class="text-xs font-semibold text-primary-600"
                                    >View</a>
                                </div>
                                <ul class="mt-4 space-y-3 text-sm">
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $fraudClaims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="text-sm font-medium text-slate-800"><?php echo e(\Illuminate\Support\Str::limit($claim->details, 80)); ?></div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                <span><?php echo e(optional($claim->created_at)->format('d.m.Y H:i')); ?></span>
                                                <span class="inline-flex rounded-full px-3 py-1 font-semibold <?php echo e($statusBadge($claim->status)); ?>">
                                                    <?php echo e(strtoupper($claim->status ?? 'В РАССМОТРЕНИИ')); ?>

                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No claims</li>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </ul>
                            </div>

                            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-slate-900">Withdrawals</h3>
                                    <a
                                        href="<?php echo e(route('filament.admin.resources.withdrawals.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]])); ?>"
                                        class="text-xs font-semibold text-primary-600"
                                    >View</a>
                                </div>
                                <ul class="mt-4 space-y-3 text-sm">
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <li class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-slate-800"><?php echo e($formatMoney($withdrawal->amount, optional($withdrawal->user)->currency)); ?></span>
                                                <span class="text-xs font-semibold <?php echo e($statusBadge($withdrawal->status)); ?> rounded-full px-3 py-1">
                                                    <?php echo e(strtoupper($withdrawal->status)); ?>

                                                </span>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                <span><?php echo e(optional($withdrawal->created_at)->format('d.m.Y H:i')); ?></span>
                                                <span><?php echo e(ucfirst($withdrawal->method ?? '—')); ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No withdrawals</li>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Transactions</h2>
                            <a
                                href="<?php echo e(route('filament.admin.resources.transactions.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]])); ?>"
                                class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                            >
                                View all
                            </a>
                        </div>
                        <div class="space-y-3 p-4">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php ([$icon, $iconColor] = $txStatus($transaction->status)); ?>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl <?php echo e($iconColor); ?>"><?php echo e($icon); ?></span>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-800"><?php echo e(ucfirst($transaction->type ?? 'Transaction')); ?></div>
                                                <div class="text-xs text-slate-500"><?php echo e($transaction->from); ?> → <?php echo e($transaction->to); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-slate-400"><?php echo e(optional($transaction->created_at)->format('d.m.Y H:i')); ?></div>
                                            <div class="mt-1 text-base font-semibold text-slate-900">
                                                <?php echo e($formatMoney($transaction->amount, $transaction->currency ?? $selectedUser->currency)); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No transactions yet</div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <?php ($reportAction = $this->getAction('reportViolation')); ?>
                    <!--[if BLOCK]><![endif]--><?php if($reportAction): ?>
                        <div class="rounded-3xl bg-rose-600 p-6 text-white shadow-lg">
                            <div class="text-lg font-semibold">Need to report a violation?</div>
                            <p class="mt-2 text-sm text-rose-50/90">
                                Log a fraud claim and the team will follow up with the client immediately.
                            </p>
                            <div class="mt-4">
                                <?php if (isset($component)) { $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-actions::components.action','data' => ['action' => $reportAction,'class' => 'fi-btn fi-btn--size-lg fi-btn--color-white/95']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-actions::action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reportAction),'class' => 'fi-btn fi-btn--size-lg fi-btn--color-white/95']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $attributes = $__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__attributesOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83)): ?>
<?php $component = $__componentOriginal70308eab0db7bee07ae0d7b141f6dc83; ?>
<?php unset($__componentOriginal70308eab0db7bee07ae0d7b141f6dc83); ?>
<?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <?php if (isset($component)) { $__componentOriginal028e05680f6c5b1e293abd7fbe5f9758 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-actions::components.modals','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-actions::modals'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758)): ?>
<?php $attributes = $__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758; ?>
<?php unset($__attributesOriginal028e05680f6c5b1e293abd7fbe5f9758); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal028e05680f6c5b1e293abd7fbe5f9758)): ?>
<?php $component = $__componentOriginal028e05680f6c5b1e293abd7fbe5f9758; ?>
<?php unset($__componentOriginal028e05680f6c5b1e293abd7fbe5f9758); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /Users/admin/Desktop/html_blade-codex-move-personal-acc-assets-to-resources-or-public/resources/views/filament/admin/pages/dashboard.blade.php ENDPATH**/ ?>