<?php $__env->startSection('title', 'Админка'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $formatMoney = static function ($amount, ?string $currency): string {
        if ($amount === null) {
            return '—';
        }

        $formatted = number_format((float) $amount, 2, '.', ' ');
        $currencyCode = $currency ?: (config('currencies.default') ?? 'EUR');

        return $formatted . ' ' . $currencyCode;
    };

    $formatDate = static function ($value, string $format = 'd.m.Y H:i'): string {
        if (! $value) {
            return '—';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format($format);
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $statusBadgeStyle = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'approved', 'active', 'verified', 'verificated', 'success', 'completed' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#dcfce7; color:#166534; text-transform:uppercase;',
            'pending', 'hold', 'processing' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#fef3c7; color:#92400e; text-transform:uppercase;',
            'blocked', 'rejected', 'failed', 'declined', 'canceled' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#fee2e2; color:#b91c1c; text-transform:uppercase;',
            default => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#e2e8f0; color:#334155; text-transform:uppercase;',
        };
    };

    $selectedUserCurrency = $selectedUser?->currency ?? (config('currencies.default') ?? 'EUR');
    $hasClients = $clientOptions->isNotEmpty();
    $withdrawalTab = old('method', 'card');
?>

<div class="wrapper">
    <main class="page">
        <div class="admin-page">
            <div class="container">
                <div class="admin-panel">
                    <?php if(session('status')): ?>
                        <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: #ecfdf5; border-radius: 1rem; color: #047857; font-weight: 600;">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if($errors->any() && ! session('status')): ?>
                        <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: #fef2f2; border-radius: 1rem; color: #b91c1c; font-weight: 600;">
                            Исправьте ошибки формы и попробуйте снова.
                        </div>
                    <?php endif; ?>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">User selection</div>

                        <?php if(! $hasClients): ?>
                            <p class="admin-panel__empty">Нет клиентов для отображения. Создайте клиента через Filament панель.</p>
                        <?php else: ?>
                            <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="admin-panel__line" style="gap: 1rem; flex-wrap: wrap;" id="admin-dashboard-user-form">
                                <select id="admin-dashboard-client" name="user" class="admin-panel__select" onchange="window.adminDashboardChangeUser(this.value)">
                                    <?php $__currentLoopData = $clientOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php if($selectedUserId === (int) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php if($selectedUser): ?>
                                    <div class="admin-panel__item">
                                        <div style="font-size: 0.75rem; text-transform: uppercase; color: #63616C; letter-spacing: 0.04em;">Primary account</div>
                                        <div style="font-weight: 600; font-size: 1.125rem;"><?php echo e($primaryAccount ? $formatMoney($primaryAccount->balance, $primaryAccount->currency ?? $selectedUserCurrency) : '—'); ?></div>
                                        <?php if($primaryAccount): ?>
                                            <div style="font-size: 0.85rem; color: #63616C;"><?php echo e($primaryAccount->number); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </form>

                            <?php if($selectedUser): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem;">
                                    <button type="button" class="btn btn--md" data-popup="#support-modal">Написать в поддержку</button>
                                    <button type="button" class="btn btn--md" data-popup="#withdraw-modal">Создать вывод средств</button>
                                    <button type="button" class="btn btn--md" data-popup="#violation">Сообщить о нарушении</button>
                                    <a class="btn btn--md" style="display: inline-flex; align-items: center; justify-content: center;" href="<?php echo e(route('filament.admin.resources.users.edit', $selectedUser)); ?>" target="_blank" rel="noopener">Профиль в панели</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">User info</div>

                        <?php if(! $selectedUser): ?>
                            <p class="admin-panel__empty">Выберите клиента, чтобы увидеть информацию об аккаунте.</p>
                        <?php else: ?>
                            <div class="admin-panel__grid">
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Full name</div>
                                    <input class="admin-panel__field-input" type="text" value="<?php echo e($selectedUser->name); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Email</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedUser->email); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Verification</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e(strtoupper($selectedUser->verification_status ?? 'unknown')); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Main balance</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($formatMoney($selectedUser->main_balance, $selectedUserCurrency)); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Currency</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedUserCurrency); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Created at</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($formatDate($selectedUser->created_at, 'd.m.Y')); ?>" readonly>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Bank accounts</div>

                        <?php if(! $selectedUser || $accounts->isEmpty()): ?>
                            <p class="admin-panel__empty">Нет активных счетов для клиента.</p>
                        <?php else: ?>
                            <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="admin-panel__grid" style="gap: 1.25rem;">
                                <input type="hidden" name="user" value="<?php echo e($selectedUserId); ?>">
                                <div class="admin-panel__field" style="min-width: 220px;">
                                    <div class="admin-panel__field-label">Выберите счёт</div>
                                    <select name="account" onchange="this.form.submit()">
                                        <?php $__currentLoopData = $accountOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($id); ?>" <?php if($selectedAccount && $selectedAccount->id === (int) $id): echo 'selected'; endif; ?>><?php echo e($number); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Статус</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e(strtoupper($selectedAccount->status ?? '—')); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Тип счёта</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount->type ?? '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Организация</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount->organization ?? '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Банк</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount->bank ?? '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Баланс счёта</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount ? $formatMoney($selectedAccount->balance, $selectedAccount->currency ?? $selectedUserCurrency) : '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Срок действия</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount ? $formatDate($selectedAccount->term, 'd.m.Y') : '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Инициалы клиента</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount->client_initials ?? '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Инициалы брокера</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount->broker_initials ?? '—'); ?>" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Primary</div>
                                    <input class="admin-panel__field-info" type="text" value="<?php echo e($selectedAccount && $selectedAccount->is_default ? 'YES' : 'NO'); ?>" readonly>
                                </div>
                            </form>
                        <?php endif; ?>

                        <button class="btn btn--md" data-popup="#create-modal" type="button" <?php if(! $selectedUser): echo 'disabled'; endif; ?>>Создать новый счёт</button>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Последние транзакции</div>
                        <?php if(! $selectedUser || $transactions->isEmpty()): ?>
                            <p class="admin-panel__empty">Для выбранного клиента нет транзакций.</p>
                        <?php else: ?>
                            <div class="admin-panel__table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Тип</th>
                                            <th>От</th>
                                            <th>Кому</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($formatDate($transaction->created_at, 'd.m.Y H:i')); ?></td>
                                                <td><?php echo e(strtoupper($transaction->type)); ?></td>
                                                <td><?php echo e($transaction->from); ?></td>
                                                <td><?php echo e($transaction->to); ?></td>
                                                <td><?php echo e($formatMoney($transaction->amount, $transaction->currency ?? $selectedUserCurrency)); ?></td>
                                                <td><span style="<?php echo e($statusBadgeStyle($transaction->status)); ?>"><?php echo e(strtoupper($transaction->status)); ?></span></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Заявки на вывод средств</div>
                        <?php if(! $selectedUser || $withdrawals->isEmpty()): ?>
                            <p class="admin-panel__empty">Данных пока нет.</p>
                        <?php else: ?>
                            <div class="admin-panel__table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Метод</th>
                                            <th>Счёт</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($formatDate($withdrawal->created_at, 'd.m.Y H:i')); ?></td>
                                                <td><?php echo e(ucfirst($withdrawal->method)); ?></td>
                                                <td><?php echo e($withdrawal->fromAccount?->number ?? 'Main balance'); ?></td>
                                                <td><?php echo e($formatMoney($withdrawal->amount, $selectedUserCurrency)); ?></td>
                                                <td><span style="<?php echo e($statusBadgeStyle($withdrawal->status)); ?>"><?php echo e(strtoupper($withdrawal->status)); ?></span></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Документы</div>
                        <?php if(! $selectedUser || $documents->isEmpty()): ?>
                            <p class="admin-panel__empty">Документы отсутствуют.</p>
                        <?php else: ?>
                            <ul class="admin-panel__list">
                                <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo e($document->original_name); ?></div>
                                            <div style="font-size: 0.85rem; color: #63616C;"><?php echo e($formatDate($document->created_at, 'd.m.Y H:i')); ?></div>
                                        </div>
                                        <span style="<?php echo e($statusBadgeStyle($document->status)); ?>"><?php echo e(strtoupper($document->status ?? 'PENDING')); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Сообщения о нарушениях</div>
                        <?php if(! $selectedUser || $fraudClaims->isEmpty()): ?>
                            <p class="admin-panel__empty">Заявок нет.</p>
                        <?php else: ?>
                            <ul class="admin-panel__list">
                                <?php $__currentLoopData = $fraudClaims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo e(str(strip_tags($claim->details))->limit(80)); ?></div>
                                            <div style="font-size: 0.85rem; color: #63616C;"><?php echo e($formatDate($claim->created_at, 'd.m.Y H:i')); ?></div>
                                        </div>
                                        <span style="<?php echo e($statusBadgeStyle($claim->status)); ?>"><?php echo e(strtoupper($claim->status)); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
    $supportErrors = $errors->support ?? null;
    $fraudErrors = $errors->fraud ?? null;
    $withdrawalErrors = $errors->withdrawal ?? null;
    $accountErrors = $errors->account ?? null;
?>

<div aria-hidden="true" class="popup popup--sm" id="support-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="<?php echo e(asset('personal-acc/img/logo.svg')); ?>"></div>
                    <div class="modal-content__text">
                        <p>Отправить сообщение в поддержку</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    <?php if($supportErrors && $supportErrors->any()): ?>
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;"><?php echo e($supportErrors->first()); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('admin.dashboard.support')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="field">
                            <select name="user_id" <?php if(! $hasClients): echo 'disabled'; endif; ?>>
                                <option value="">Выберите клиента</option>
                                <?php $__currentLoopData = $clientOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php if(old('user_id', $selectedUser?->id) == (int) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="field">
                            <textarea name="message" placeholder="Сообщение" rows="6"><?php echo e(old('message')); ?></textarea>
                            <?php $__errorArgs = ['message', 'support'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button class="btn btn--md" type="submit" <?php if(! $hasClients): echo 'disabled'; endif; ?>>Отправить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup" id="withdraw-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="<?php echo e(asset('personal-acc/img/logo.svg')); ?>"></div>
                    <div class="modal-content__text">
                        <p>Создать заявку на вывод средств</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    <?php if($withdrawalErrors && $withdrawalErrors->any()): ?>
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;"><?php echo e($withdrawalErrors->first()); ?></div>
                    <?php endif; ?>
                    <div class="tabs" data-tabs data-tabs-animate="300">
                        <nav class="tabs__navigation" data-tabs-titles>
                            <button class="tabs__title <?php echo e($withdrawalTab === 'card' ? '_tab-active' : ''); ?>" type="button" data-tabs-title>
                                <span>На банковскую карту</span>
                            </button>
                            <button class="tabs__title <?php echo e($withdrawalTab === 'bank' ? '_tab-active' : ''); ?>" type="button" data-tabs-title>
                                <span>По IBAN</span>
                            </button>
                            <button class="tabs__title <?php echo e($withdrawalTab === 'crypto' ? '_tab-active' : ''); ?>" type="button" data-tabs-title>
                                <span>В криптовалюте</span>
                            </button>
                        </nav>
                        <div class="tabs__content" data-tabs-body>
                            <div class="tabs__body" data-tabs-item <?php if($withdrawalTab !== 'card'): ?> hidden <?php endif; ?>>
                                <form method="POST" action="<?php echo e(route('admin.dashboard.withdrawals.store')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="method" value="card">
                                    <input type="hidden" name="user_id" value="<?php echo e(old('user_id', $selectedUser?->id)); ?>">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            <?php $__currentLoopData = $accountOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($id); ?>" <?php if(old('from_account_id') == (int) $id): echo 'selected'; endif; ?>><?php echo e($number); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[card_number]" placeholder="1111 2222 3333 4444" type="text" value="<?php echo e(old('details.card_number')); ?>">
                                        <?php $__errorArgs = ['details.card_number', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="field">
                                        <input name="details[card_holder]" placeholder="Fullname card holder" type="text" value="<?php echo e(old('details.card_holder')); ?>">
                                        <?php $__errorArgs = ['details.card_holder', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="<?php echo e(old('amount')); ?>">
                                        <?php $__errorArgs = ['amount', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <button class="btn btn--md" type="submit" <?php if(! $selectedUser): echo 'disabled'; endif; ?>>Отправить</button>
                                </form>
                            </div>
                            <div class="tabs__body" data-tabs-item <?php if($withdrawalTab !== 'bank'): ?> hidden <?php endif; ?>>
                                <form method="POST" action="<?php echo e(route('admin.dashboard.withdrawals.store')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="method" value="bank">
                                    <input type="hidden" name="user_id" value="<?php echo e(old('user_id', $selectedUser?->id)); ?>">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            <?php $__currentLoopData = $accountOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($id); ?>" <?php if(old('from_account_id') == (int) $id): echo 'selected'; endif; ?>><?php echo e($number); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[iban]" placeholder="Enter IBAN" type="text" value="<?php echo e(old('details.iban')); ?>">
                                        <?php $__errorArgs = ['details.iban', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="field">
                                        <input name="details[bic]" placeholder="BIC code" type="text" value="<?php echo e(old('details.bic')); ?>">
                                        <?php $__errorArgs = ['details.bic', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="field">
                                        <input name="details[holder]" placeholder="Fullname bank account holder" type="text" value="<?php echo e(old('details.holder')); ?>">
                                    </div>
                                    <div class="field">
                                        <input name="details[country]" placeholder="Country" type="text" value="<?php echo e(old('details.country')); ?>">
                                    </div>
                                    <div class="field">
                                        <input name="details[bank_name]" placeholder="Name of the bank" type="text" value="<?php echo e(old('details.bank_name')); ?>">
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="<?php echo e(old('amount')); ?>">
                                        <?php $__errorArgs = ['amount', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <button class="btn btn--md" type="submit" <?php if(! $selectedUser): echo 'disabled'; endif; ?>>Отправить</button>
                                </form>
                            </div>
                            <div class="tabs__body" data-tabs-item <?php if($withdrawalTab !== 'crypto'): ?> hidden <?php endif; ?>>
                                <form method="POST" action="<?php echo e(route('admin.dashboard.withdrawals.store')); ?>" class="form-crypto">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="method" value="crypto">
                                    <input type="hidden" name="user_id" value="<?php echo e(old('user_id', $selectedUser?->id)); ?>">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            <?php $__currentLoopData = $accountOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $number): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($id); ?>" <?php if(old('from_account_id') == (int) $id): echo 'selected'; endif; ?>><?php echo e($number); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[address]" placeholder="Deposit address" type="text" value="<?php echo e(old('details.address')); ?>">
                                        <?php $__errorArgs = ['details.address', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="field">
                                        <input name="details[network]" placeholder="Network" type="text" value="<?php echo e(old('details.network')); ?>">
                                    </div>
                                    <div class="field">
                                        <input name="details[coin]" placeholder="Coin" type="text" value="<?php echo e(old('details.coin')); ?>">
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="<?php echo e(old('amount')); ?>">
                                        <?php $__errorArgs = ['amount', 'withdrawal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="error-message"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <button class="btn btn--md" type="submit" <?php if(! $selectedUser): echo 'disabled'; endif; ?>>Отправить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup popup--md" id="violation">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="<?php echo e(asset('personal-acc/img/logo.svg')); ?>"></div>
                    <div class="modal-content__text">
                        <p>Сообщить о нарушении</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    <?php if($fraudErrors && $fraudErrors->any()): ?>
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;"><?php echo e($fraudErrors->first()); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('admin.dashboard.fraud-claims.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="field">
                            <select name="user_id" <?php if(! $hasClients): echo 'disabled'; endif; ?>>
                                <option value="">Выберите клиента</option>
                                <?php $__currentLoopData = $clientOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php if(old('user_id', $selectedUser?->id) == (int) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="field">
                            <textarea name="details" placeholder="Опишите нарушение" rows="6"><?php echo e(old('details')); ?></textarea>
                            <?php $__errorArgs = ['details', 'fraud'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button class="btn" type="submit" <?php if(! $hasClients): echo 'disabled'; endif; ?>>Отправить</button>
                        <label class="modal-content__file">
                            <input hidden type="file" disabled>
                            <span>Прикрепление файлов реализуется отдельно</span>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup popup--sm" id="create-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <div class="create-account">
                <div class="create-account__title">Создание нового счёта</div>
                <?php if($accountErrors && $accountErrors->any()): ?>
                    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;"><?php echo e($accountErrors->first()); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(route('admin.dashboard.accounts.store')); ?>" method="POST" class="create-account__form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo e(old('user_id', $selectedUser?->id)); ?>">

                    <div class="field">
                        <input name="number" placeholder="Номер счёта" type="text" value="<?php echo e(old('number')); ?>">
                        <?php $__errorArgs = ['number', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <select name="type">
                            <option value="">Тип счёта</option>
                            <?php $__currentLoopData = $accountTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(old('type') === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['type', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <input name="balance" placeholder="Баланс" type="number" step="0.01" min="0" value="<?php echo e(old('balance')); ?>">
                        <?php $__errorArgs = ['balance', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <select name="currency">
                            <option value="">Валюта (по умолчанию <?php echo e($selectedUserCurrency); ?>)</option>
                            <?php $__currentLoopData = $currencyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(old('currency') === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['currency', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <input name="organization" placeholder="Организация" type="text" value="<?php echo e(old('organization')); ?>">
                        <?php $__errorArgs = ['organization', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <input name="bank" placeholder="Банк" type="text" value="<?php echo e(old('bank')); ?>">
                    </div>

                    <div class="field">
                        <input name="client_initials" placeholder="Инициалы клиента" type="text" value="<?php echo e(old('client_initials')); ?>">
                        <?php $__errorArgs = ['client_initials', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <input name="broker_initials" placeholder="Инициалы брокера" type="text" value="<?php echo e(old('broker_initials')); ?>">
                        <?php $__errorArgs = ['broker_initials', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <input name="term" placeholder="Срок действия" type="date" value="<?php echo e(old('term')); ?>">
                        <?php $__errorArgs = ['term', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="field">
                        <select name="status">
                            <option value="">Статус</option>
                            <?php $__currentLoopData = $accountStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(old('status') === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['status', 'account'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="checkbox">
                        <input class="checkbox__input" id="account_is_default" name="is_default" type="checkbox" value="1" <?php if(old('is_default')): echo 'checked'; endif; ?>>
                        <label class="checkbox__label" for="account_is_default"><span class="checkbox__text">Сделать основным</span></label>
                    </div>

                    <button class="btn btn--md" type="submit" <?php if(! $selectedUser): echo 'disabled'; endif; ?>>Добавить счёт</button>
                </form>
            </div>
        </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        const baseDashboardUrl = <?php echo json_encode(route('admin.dashboard'), 15, 512) ?>;

        window.adminDashboardChangeUser = function (value) {
            const url = new URL(baseDashboardUrl, window.location.origin);

            if (value) {
                url.searchParams.set('user', value);
            }

            window.location.assign(url.toString());
        };

        const userForm = document.getElementById('admin-dashboard-user-form');
        if (userForm) {
            userForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const select = userForm.querySelector('select[name="user"]');
                if (select) {
                    window.adminDashboardChangeUser(select.value);
                }
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/admin/Desktop/html_blade-codex-move-personal-acc-assets-to-resources-or-public/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>