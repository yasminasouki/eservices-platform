<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

















# E-Services Management Platform

A web-based platform that digitizes and streamlines public services provided by government offices, municipalities, and administrative entities.

## Tech Stack
- Laravel 12
- MySQL
- Bootstrap

## Team: installing dependencies (after clone or `git pull`)

Run these from the project root. They install **everything** declared in `composer.json` / `composer.lock` and `package.json` / `package-lock.json`. Teammates should **not** run one-off `composer require` or `npm install <package>` unless they are adding a new dependency and committing the updated manifests.

```bash
composer install
npm install
```

For a clean install that matches `package-lock.json` exactly (CI-style), you can use `npm ci` instead of `npm install`.

**If `composer install` fails** (PHP version, missing `ext-*`, etc.):

```bash
composer install --ignore-platform-reqs
```

Prefer matching the project’s PHP version when you can; `--ignore-platform-reqs` is a fallback so everyone can get a working `vendor/` folder.

### PHP packages (`composer.json` → `composer install`)

| Package | Role in this project |
|---------|----------------------|
| `laravel/framework` | Laravel application core |
| `laravel/tinker` | REPL (`php artisan tinker`) |
| `laravel/socialite` | Google & Facebook login |
| `pragmarx/google2fa` | TOTP secret generation / verification |
| `pragmarx/google2fa-laravel` | Laravel integration for 2FA |
| `bacon/bacon-qr-code` | QR images for 2FA enrollment |
| `laravel/reverb` | WebSocket server for real-time chat (with Laravel Echo) |
| `stripe/stripe-php` | Server-side Stripe API (PaymentIntents, webhooks) for citizen checkout |
| `dompdf/dompdf` | PDF generation (approval notices, certificates, receipts) |

Transitive dependencies (Symfony, Monolog, etc.) are pulled in automatically; `composer.lock` is the source of truth for exact versions.

### Front-end tooling (`package.json` → `npm install`)

| Package | Role in this project |
|---------|----------------------|
| `vite` | Build tool and dev server |
| `laravel-vite-plugin` | Laravel ↔ Vite integration |
| `axios` | HTTP client (used from `resources/js/bootstrap.js`) |
| `laravel-echo` | Subscribe to private channels and listen for broadcast events |
| `pusher-js` | WebSocket client (Reverb speaks the Pusher protocol) |
| `tailwindcss` / `@tailwindcss/vite` | CSS pipeline (if used in `resources/css`) |
| `concurrently` | Used by Composer script `composer run dev` to run multiple processes |

## Clone, `git pull`, and what Git does not include

These paths are **not** in the repository (see `.gitignore`). Each developer creates them locally:

| Path | How to get it |
|------|----------------|
| `.env` | Copy from `.env.example` and edit (never commit `.env`). |
| `vendor/` | `composer install` |
| `node_modules/` | `npm install` |
| `public/build/` | `npm run build`, or `npm run dev` while you edit JS/CSS |

**After every `git pull`** — especially when `composer.lock` or `package-lock.json` changed — run:

```bash
composer install
npm install
npm run build
```

Use `npm run dev` instead of `npm run build` if you are actively working on front-end assets and want Vite’s dev server with hot reload.

**First-time clone** also needs the steps in **Local Setup** below (`php artisan key:generate`, database, `php artisan migrate`, `php artisan storage:link`, optional seed).

**Run the app with live chat (WebSockets) in one process:**

```bash
composer run dev
```

That starts `php artisan serve`, `php artisan reverb:start`, the queue worker, Pail, and `npm run dev`. Alternatively, use separate terminals: `php artisan serve`, `php artisan reverb:start`, and `npm run dev` (see **Real-time chat**).

## Real-time chat (Laravel Reverb + Echo)

**Office live chat** (citizen ↔ municipality staff) is **not tied to a service request**. Citizens open it from an office’s page (**Live chat**) or `/citizen/offices/{office}/chat`. Staff use **Live chat** in the office nav or `/office/{office}/chat` (inbox) and `/office/{office}/chat/{citizen}` (thread).

Broadcasting uses **private channels** `office-chat.{officeId}.{citizenUserId}` and the **`OfficeChatMessageSent`** event (`ShouldBroadcastNow`). After copying `.env.example` to `.env`, ensure **`BROADCAST_CONNECTION=reverb`** and the **`REVERB_*` / `VITE_REVERB_*`** variables are present. If you use `BROADCAST_CONNECTION=reverb` without those keys, Artisan and the app can error until you add them.

For local development you need the **Reverb** WebSocket server running whenever `BROADCAST_CONNECTION=reverb`, otherwise the app cannot push chat events and the browser will not get live updates (messages still save to the database).

**Option A — one command** (starts HTTP server, Reverb, queue worker, logs, and Vite):

```bash
composer run dev
```

**Option B — separate terminals:**

```bash
php artisan serve
php artisan reverb:start
npm run dev
```

