@extends('layouts.citizen')

@section('title', __('ui.citizen_pay_title').' #'.$request->id)

@if(!empty($stripeElementsReady) && !empty($stripePaymentIntentClientSecret))
    @push('head')
        <script src="https://js.stripe.com/v3/"></script>
    @endpush
    @push('styles')
        <style>
            .stripe-el-wrap {
                padding: 0.5rem 0.75rem;
                border: 1px solid #ced4da;
                border-radius: 0.375rem;
                background: #fff;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .stripe-el-wrap.StripeElement--focus {
                border-color: #6d28d9;
                box-shadow: 0 0 0 0.2rem rgba(109, 40, 217, 0.15);
            }
            .stripe-el-wrap.StripeElement--invalid {
                border-color: #dc3545;
            }
        </style>
    @endpush
@endif

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.requests.index') }}">{{ __('ui.my_requests') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('citizen.requests.show', $request) }}">#{{ $request->id }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('ui.citizen_pay_breadcrumb') }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">{{ __('ui.citizen_pay_title') }}</h2>
            <p class="text-muted small mb-4">
                {{ __('ui.citizen_show_lbl_service') }}: <strong>{{ $request->service?->name }}</strong> · {{ __('ui.citizen_show_lbl_office') }}: <strong>{{ $request->governmentOffice?->name }}</strong>
            </p>

            <div class="card card-soft mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-2">
                        <span class="text-muted text-uppercase fw-semibold small">{{ __('ui.citizen_pay_amount_due') }}</span>
                        <span class="fs-3 fw-bold text-success">${{ number_format((float) $payment->amount, 2) }} <span class="fs-6 text-muted fw-normal">USD</span></span>
                    </div>
                    @if($eurRate !== null && $eurAmount !== null)
                        <p class="small text-muted mb-0">
                            Reference (ECB via Frankfurter): ≈ <strong>{{ number_format($eurAmount, 2) }} EUR</strong> at rate 1 USD = {{ number_format($eurRate, 4) }} EUR.
                        </p>
                    @else
                        <p class="small text-muted mb-0">EUR reference unavailable right now; amount is charged in USD.</p>
                    @endif
                </div>
            </div>

            @php $openCrypto = request()->query('tab') === 'crypto'; @endphp

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $openCrypto ? '' : 'active' }}" id="tab-card" data-bs-toggle="tab" data-bs-target="#pane-card" type="button" role="tab">{{ __('ui.citizen_pay_tab_card') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $openCrypto ? 'active' : '' }}" id="tab-crypto" data-bs-toggle="tab" data-bs-target="#pane-crypto" type="button" role="tab">{{ __('ui.citizen_pay_tab_crypto') }}</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade {{ $openCrypto ? '' : 'show active' }}" id="pane-card" role="tabpanel">
                    <div class="card card-soft">
                        <div class="card-body">
                            @if(!empty($stripeElementsReady))
                                <p class="small text-muted mb-3">
                                    Card details are collected securely by <strong>Stripe</strong> (PCI-compliant fields). Use Stripe <a href="https://stripe.com/docs/testing" target="_blank" rel="noopener noreferrer">test cards</a> in test mode (e.g. <span class="font-monospace">4242&nbsp;4242&nbsp;4242&nbsp;4242</span>).
                                </p>
                                <div class="mb-3">
                                    <label class="form-label small">{{ __('ui.citizen_pay_name_on_card') }} <span class="text-muted">({{ __('ui.citizen_feedback_optional') }})</span></label>
                                    <input type="text" id="stripe-billing-name" class="form-control form-control-sm" maxlength="120" autocomplete="cc-name" placeholder="{{ __('ui.citizen_pay_name_on_card') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">{{ __('ui.citizen_pay_card_number') }}</label>
                                    <div id="stripe-card-number" class="stripe-el-wrap"></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">{{ __('ui.citizen_pay_expiry') }}</label>
                                        <div id="stripe-card-expiry" class="stripe-el-wrap"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">{{ __('ui.citizen_pay_cvc') }}</label>
                                        <div id="stripe-card-cvc" class="stripe-el-wrap"></div>
                                    </div>
                                </div>
                                <div id="stripe-card-errors" class="text-danger small mb-2" role="alert"></div>
                                <button type="button" id="stripe-pay-btn" class="btn btn-primary">
                                    <i class="bi bi-credit-card me-1"></i>{{ __('ui.citizen_pay_btn', ['amount' => '$'.number_format((float) $payment->amount, 2)]) }}
                                </button>
                            @elseif(!empty($stripeElementsError))
                                <div class="alert alert-warning mb-0 small">{{ $stripeElementsError }}</div>
                            @elseif(! $stripeSecretConfigured)
                                <div class="alert alert-warning mb-0 small">
                                    Add <code>STRIPE_SECRET</code> to your <code>.env</code> (see <a href="https://dashboard.stripe.com/test/apikeys" target="_blank" rel="noopener noreferrer">Stripe API keys</a>). You can still use the <strong>Cryptocurrency</strong> tab if it is configured.
                                </div>
                            @elseif(! $stripePublishableConfigured)
                                <div class="alert alert-warning mb-0 small">
                                    Add <code>STRIPE_PUBLISHABLE_KEY</code> (starts with <code class="user-select-all">pk_test_</code> or <code class="user-select-all">pk_live_</code>) to <code>.env</code> so the card form can load. The secret key alone is not enough for embedded fields.
                                </div>
                            @else
                                <div class="alert alert-warning mb-0 small">
                                    Card form could not be started. Try again or use the <strong>Cryptocurrency</strong> tab.
                                </div>
                            @endif
                            @error('stripe')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $openCrypto ? 'show active' : '' }}" id="pane-crypto" role="tabpanel">
                    @php $activeAsset = $cryptoQuote['asset'] ?? null; @endphp

                    <div class="card card-soft mb-3">
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                {{ __('ui.citizen_pay_tab_crypto') }} — <strong>{{ __('ui.citizen_pay_crypto_get_quote') }}</strong>
                            </p>
                            <form method="POST" action="{{ route('citizen.requests.pay.crypto-quote', $request) }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small">{{ __('ui.citizen_pay_crypto_title') }}</label>
                                    <select name="asset" class="form-select form-select-sm" required>
                                        <option value="btc" @selected($activeAsset === 'btc')>Bitcoin (BTC)</option>
                                        <option value="eth" @selected($activeAsset === 'eth')>Ethereum (ETH)</option>
                                        <option value="usdt" @selected($activeAsset === 'usdt')>Tether (USDT, ERC-20)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                                        <i class="bi bi-arrow-repeat me-1"></i>{{ __('ui.citizen_pay_crypto_get_quote') }}
                                    </button>
                                </div>
                            </form>
                            @error('crypto')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if($cryptoQuote)
                        @php
                            $assetLabel = match($cryptoQuote['asset']) {
                                'btc'  => 'Bitcoin (BTC)',
                                'eth'  => 'Ethereum (ETH)',
                                'usdt' => 'Tether (USDT)',
                                default => strtoupper($cryptoQuote['asset']),
                            };
                        @endphp
                        <div class="card card-soft mb-3 border-primary border-opacity-25">
                            <div class="card-header bg-white border-0 fw-semibold small d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-send me-1 text-primary"></i>Send {{ $assetLabel }}</span>
                                @if(!empty($showDemoCryptoNotice))
                                    <span class="badge bg-warning text-dark fw-normal" style="font-size:.7rem;">
                                        <i class="bi bi-cone-striped me-1"></i>Test mode — demo address
                                    </span>
                                @endif
                            </div>
                            <div class="card-body small">
                                @if(!empty($cryptoQuoteExpiresAt))
                                    <p class="small text-muted border rounded px-2 py-2 bg-light mb-3 mb-md-3">
                                        <i class="bi bi-clock-history me-1"></i>This rate and amount are valid until
                                        <strong>{{ \Illuminate\Support\Carbon::parse($cryptoQuoteExpiresAt)->timezone(config('app.timezone'))->format('M j, Y g:i A T') }}</strong>.
                                        After that, click <strong>Get quote</strong> again.
                                    </p>
                                @endif
                                <dl class="row mb-3">
                                    <dt class="col-sm-4 text-muted">{{ __('ui.citizen_pay_crypto_amount') }}</dt>
                                    <dd class="col-sm-8 mb-2 font-monospace fw-bold">{{ $cryptoQuote['crypto_amount'] }}</dd>
                                    <dt class="col-sm-4 text-muted">{{ __('ui.citizen_pay_crypto_rate') }}</dt>
                                    <dd class="col-sm-8 mb-2">1 unit = ${{ number_format($cryptoQuote['usd_per_unit'], 2) }} USD <span class="text-muted">({{ $cryptoQuote['rate_source'] }})</span></dd>
                                    <dt class="col-sm-4 text-muted">{{ __('ui.citizen_pay_crypto_address') }}</dt>
                                    <dd class="col-sm-8 mb-0">
                                        <div class="d-flex align-items-start gap-2 flex-wrap">
                                            <span class="font-monospace text-break" id="crypto-wallet-addr" style="word-break:break-all;">{{ $cryptoQuote['wallet'] }}</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 flex-shrink-0"
                                                    id="copy-wallet-btn"
                                                    title="Copy address"
                                                    onclick="(function(b){navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('crypto-wallet-addr').textContent.trim()).then(function(){var t=b.innerHTML;b.innerHTML='<i class=\'bi bi-check2\'></i>';setTimeout(function(){b.innerHTML=t;},1500);}).catch(function(){});})(this)">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                        </div>
                                    </dd>
                                </dl>
                                @php
                                    $txRequired = !config('payments.crypto.auto_complete_after_citizen_submit')
                                        && config('payments.crypto.require_tx_reference_when_manual_verify');
                                    $txMinLen = (int) config('payments.crypto.tx_reference_min_length', 10);
                                @endphp
                                <form method="POST" action="{{ route('citizen.requests.pay.crypto-confirm', $request) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small">
                                            {{ __('ui.citizen_pay_crypto_tx_id') }}
                                            @if($txRequired)
                                                <span class="text-danger">*</span>
                                            @else
                                                <span class="text-muted">({{ __('ui.citizen_feedback_optional') }})</span>
                                            @endif
                                        </label>
                                        <input type="text" name="tx_reference"
                                               class="form-control form-control-sm font-monospace @error('crypto') is-invalid @enderror"
                                               maxlength="500"
                                               value="{{ old('tx_reference') }}"
                                               placeholder="{{ $txRequired ? 'Required — paste your tx hash or explorer link' : 'Paste explorer link or tx hash' }}"
                                               @if($txRequired) required minlength="{{ $txMinLen }}" @endif>
                                        @if($txRequired)
                                            <div class="form-text" style="font-size:.74rem;">
                                                After you send the crypto, paste the transaction hash (or full explorer URL) here so the office can verify on-chain.
                                            </div>
                                        @endif
                                        @error('crypto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check2-circle me-1"></i>{{ __('ui.citizen_pay_crypto_confirm') }}
                                    </button>
                                </form>
                                @if(config('payments.crypto.auto_complete_after_citizen_submit'))
                                    <p class="text-muted mt-3 mb-0" style="font-size:0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i>Demo / local mode: submitting confirms the request immediately. In production set <code>PAYMENTS_CRYPTO_AUTO_COMPLETE=false</code> and verify on-chain before releasing the request.
                                    </p>
                                @else
                                    <p class="text-muted mt-3 mb-0" style="font-size:0.75rem;">
                                        After you submit a reference, staff will verify the transfer before your request appears in the office queue.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <p class="small text-muted mt-4 mb-0">
                <a href="{{ route('citizen.requests.show', $request) }}">
                    <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} me-1"></i>{{ __('ui.citizen_pay_back') }}
                </a>
            </p>
        </div>
    </div>
