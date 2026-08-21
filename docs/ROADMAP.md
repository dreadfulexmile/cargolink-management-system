# Build roadmap

Phased so each stage is usable on its own. Check items off as you go.

## Phase 0 — Setup
- [ ] Laravel 11 app, `.env` (MySQL, `APP_TIMEZONE=Asia/Colombo`)
- [ ] Install Livewire, spatie/laravel-permission, dompdf, maatwebsite/excel
- [ ] Breeze (Blade) auth; Tailwind `darkMode: 'class'`
- [ ] Roles `gm`, `staff`; `users.theme` column; theme toggle in header
- [ ] All migrations (see DATA_MODEL.md) + `ChargeTypeSeeder`, `ExpenseCategorySeeder`

## Phase 1 — Jobs & costing (the core)
- [ ] Customer CRUD
- [ ] Job file opening screen (auto job_no, computed profit fields)
- [ ] Job Per Cost screen — disbursement vs service split, advances, IOUs, totals
- [ ] `JobCosting` service (totals, job profit, company profit)

## Phase 2 — Money in
- [ ] Generate invoice from job; invoice lines; subtotal/advance/balance
- [ ] Record payments; update balance & status
- [ ] Invoice PDF on letterhead
- [ ] `Receivables` service + ageing report (current/30/60/90+)

## Phase 3 — Reporting & dashboard
- [ ] Expense entry against categories
- [ ] Vehicles & lease payments
- [ ] `ReportEngine` (period P&L: revenue, cost, gross, opex, operating profit, leases)
- [ ] Dashboard (current month so far) — tiles + Chart.js bar chart + lists
- [ ] Management Report PDF; Import vs Export split

## Phase 4 — Owner views & polish
- [ ] Director current account + drawings-vs-profit analysis (gm only)
- [ ] Creditors / debt register + total (gm only)
- [ ] Customer-/Job-wise profit report; Excel exports
- [ ] Audit trail on key records
- [ ] Nightly `mysqldump` backup via scheduler/cron

## Definition of done (per feature)
Mirrors the paper form / report · numbers reconcile to line items · works in light **and** dark
mode · `staff` cannot see gm-only figures · money via bcmath, formatted as `Rs`.