Sending a message uses **AJAX**, so **your** new line appears in the thread right away without a full page reload. The **other** participant still relies on **Reverb + Echo** for instant delivery; if Reverb is not running, they will only see new lines after they refresh until you start `php artisan reverb:start`.

Open the live chat for the **same office** in a citizen session and an office session; with Reverb up, new messages should appear on the other side without refreshing. If Echo cannot connect, check that `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME` match your Reverb process (defaults: `localhost`, `8080`, `http`) and that `npm run build` or `npm run dev` has run so `VITE_REVERB_*` values are baked into the front-end bundle.

## Local Setup (run these after cloning)

1. Install dependencies (see **Team: installing dependencies** above).

2. Copy the environment file
   cp .env.example .env

3. Generate app key
   php artisan key:generate

4. Configure your database in .env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=eservices_db
   DB_USERNAME=root
   DB_PASSWORD=

5. Create the database
   mysql -u root -p -e "CREATE DATABASE eservices_db;"

6. Run migrations
   php artisan migrate

7. Link storage
   php artisan storage:link

8. Start the server — either:
   - `composer run dev` (recommended: also starts Reverb, queue, Vite — see **Clone, `git pull`, and what Git does not include**), or
   - `php artisan serve` (add `php artisan reverb:start` and `npm run dev` in other terminals if you need live chat and fresh assets)

   **Session cookies:** Set **`APP_URL`** in `.env` to match what you type in the browser. If you sometimes open `http://localhost:8000` and sometimes `http://127.0.0.1:8000`, the browser keeps **two separate session cookies**, which often looks like “nothing works until I delete cookies.” Pick one host and stick to it (and align `APP_URL`). For plain HTTP, session cookies are not marked Secure unless `APP_URL` is `https://` (see `config/session.php`).

9. Seed default data (includes first admin account)
   php artisan db:seed

## Seeded Admin Login
- Admin portal: `/admin/login`
- Email: `admin@eservices.gov`
- Password: `Admin@12345`
- **First sign-in:** you are redirected to **2FA setup** (scan the QR code in an authenticator app and confirm with a 6-digit code). The seeded account has no TOTP configured until you finish this step.
- **Later sign-ins:** after email/password, you must enter a **6-digit TOTP** (or a recovery code) once per browser session before the admin area loads.

## Seeded Demo Citizen (`php artisan db:seed`)
- Citizen portal: `/login` (not `/admin/login`)
- Email: `citizen@eservices.demo`
- Password: `Citizen@12345`
- This account is **email-verified**, has a placeholder ID on file, and skips **TOTP** in the seed data only (marked like a social-login user) so you can test **Browse offices** and **service requests** immediately after seeding. Use normal registration for a realistic 2FA + ID-upload flow.

## Seeded Municipality Staff
- Portal: `/municipality/login`
- Email: `manager@beirut.gov`
- Password: `password`
- Complete **2FA setup** on first sign-in like the admin account.
- Use this command to clear the already signed-in staff:
-UPDATE users 
SET two_factor_secret = NULL,
    two_factor_recovery_codes = NULL,
    two_factor_confirmed_at = NULL,
    social_provider = 'seed',
    social_provider_id = 'local-demo-office'
WHERE email = 'manager@beirut.gov';

## Environment Variables

After running `cp .env.example .env`, configure the sections below. The file **`.env.example`** in the repo is the template; copy it and adjust for your machine.

### Live chat & broadcasting (Reverb)

These variables enable **real-time** office chat (the other participant’s tab updates without a refresh). They are already present in **`.env.example`** — copy them into **`.env`** and keep PHP (`REVERB_*`) and Vite (`VITE_REVERB_*`) in sync. If you change any `VITE_REVERB_*` value, run **`npm run build`** or **`npm run dev`** again so the browser bundle picks it up.

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=100001
REVERB_APP_KEY=local-reverb-key
REVERB_APP_SECRET=local-reverb-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

| Variable | Purpose |
|----------|---------|
| `BROADCAST_CONNECTION` | Set to `reverb` for live chat. Use `null` only if you intentionally disable broadcasting (other tabs will not get instant updates). |
| `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` | Credentials Laravel and the Reverb server use; defaults match a typical local `php artisan reverb:start`. |
| `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` | Where the **server** publishes events (default: `http://localhost:8080`). |
| `VITE_REVERB_*` | Same values exposed to the **browser** so Laravel Echo can open the WebSocket. Must match `REVERB_*` for host/port/scheme. |

Also set **`APP_URL`** to the exact base URL you use in the browser (for example `http://127.0.0.1:8000` **or** `http://localhost:8000`, not both interchangeably) so sessions and cookies stay consistent.

### OCR.space (Lebanese ID extraction)

After running `cp .env.example .env`, add your OCR.space key for Lebanese ID extraction:

```env
OCR_SPACE_API_KEY=K89286009588957
```

