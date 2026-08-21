# Requirements summary

Condensed from the SRS. Priority: M = must, S = should, C = could.

## Modules

### Customers (M)
CRUD master with type, credit days, credit limit. Show outstanding balance & job count. Deactivate,
never hard-delete if jobs/invoices exist.

### Job file (M)
Create/edit jobs from the Job File Opening fields (see DATA_MODEL). Auto-build `job_no`
(`IMP/SEA/26/03/3048`). Compute job profit & company profit. Status open→cleared→invoiced→closed.
List/filter by customer, month, mode, direction.

### Job costing — Job Per Cost (M)
Capture cost lines from `charge_types`, each tagged `disbursement` or `service`. Record advances
(receipt no) and IOUs. Auto-total disbursements, service charges and total cost. **Keep the split
intact — true earnings = service total.**

### Invoicing & receivables (M)
Generate invoice from a job (carry invoice_no, date, customer, CusDec, HBL, job_no). Lines +
subtotal − advance = balance due. PDF on letterhead with standard terms. Record payments. Ageing
report (current/30/60/90+) by customer; flag overdue vs credit days. Show fronted disbursements
outstanding separately (S).

### Expenses (M)
Record against `expense_categories`. Group by category & month for the report.

### Vehicle leases (M)
Vehicles by reg_no + monthly rental. Record monthly payments; total = report's lease line.

### Director current account (M)
Record txns (date, description, debit, credit). Analysis: business profit after leases, director
drawings, excess drawings over profit. Warning when drawings exceed profit (S). `gm` only.

### Creditors / debt (M)
Register (name, type, outstanding). Total debt + breakdown. `gm` only.

### Management report & dashboard (M) — the headline
For a selected period show: Revenue, Cost of Services, Gross Profit, Operating Expenses, Operating
Profit, Lease Payments, Profit After Leases. Financial summary bar chart (Revenue/Cost/Gross). 
Import vs Export split (S). Director analysis + total debt alongside. Export to PDF (M).

### Login → dashboard (M)
On login, land on the dashboard scoped to **current month so far** (Asia/Colombo). Tiles:
- P&L MTD: Revenue, Cost of services, Gross profit, Operating expenses, Operating profit
- Jobs this month (sea/air), Active customers (import/export)
- Receivables outstanding (+ overdue in red)
- Total debt
- Drawings vs profit (amber when drawings > profit)
- Revenue/Cost/Gross bar chart
- Top customers by profit; overdue invoices to chase
Month picker switches to past months using the same logic.

### Users & access (M)
Login; roles `gm` (full) and `staff` (operational, sensitive totals hidden). Audit key records (S).

## Supporting reports
Customer-/Job-wise profit (Date, Job No, Customer, Amount, Cost, Gross Profit; grouped by
customer) · Job Per Cost printout · Receivables ageing · Expenses by category/month · Lease
schedule · Director current-account statement.

## Open questions (confirm with GM)
1. Add an optional "net / true-earnings" view alongside the gross view?
2. Any USD invoicing (freight) → multi-currency?
3. VAT/SSCL on service fees — calculate & show on invoices?
4. Exact difference between Customer Incentive and Job Commission?
5. Which figures must be hidden from `staff`?
6. Confirm job-number format & auto-numbering.
7. Opening balances (receivables, creditors, director account) at go-live.

## Out of scope (this phase)
ASYCUDA/ASYHUB integration · bank import · statutory VAT filing · payroll · customer portal.
