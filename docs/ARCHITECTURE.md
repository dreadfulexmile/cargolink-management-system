# Architecture & conventions

## Tiers

```
Clients (GM phone/tablet, office PCs — browser)
        │
Laravel app  ── Blade + Livewire 3 + Tailwind ── Auth.js? no → Breeze + spatie roles
        │
Domain services ── JobCosting · ReportEngine · ReceivablesAging
        │
Eloquent ORM
        │
MySQL / MariaDB        (phpMyAdmin for admin; nightly mysqldump backup)
```

## Stack rationale

A single Laravel app (Blade + Livewire) keeps everything in PHP — no separate JS frontend — which
suits a small team and **shared/cPanel hosting**. Livewire gives reactive dashboards and CRUD
without a SPA.

## Shared-hosting notes (important)

Shared cPanel hosting has no always-on processes. Plan for it:

1. **Document root** must point at `/public` (cPanel “Set document root”, or place `public`
   contents in `public_html` with the app one level up).
2. **Scheduler & queues:** one cron entry `* * * * * php artisan schedule:run`. Heavy work (PDF,
   backups) runs via the **database** queue driver, dispatched by the scheduler. Light work runs
   synchronously.
3. **PHP 8.2+** with `bcmath`, `mbstring`, `pdo_mysql` enabled (cPanel “Select PHP Version”).
4. **Backups:** scheduled `mysqldump` to a separate location; don't rely on host backups.

## Money & locale

- `DECIMAL(15,2)`, LKR, **bcmath** for arithmetic. Never float.
- `APP_TIMEZONE=Asia/Colombo`. Current-month scope = `now()->startOfMonth()` → `now()`.
- Format for display with grouping, e.g. `Rs 226,318`.

## Theme toggle (dark / light)

- Tailwind `darkMode: 'class'`. A `dark` class on `<html>` flips the UI.
- Header toggle (sun/moon). Apply instantly via `localStorage` to avoid flash on load, and persist
  to `users.theme` (`system` / `light` / `dark`, default `system`) so it follows the user across
  devices.
- Every screen must be readable in both modes — use semantic Tailwind classes, not hardcoded
  colors.

## Access control

- spatie/laravel-permission. Roles:
  - `gm` — everything (director account, company-wide profit, debt, config).
  - `staff` — customers, jobs, costs, invoices, payments, expenses; sensitive totals hidden.
- Audit key financial records (who created/changed).

## Suggested structure

```
app/
  Livewire/        Dashboard, Jobs/, Invoices/, Expenses/, Reports/
  Models/          Customer, Job, JobCostLine, Invoice, Payment, ...
  Services/        JobCosting, ReportEngine, Receivables
database/
  migrations/      one per table (see DATA_MODEL.md)
  seeders/         ChargeTypeSeeder, ExpenseCategorySeeder
resources/views/   Blade + Livewire templates
```