Get a free key at [ocr.space/ocrapi](https://ocr.space/ocrapi) (or ask your team lead for the shared key in a **private** channel — do not commit real keys to Git).

### Stripe (citizen payments)

Add these to **`.env`** (values from [Stripe Test API keys](https://dashboard.stripe.com/test/apikeys) or from a teammate via chat / password manager — **never** commit real keys; GitHub will reject the push):

```env
STRIPE_SECRET=
STRIPE_PUBLISHABLE_KEY=
STRIPE_WEBHOOK_SECRET=
```

Leave `STRIPE_WEBHOOK_SECRET` empty until webhooks are configured; see **Stripe webhook** under **Branch: `admin-fix`** for `whsec_…` setup. Use the standard **`sk_test_…`** value for `STRIPE_SECRET`, not a restricted `rk_test_…` key.

## Branch: `admin-fix` — what changed

Work on this branch adds **admin tooling**, **citizen payments**, **municipality management**, and related polish. After merging or checking out this branch, run **`composer install`**, **`npm install`**, and **`php artisan migrate`** so new PHP dependencies and the payments migration are applied.

### Features and fixes (high level)

- **Admin — municipalities:** CRUD for municipalities (`/admin/municipalities`) with list and form views.
- **Admin — service requests:** Improved listing and a **detail** view for a single request; service-operations and user-management controller updates; office-user admin UI (index/edit).
- **Citizen — payments:** Checkout for a service request with **Stripe** (embedded card flow / PaymentIntent) and an optional **cryptocurrency** path with live-style USD→crypto quotes (`CurrencyExchangeService`, Frankfurter + CoinGecko; configurable in `config/payments.php`).
- **Stripe webhooks:** `POST /webhooks/stripe` handled by `StripeWebhookController` (CSRF excluded in `bootstrap/app.php`). Set `STRIPE_WEBHOOK_SECRET` after you add the endpoint in the Stripe Dashboard.
- **Payments config:** `config/payments.php` centralizes Stripe keys, crypto receiving addresses, and exchange URLs; see **`.env.example`** for all related variables.
- **Data model:** `payments` table extended (e.g. `stripe_checkout_session_id`, expanded `method` enum) — migration `2026_04_09_230000_extend_payments_for_checkout.php`.
- **Notifications:** New/updated mail notifications (e.g. appointment confirmed, missing documents requested, document uploads).
- **Public / citizen / office flows:** Updates to service-request **tracking**, citizen request and office views, and office dashboard/appointment/request handling where tied to the above.

### PHP / Composer

No extra **manual** `composer require` is needed if you run **`composer install`** from a lock file that already includes:

- **`stripe/stripe-php`** — required for Stripe checkout and webhooks.

### Database

```bash
php artisan migrate
```

This applies the payments extension migration (and any others merged with the branch).

### Environment variables (payments)

Copy from **`.env.example`** into your local **`.env`** (never commit `.env`):

| Variable | Purpose |
|----------|---------|
| `STRIPE_SECRET` | Server secret key (`sk_test_…` or `sk_live_…`) |
| `STRIPE_PUBLISHABLE_KEY` | Publishable key for Stripe.js / Elements (`pk_test_…` or `pk_live_…`) |
| `STRIPE_WEBHOOK_SECRET` | Signing secret from **Developers → Webhooks** (`whsec_…`) — empty until the endpoint is configured |
| `CRYPTO_*` | Optional real wallet addresses; on `local`, demo addresses can apply when empty (see `config/payments.php`) |

Fill the Stripe variables using the Dashboard or your team’s shared credentials (not this repo).

### Stripe webhook (local or deployed)

1. In Stripe: **Developers → Webhooks → Add endpoint**.  
2. URL: your app base + `/webhooks/stripe` (e.g. `https://your-ngrok-url.test/webhooks/stripe` for local tunneling).  
3. Subscribe at least to **`checkout.session.completed`** and **`payment_intent.succeeded`** (handled in `StripeWebhookController`).  
4. Copy the endpoint **Signing secret** into **`STRIPE_WEBHOOK_SECRET`** in `.env`.

For local development, [Stripe CLI](https://stripe.com/docs/stripe-cli) can forward webhooks: `stripe listen --forward-to localhost:8000/webhooks/stripe` and use the CLI’s temporary signing secret.

### Editor / IDE (optional)

No new **VS Code extensions** are required for this branch. Use whatever you already use for **PHP (Laravel)** and **Blade**. If front-end assets fail to build, ensure **Node.js** matches a current LTS and run **`npm install`** again.

## Branching Strategy
- main → production ready only, never push directly
- develop → integration branch, all PRs merge here
- feature/your-task → create a new branch for every task

## Brief — Appointment Management

What We Built:
Task 1 — Manage Officer Time Slots
Office staff can create, view and delete available time slots for citizen appointments
Task 2 — Office Views Appointments
Office staff can view all booked appointments, confirm or cancel them with a reason
Task 3 — Send Email Reminders
System sends automatic email reminder to citizen 24 hours before their appointment

###1. No new packages needed
2. No .env changes needed
3. No new migrations needed
4. Run: php artisan schedule:work
   to run scheduler locally(to send the email at the moment to test it)