@endsection

@if(!empty($stripeElementsReady) && !empty($stripePaymentIntentClientSecret))
    @push('scripts')
        <script>
            window.STRIPE_PAY_PAGE = {
                pk: @json($stripePublishableKey),
                clientSecret: @json($stripePaymentIntentClientSecret),
                confirmUrl: @json(route('citizen.requests.pay.stripe.confirm', $request)),
                returnUrl: @json(route('citizen.payments.stripe.elements-return', [], true)),
                email: @json(auth()->user()->email),
                csrf: @json(csrf_token()),
            };
        </script>
        <script>
            (function () {
                var cfg = window.STRIPE_PAY_PAGE;
                if (!cfg || !window.Stripe) return;

                var stripe = Stripe(cfg.pk);
                var elements = stripe.elements();
                var style = { base: { fontSize: '16px', color: '#212529', '::placeholder': { color: '#6c757d' } } };

                var cardNumber = elements.create('cardNumber', { style: style, showIcon: true });
                var cardExpiry = elements.create('cardExpiry', { style: style });
                var cardCvc = elements.create('cardCvc', { style: style });

                cardNumber.mount('#stripe-card-number');
                cardExpiry.mount('#stripe-card-expiry');
                cardCvc.mount('#stripe-card-cvc');

                var errEl = document.getElementById('stripe-card-errors');
                var btn = document.getElementById('stripe-pay-btn');
                var nameInput = document.getElementById('stripe-billing-name');

                function showErr(msg) {
                    if (errEl) errEl.textContent = msg || '';
                }

                [cardNumber, cardExpiry, cardCvc].forEach(function (el) {
                    el.on('change', function (e) {
                        if (e.error) showErr(e.error.message);
                        else showErr('');
                    });
                });

                btn.addEventListener('click', async function () {
                    showErr('');
                    btn.disabled = true;

                    var billing = { email: cfg.email };
                    var nm = nameInput && nameInput.value ? nameInput.value.trim() : '';
                    if (nm) billing.name = nm;

                    try {
                        var result = await stripe.confirmCardPayment(cfg.clientSecret, {
                            payment_method: { card: cardNumber, billing_details: billing },
                            return_url: cfg.returnUrl,
                        });

                        if (result.error) {
                            showErr(result.error.message);
                            btn.disabled = false;
                            return;
                        }

                        var pi = result.paymentIntent;
                        if (pi && pi.status === 'succeeded') {
                            var res = await fetch(cfg.confirmUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': cfg.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ payment_intent_id: pi.id }),
                            });
                            var data = await res.json().catch(function () { return {}; });
                            if (data.ok && data.redirect) {
                                btn.textContent = 'Payment successful! Redirecting…';
                                btn.classList.remove('btn-primary');
                                btn.classList.add('btn-success');
                                setTimeout(function () { window.location.href = data.redirect; }, 1500);
                                return;
                            }
                            showErr(data.message || 'Could not finalize payment. Please refresh or contact support.');
                            btn.disabled = false;
                            return;
                        }

                        showErr('Payment is still processing. If you were redirected for bank verification, complete that step.');
                        btn.disabled = false;
                    } catch (e) {
                        showErr('Something went wrong. Try again.');
                        btn.disabled = false;
                    }
                });
            })();
        </script>
    @endpush
@endif
