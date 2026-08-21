# Domain primer — customs brokerage

Read this first. If you understand this page, the rest of the app makes sense.

## What the company does

Cargo Link is a licensed **Customs House Agent (CHA)** and **freight forwarder** at the Port of
Colombo. For each shipment ("job") it:

1. Files the customs declaration (**CusDec**) in Sri Lanka Customs' ASYCUDA system.
2. Pays duty, port (S.L.P.A) and shipping-line charges **on the customer's behalf**.
3. Delivers the cargo and **invoices the customer** to recover those costs **plus its own fees**.

Mostly imports; the occasional export (e.g. Ceylon Quartz). Currency: **LKR**.

## The two kinds of money (the core concept)

| Kind | Examples | Whose money | Income? |
|------|----------|-------------|---------|
| **Pass-through disbursement** | Custom duty, S.L.P.A, D/O, yard O.T, line charges | The customer's — broker just fronts it | **No** — recovered at cost |
| **Company service charge** | Documentation, handling, agency fee, transport margin | The broker's | **Yes** — real earnings |

On a typical container the disbursements can be **Rs 700k+** while the company earns only **~Rs
100k**. Mix them up and profit looks 10–50× too big. The schema tags every cost line as
`disbursement` or `service` precisely so this never happens.

## How the company keeps its books (gross method)

The GM's existing monthly Management Report uses a **gross** presentation:

```
Revenue              = total invoiced (includes recovered disbursements)
Cost of services     = job costs (the disbursements paid out)
Gross profit         = Revenue − Cost of services   (the thin real margin)
Operating profit     = Gross profit − operating expenses
Profit after leases  = Operating profit − vehicle lease payments
```

Mirror this exactly. (We may later add an optional "net / true-earnings" view — see requirements.)

## A job's life

```
Open job file → record costs (Job Per Cost) → generate invoice → receive payment
   header           disbursements + fees          bill customer      clears receivable
```

`Job profit = invoice total − total job cost`.
`Company profit = job profit − customer incentive − job commission`.

## Real example — May 2026 (from the GM's report)

| Line | LKR |
|------|-----|
| Total Revenue | 74,300,957.96 |
| Cost of Services | 71,500,269.62 |
| Gross Profit | 2,800,688.36 |
| Operating Expenses | 2,014,309.91 |
| Operating Profit | 786,378.45 |
| Lease Payments | 470,335.00 |
| Profit After Leases | 316,043.45 |
| Director drawings | 707,383.00 |
| Excess drawings over profit | 391,340.00 |
| Total debt | 27,000,000.00 |
| Active customers / jobs | 35 / 89 |

Note Revenue and Cost sit close together — that gap is almost entirely pass-through. The **director
drawings exceeding profit** is a real working-capital risk the dashboard must surface (amber tile).

## Glossary

- **CHA / Customs Broker** — licensed agent clearing cargo through customs.
- **CusDec** — customs declaration (ASYCUDA). Each cleared job has one.
- **S.L.P.A** — Sri Lanka Ports Authority (port/terminal charges).
- **D/O** — Delivery Order (line releases cargo).
- **HBL / MBL** — House / Master Bill of Lading.
- **Demurrage / detention** — penalties for cargo/containers held too long.
- **Disbursement** — money paid for the customer, recovered at cost.
- **Service charge / agency fee** — the broker's own earnings.
- **Director current account** — running record of money between the company and the director.
