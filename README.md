# Novera ERP

Multi-company ERP for any business — Laravel 11 API + Vue 3 SPA, Docker-ready, with SaaS billing.

## Features

- **Modules** — Catalog, Inventory, Production, Sales, CRM, Procurement, Logistics, Finance, HR, Reports
- **Multi-company SaaS** — Organizations/workspaces with per-company data isolation (`organization_id` + global scopes), member roles, trial periods
- **Billing** — Stripe via Laravel Cashier (plans in `config/saas.php`, checkout + customer portal, `stripe/webhook` included)
- **UI** — Tailwind + dark mode, tables with filters/sort/search, bulk select + CSV export, kanban boards (sales, production, shipments, leads), stat cards, Business Hub home
- **Platform** — Sanctum auth, Spatie roles/permissions, Scout + Meilisearch search, Horizon queues, Excel/PDF export, activity log, backups, media library

## Tech Stack

| Layer    | Tech                                            |
|----------|-------------------------------------------------|
| Backend  | PHP 8.3+, Laravel 11, Sanctum, Cashier (Stripe) |
| Frontend | Vue 3, Vite, Pinia, Vue Router, Tailwind        |
| Data     | MySQL 8, Redis 7, Meilisearch                   |
| Tests    | Pest (backend), Vitest (frontend)               |

## Prerequisites

- PHP 8.3+, Composer, Node 20+, npm
- Docker + Docker Compose (for full stack), or local MySQL/Redis
- Stripe account (only for live billing)

## Quick Start (local, fastest)

```bash
cp .env.example .env
composer install
cd frontend && npm install && cd ..
php artisan key:generate
php artisan migrate --seed
./start.sh
```

- Frontend: http://localhost:3000
- API: http://localhost:8000
- Login: `admin@feltandyarn.com` / `password` (demo company: Felt and Yarn)

## Quick Start (Docker, full stack)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

| Service    | URL / Port              |
|------------|-------------------------|
| App (nginx)| http://localhost:80     |
| Vite       | http://localhost:5173   |
| MySQL      | localhost:3306          |
| Redis      | localhost:6379          |
| Meilisearch| http://localhost:7700   |
| Mailpit    | http://localhost:8025   |

## Environment Keys

Required in `.env`:

```bash
APP_NAME=Novera
APP_URL=http://localhost:8000
APP_KEY=              # php artisan key:generate
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=erp
DB_USERNAME=erp_user
DB_PASSWORD=secret_password
```

SaaS / billing (see `.env.example`):

```bash
SAAS_TRIAL_DAYS=14
SAAS_REQUIRE_SUBSCRIPTION=false
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_PRICE_STARTER_MONTHLY=
STRIPE_PRICE_STARTER_YEARLY=
STRIPE_PRICE_GROWTH_MONTHLY=
STRIPE_PRICE_GROWTH_YEARLY=
STRIPE_PRICE_ENTERPRISE_MONTHLY=
STRIPE_PRICE_ENTERPRISE_YEARLY=
CASHIER_MODEL=App\Models\Organization
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=
```

## Useful Commands

```bash
make setup        # first-time setup
make dev          # docker up
make migrate      # run migrations
make seed         # fresh migrate + seed
make test         # backend tests (Pest)
make test-frontend# frontend tests (Vitest)
make lint         # Pint check
php artisan test tests/Feature/Saas/SaasTenancyTest.php
cd frontend && npm run build   # production frontend -> frontend/dist
```

## Multi-Company Model

- `organizations` + `organization_user` (role: owner/admin/member/viewer), `users.current_organization_id`
- Every domain table has nullable `organization_id`; `BelongsToOrganization` trait scopes reads + fills writes from `CurrentOrganization` (user's current org, overridable via `X-Organization-ID` header)
- Signup (`POST /api/v1/auth/register` + `organization_name`) creates org with trial; login returns `organizations` + `permissions`
- Enforce payment with `subscribed` middleware (`SAAS_REQUIRE_SUBSCRIPTION=true`)

## API Overview

Base: `/api/v1` (Sanctum). Key routes:

```text
POST auth/login, auth/register
GET  auth/me
GET/POST organizations, POST organizations/{id}/switch
GET  billing, POST billing/checkout, POST billing/portal
CRUD products, categories, raw-materials, warehouses, stock-movements
CRUD production-orders, artisans, qc-inspections
CRUD sales-orders, wholesale-accounts, leads, quotes
CRUD purchase-orders, suppliers, shipments, invoices, expenses
CRUD payroll-runs, piece-rates
GET  reports/dashboard, reports/sales-summary
POST stripe/webhook (Cashier)
```

## Frontend

```text
frontend/src/
  pages/       # Dashboard (Business Hub) + 30 module pages
  components/  # DataTable, KanbanBoard, FilterBar, BulkBar, StatStrip, OrgSwitcher, ...
  stores/      # auth, organization, catalog, inventory, production, sales, crm, ...
  composables/ # useList (search/filter/sort/paginate/select/CSV export)
  lib/         # axios (token + X-Organization-ID), paginated
  router/      # 34 routes, auth + permission guards
```

## Deployment (single VPS, easiest)

1. Provision Ubuntu VPS, install Docker
2. Clone repo, `cp .env.example .env`, set `APP_URL`, `DB_*`, Stripe keys, `APP_ENV=production`, `APP_DEBUG=false`
3. `docker compose up -d --build`
4. `docker compose exec app php artisan key:generate --force`
5. `docker compose exec app php artisan migrate --force`
6. Point DNS at the server; terminate TLS in nginx (see `docker/nginx/`)

## Troubleshooting

- Empty tables → sign in as `admin@feltandyarn.com`, pick `Felt and Yarn` in the sidebar switcher (each company sees only its data)
- API 500 → `php artisan config:clear`, check `storage/logs/laravel.log`
- Vite can't reach API → ensure API on `:8000` (dev proxy in `vite.config.ts` maps `/api` → `:8000`)
- Fresh start → `php artisan migrate:fresh --seed`

## License

MIT
