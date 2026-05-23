# Hariman Nexus

**A modern business operations platform** built with Laravel 12 — covering invoicing, quotations, payments, client management, and financial reporting. Designed to grow into a full ERP system.

🔗 **Live Demo:** [nexus.hariman.co.in](https://nexus.hariman.co.in)

---

## What it does

Hariman Nexus handles the full sales and billing lifecycle for service and product businesses:

- **Quotations** — create, send, approve, reject, or convert to invoice with one click
- **Invoices** — full lifecycle from draft to paid, with partial payment support and overdue tracking
- **Payment recording** — multiple payment methods, receipt uploads, automatic balance reconciliation
- **Client & project management** — with per-client tax configuration and status tracking
- **Products & services catalogue** — reusable line items for fast invoice creation
- **Financial reports** — revenue summaries, payment status breakdowns, Excel export
- **Email dispatch** — send invoices, payment reminders, and overdue notices with PDF attachments
- **Role-based access control** — 5 roles (Super Admin, Admin, Accountant, Operations Staff, Viewer) with granular permissions
- **Activity logging** — full audit trail of every action across the system
- **Multi-currency support** — configurable currencies with default selection
- **Company settings** — logo, payment details, tax registration, bank information

---

## Tech stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Database | SQLite (dev) / MySQL or PostgreSQL (production) |
| PDF generation | barryvdh/laravel-dompdf |
| Excel export | maatwebsite/laravel-excel |
| Permissions | spatie/laravel-permission |
| Auth | Laravel Breeze |
| Build tools | Vite |

---

## Architecture highlights

- **RBAC with Spatie Permissions** — granular permission system across 36 defined permissions, mapped to 5 roles
- **Financial integrity** — all monetary calculations use `decimal:2` casts, DB transactions wrap multi-step writes, and sequence numbers use `lockForUpdate()` to prevent race conditions
- **Quotation → Invoice conversion** — approved quotations can be converted to invoices with line items pre-filled
- **Soft deletes** — invoices, quotations, and payments use soft deletes to preserve audit history
- **Demo mode** — baseline snapshot/restore system for live demo environments with automatic data purging

---

## Getting started

```bash
# Clone the repository
git clone https://github.com/ashishlearn1517/hariman-nexus.git
cd hariman-nexus

# Install dependencies and set up
composer run setup

# Start development server
composer run dev
```

Then visit `http://localhost:8000`

**Requirements:** PHP 8.2+, Composer, Node.js 18+

---

## Roadmap

The platform is being actively developed toward full ERP capability:

- [ ] Purchase orders and vendor management
- [ ] Inventory and stock tracking
- [ ] Chart of accounts and double-entry bookkeeping
- [ ] HR and payroll module
- [ ] Client self-service portal
- [ ] Budget management and forecasting
- [ ] Payment gateway integration (Stripe / Paystack / Flutterwave)
- [ ] Multi-tenancy (SaaS mode)
- [ ] REST API for mobile and third-party integrations

---

## Security

This application handles financial data. Security measures in place:

- SMTP credentials encrypted at rest using Laravel's `encrypted` cast
- CSRF protection on all forms
- Permission checks on every route using Laravel Gates
- Rate limiting on authentication endpoints
- Input validation with `Rule::exists()` for all foreign key references
- File upload MIME type validation

To report a security vulnerability, please contact via GitHub Issues.

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

*Built by Ashish — 17 years of enterprise application development, now modernising onto Laravel and cloud-native platforms.*
