# TALAbahan Seafood System

A seafood e-commerce, POS, and order-management platform built with **CodeIgniter 4** (PHP 8.2+), **Vue 3 + Inertia.js + Vite + Tailwind CSS**, and an AI chatbot powered by Gemini and OpenRouter.

## Features

- **Role-based access** — Admin, Staff, and Customer dashboards with dedicated UIs
- **Point-of-Sale (POS)** — Walk-in sales with COD/GCash payment support
- **Product management** — Daily seafood inventory with images, cost/selling prices, live availability toggle
- **Order lifecycle** — Full pipeline: Pending → Processing → Shipped → Completed, with status history, tracking, refunds, and damaged-in-transit handling
- **Voucher system** — Platform & shop-scoped vouchers with percent/fixed discounts, min-order rules, payment-method limits
- **Checkout flow** — Cart → Quote → Place Order with shipping location validation, COD compliance, and GCash simulation
- **AI Chatbot** — Customer and admin assistants via Gemini 2.5 Flash / Gemma 4 / GPT-OSS with streaming responses and daily rate limits
- **Analytics dashboard** — Revenue, orders, top items, low-stock alerts

## Architecture

```
┌──────────────────────────────────────────────┐
│  Browser (Vue 3 + Inertia.js + Tailwind)    │
│  Vite dev server :5173 (HMR)                 │
└──────────────────┬───────────────────────────┘
                   │ Inertia protocol
┌──────────────────▼───────────────────────────┐
│  PHP Built-in Server :8080                   │
│  CodeIgniter 4 — Controllers / Services /    │
│  Models — MySQL (mj_chatbot)                 │
└──────────────────┬───────────────────────────┘
                   │ SSE Stream
┌──────────────────▼───────────────────────────┐
│  Node.js/Express :3000                       │
│  Firebase Realtime DB (user tracking)        │
│  AI Chatbot API proxy                        │
└──────────────────────────────────────────────┘
```

## Development Team

- **Mj Laurito** — Lead Developer
- **Adrian Lirazan** — Backend Developer
- **James Hanzo** — Frontend Developer
- **Andrie Ravina** — QA & Documentation

---

## Getting Started

### Prerequisites

- PHP 8.2+ with extensions: `intl`, `mbstring`, `mysqli`, `gd`
- Composer
- Node.js 20+ and npm
- MySQL / MariaDB

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
```

Edit `.env` and set:

| Key | Description |
|-----|-------------|
| `CI_ENVIRONMENT` | `development` for local, `production` for deploy |
| `database.default.*` | MySQL host, database name, username, password |
| `encryption.key` | CI4 encryption key |
| `OPENROUTER_API_KEY` | OpenRouter API key for AI chatbot |
| `GEMINI_API_KEY` | Google Gemini API key |
| `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA v2 |

### 3. Database setup

```bash
# Create the database
mysql -u root -e "CREATE DATABASE mj_chatbot"

# Run migrations
php spark migrate

# Seed initial data (admin/staff accounts from .env)
php spark db:seed DatabaseSeeder
```

### 4. Run development servers

```bash
npm run dev
```

This launches all three servers concurrently:
- **Vite** (HMR) on `:5173`
- **Node.js/Express** on `:3000`
- **PHP built-in server** on `:8080`

Open **http://localhost:8080/**

### 5. Build for production

```bash
npm run build
```

Outputs versioned assets to `public/build/`.

---

## Testing

```bash
# Run all PHPUnit tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit --testsuite=App
```

Tests use SQLite in-memory database — no MySQL setup required for CI.

## Code Quality

```bash
# Lint Vue/JS files
npm run lint

# Auto-fix lint issues
npm run lint:fix

# Check formatting
npm run format:check

# Auto-format
npm run format

# PHP code style check
npm run cs-check

# PHP code style fix
npm run cs-fix
```

## Order Lifecycle Scheduler

Run automatic order lifecycle updates (payment timeouts, auto-complete, refund windows):

```bash
php spark orders:run-lifecycle
```

Recommended `.env` values:

```
orderLifecycle.paymentTimeoutMinutes = 30
orderLifecycle.autoCompleteDays = 3
orderLifecycle.refundWindowDays = 7
```

---

## Deployment

The app supports Docker and Render.com:

- `Dockerfile` / `Dockerfile.production` — Multi-stage build (Node frontend → PHP-Apache)
- `Dockerfile.render` + `render.yaml` — Render.com blueprint
- `build.sh` — Production build script
- `docker-entrypoint.sh` — Runtime env generation, migrations, uploads symlink

## CI/CD

GitHub Actions workflow (`.github/workflows/ci.yml`) runs on push to `main` and PRs:

1. **Lint** — Prettier format check
2. **Test** — PHPUnit with MySQL 8 service container
3. **Build** — Vite production build

## Project Structure

```
├── app/
│   ├── Controllers/       # Admin, Staff, Customer, Home, Auth
│   ├── Models/            # Eloquent-style CI4 models
│   ├── Services/          # CheckoutService, OrderService, EmailNotificationService
│   ├── Filters/           # Auth guards, CORS, rate limiting
│   ├── Database/
│   │   ├── Migrations/    # 21 migration files
│   │   └── Seeds/         # DatabaseSeeder, UserSeeder, OrderSeeder
│   └── Validation/        # Custom validation rules
├── resources/js/
│   ├── components/        # Vue components (Chatbot, GlassCard, etc.)
│   ├── composables/       # Vue composables (usePerformance, useRecaptcha)
│   ├── layouts/           # AdminLayout, CustomerLayout, StaffLayout
│   ├── pages/             # Inertia page components
│   └── admin-products/    # Standalone admin products Vue app
├── public/
│   ├── build/             # Vite production output (gitignored)
│   └── uploads/           # User-uploaded files
├── tests/
│   ├── unit/              # PHPUnit tests
│   └── _support/          # Test helpers and fixtures
├── .github/workflows/     # CI/CD pipeline
├── phpunit.xml.dist       # PHPUnit configuration
├── vite.config.js         # Vite configuration
└── server.js              # Node.js/Express server
```

## License

MIT
