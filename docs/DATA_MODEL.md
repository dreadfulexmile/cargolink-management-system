# Data model

Money columns are `DECIMAL(15,2)`. All tables get `timestamps()` unless noted.

## Entities

### People & access
- **users** — name, email, password, `theme` enum(system/light/dark) default system. Roles via
  spatie (`gm`, `staff`).
- **customers** — name, address, contact_person, phone, email, `type`(import/export) default
  import, credit_days (default 30), credit_limit (nullable), is_active.

### Jobs & costing
- **jobs** — `job_no` (unique), `mode`(sea/air), `direction`(import/export), customer_id,
  salesperson_id (nullable → users), vessel_flight, vessel_date, port_loading, port_discharge,
  mbl_no, hbl_no, cargo_description, container_no, quantity (e.g. "2 × 40"), cusdec_no,
  `status`(open/cleared/invoiced/closed) default open, customer_incentive (default 0),
  job_commission (default 0), remarks.
- **charge_types** — name, `kind`(disbursement/service), sort_order, is_active. *(seeded; see below)*
- **job_cost_lines** — job_id, charge_type_id (nullable), `kind`(disbursement/service),
  description (nullable, for free-text), amount.
- **job_advances** — job_id, `type`(advance/iou), amount, receipt_no, name (for IOU), received_on.

### Invoicing & receivables
- **invoices** — `invoice_no`(unique), job_id, customer_id, invoice_date, subtotal, advance_total,
  balance_due, `status`(unpaid/part_paid/paid) default unpaid.
- **invoice_lines** — invoice_id, description, amount, kind.
- **payments** — invoice_id, amount, `method`(cheque/bank/cash) default cheque, reference, paid_on.

### Company accounts
- **expense_categories** — `group`, name, is_active. *(seeded; see below)*
- **expenses** — expense_category_id, expense_date, amount, payee, note.
- **vehicles** — `reg_no`(unique), has_lease, monthly_rental, lease_due_day (1–28), lease_term_months, is_active.
  Auto-deactivates once every scheduled lease payment is paid.
- **lease_payments** — vehicle_id, period (date = month), due_date, amount, paid_on. Full schedule
  (one row per month, `lease_term_months` rows) is generated when the vehicle's lease is set up.
- **director_transactions** — txn_date, description, debit (default 0), credit (default 0).
- **creditors** — name, `type`(individual/bank_facility/gold_loan), outstanding, note,
  monthly_repayment, repayment_due_day (1–28), repayment_term_months. `outstanding` auto-decrements
  as scheduled repayments are marked paid, instead of being maintained by hand.
- **creditor_payments** — creditor_id, period (date = month), due_date, amount, paid_on. Full
  schedule generated the same way as vehicle leases when a repayment plan is set up.

## Relationships

- Customer → hasMany Job, Invoice
- Job → belongsTo Customer, (salesperson) User; hasMany JobCostLine, JobAdvance; hasOne Invoice
- Invoice → belongsTo Job, Customer; hasMany InvoiceLine, Payment
- Vehicle → hasMany LeasePayment

## Derived figures (compute, don't store as truth)

```
total_job_cost   = sum(job_cost_lines.amount)
total_disburse   = sum(job_cost_lines where kind = disbursement)
total_service    = sum(job_cost_lines where kind = service)   ← real earnings
job_profit       = invoice.subtotal − total_job_cost
company_profit   = job_profit − customer_incentive − job_commission
balance_due      = invoice.subtotal − advance_total − sum(payments.amount)
```

## Seed data

### charge_types (kind)
**disbursement:** D/O & A.W.B, Custom Duty, S.L.P.A, Custom O.T, R.C.T/Gryline, Yard O.T, Weight
Bridge, Quarantine, Pyto, CO, SLS, Custom Penalty, Custom Computer Fee.
**service:** Documentation, Handling, Agency Fee, Transport, Custom Examination Expenses, Valuation
Department, R.C.T/Gryline Handling, Labour & Fork Lift, Transport Demurrages, Officer/Doctor
Transport, Additional Expenses for Custom.

### expense_categories (group → names)
- Financial Expenses: Bank Charges, Loan Interest, Overdraft Interest
- Legal Charges: Legal Charges
- Marketing Expenses: Business Promotion, Donation, Sales Commission, Travelling Expenses
- Overhead Expenses: Fuel, Lorry Expenses, Office Equipment Maintenance, Office Rent, Office Service
  Charge, Subscription Fee, Telephone (SLT), Water, Welfare
- Salary Expenses: Director Salary, EPF, ETF, Staff Salary, Staff Welfare
- Secretarial Expenses: Secretarial Expenses
- Vehicle Cost: Vehicle Maintenance
