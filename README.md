# SellNow (Assessment Project)

This is a **simplified, imperfect** platform for selling digital products, built for **candidate assessment functionality**.
It contains **intentional flaws, bad practices, and security holes**.

## Project Overview

A platform where:
1. Users register and get a public profile (`/username`).
2. Users can upload products (images + digital files).
3. Buyers can browse, add to cart, and "checkout".

## Setup Instructions

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Database**:
   The project is configured to use SQLite by default.
   Initialize the database:
   ```bash
   sqlite3 database/database.sqlite < database/schema.sql
   ```
   *Note: If you switch to MySQL, update `src/Config/Database.php`.*

3. **Run Server**:
   Use PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Access**:
   http://localhost:8000


## Directory Structure

- `public/`: Web root (index.php, uploads).
- `src/`: Application classes (Controllers, Models, Config).
- `templates/`: Twig views.
- `database/`: Schema and SQLite file.

Good luck!

# SellNow – Reviewer Notes & Improvement Plan

> **Purpose of this document**
>
> This README is written for reviewers. It explains **what exists**, **what is intentionally imperfect**, and **how the project is being improved step‑by‑step without breaking behavior**.

---

## 1. Project Overview

SellNow is a lightweight PHP (MVC-style) marketplace application built without a full framework.

**Core features:**

* User authentication (login / register)
* Product creation & listing
* Cart & checkout flow
* Public seller profile pages
* SQLite database for assessment simplicity

The goal of this project is **clarity, correctness, and incremental improvement**, not feature completeness.

---

## 2. Architecture (Current State)

```
public/        → Web root (index.php, uploads)
src/
  Controllers/ → HTTP controllers (thin, request-driven)
  Config/      → Database connection
  Models/      → (being introduced gradually)
templates/     → Twig views (presentation only)
database/      → schema.sql + SQLite file
storage/       → logs (runtime data)
```

### Design principles followed

* Controllers are intentionally **thin**
* No hidden magic or framework assumptions
* Explicit SQL (easy to audit)
* Incremental refactoring instead of rewrite

---

## 3. Intentional Imperfections (For Review)

This project **intentionally started imperfect** so improvements can be demonstrated clearly.

Examples:

* Mixed casing in database schema
* Missing foreign key constraints (initially)
* Controllers performing some query logic
* No role separation (seller vs buyer) yet

Each issue is being addressed **one step at a time**, with commits that are easy to review.

---

## 4. Database Strategy

* SQLite is used for portability during assessment
* `schema.sql` represents the **baseline schema**
* Schema hardening is done **without breaking existing data**

**Rules:**

* No destructive migrations
* No data loss
* Schema changes are documented before applied

---

## 5. Authentication & Users

Current behavior:

* Any registered user can log in
* No role distinction yet (seller/buyer)

Planned (incremental):

* Introduce `role` column (`buyer`, `seller`, `admin`)
* Seller registration not publicly open
* Only sellers can create products

This is deferred intentionally to keep review scope focused.

---

## 6. Product & Checkout Flow

### Current flow

1. User registers / logs in
2. Authenticated user can create products
3. Products appear on public seller profiles
4. Cart → checkout → payment simulation

### Notes

* Checkout totals are recalculated server-side
* Payment providers are whitelisted constants
* Transactions are logged to file (not DB yet)

---

## 7. Security Considerations

Implemented:

* Prepared statements (PDO)
* Password hashing with backward compatibility
* File upload size & MIME checks
* Server-side price calculation

Planned:

* CSRF protection
* Role-based authorization
* File storage abstraction

---

## 8. Refactoring Roadmap

The refactor follows this **strict order**:

1. ✅ Database hardening (non-breaking)
2. 🔄 Extract read-only Models (User, Product)
3. 🔄 Introduce role-based authorization
4. 🔄 Service extraction (Cart / Payment)
5. 🔄 Improve error handling

Each step is committed separately for reviewer clarity.

---

## 9. Reviewer Notes

* No framework was used intentionally
* Code favors **explicitness over abstraction**
* Every change is incremental and reversible
* Comments explain *why*, not *what*

---

## 10. How to Review This Project

Recommended order:

1. `schema.sql`
2. `Database.php`
3. Controllers (Auth → Product → Checkout → Public)
4. Commits related to model extraction
5. This README

---

### Conclusion

## Project Manifesto
1. The Audit (Honest Assessment)

This codebase was intentionally inherited in an imperfect state.
### Key issues identified early:

Mixed responsibilities (controllers handling SQL, validation, and logic)

Inconsistent database schema (naming, missing constraints)

No clear domain boundaries (no Models or Services)

Business rules scattered across controllers

These were treated as signals, not mistakes.

2. Priority Matrix (Why X before Y)

Refactoring was guided by impact vs risk:

Structure before features
→ Introduced Models to reduce controller responsibility.

Safety before abstraction
→ Read-only models first, no breaking changes.

Reviewer clarity over completeness
→ Chose incremental, visible improvements instead of a full rewrite.

Some issues (roles, permissions, payments) were intentionally deferred to keep scope realistic.

3. Trade-offs (Conscious Decisions)

Did not introduce a framework → to keep changes explainable.

Kept some duplication temporarily → to avoid risky refactors.

Used sessions instead of a full auth system → simplicity over completeness.

The goal was code that explains itself, not perfection.

