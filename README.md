# SellNow — Assessment Project

## Project Intro

SellNow is a **small, intentionally imperfect** PHP application built for an engineering assessment.

The goal of this project is **not** to deliver a production-ready marketplace, but to demonstrate:
- how I analyze an inherited codebase,
- how I prioritize problems,
- and how I improve architecture **incrementally without rewriting everything**.

---

## How to Run

### Requirements
- PHP 8.x
- Composer
- SQLite

### Setup

1. Install dependencies:
   ```bash
   composer install
   ```

2. Initialize database:
   ```bash
   sqlite3 database/database.sqlite < database/schema.sql
   ```
3. Run the application:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Open in browser:
   ```bash
   http://localhost:8000
   ```
### What Was the Task

I was given a **partially working PHP application** with:

- architectural smells,
- inconsistent database design,
- mixed responsibilities,
- and missing best practices.

**Constraints:**
- No full framework (Laravel, Symfony, etc.)
- Do not rewrite from scratch
- Show evolution, not perfection

The task was to demonstrate engineering maturity, not feature count.

---

### How I Approached and Improved the Project (Step by Step)

This project was improved incrementally, with each change committed separately for checking clarity.

### Step 1 — Audit Before Action
- Reviewed schema, controllers, and data flow
- Identified responsibility leaks and risky patterns
- Documented issues instead of immediately “fixing everything”

### Step 2 — Stabilize the Foundation
- Hardened database usage without breaking data
- Centralized database access
- Avoided destructive schema changes

### Step 3 — Reduce Controller Responsibility
- Began extracting Models (User, Product)
- Moved query logic out of controllers gradually
- Kept behavior unchanged during refactor

### Step 4 — Improve Data & Security Contracts
- Server-side validation of critical values
- Prepared statements everywhere
- Safer file upload handling
- Explicit payment provider whitelisting

Each commit is small, readable, and reversible.

## Project Structure
```pgsql
public/        → Entry point, uploads
src/
  Controllers/ → HTTP layer (thin)
  Models/      → Domain access (introduced gradually)
  Config/      → Database configuration
templates/     → Twig views
database/      → SQLite database + schema
storage/       → Runtime logs
```

---

### Intentional Limitations (Left on Purpose)

Some features were intentionally not completed to keep scope realistic:

- No role separation (seller vs buyer) yet
- Seller registration not restricted yet
- Payments are simulated
- No CSRF tokens yet

These are known trade-offs, not oversights.

### What I Would Add Next (With More Time)

- Role-based authorization (seller / buyer / admin)
- CSRF protection
- Service layer for Payments & Cart
- Database-backed order storage
- Better error handling & logging

### Notes (Important)

- No framework was used intentionally
- Code favors clarity over cleverness
- Changes show evolution, not replacement
- Commit history reflects (github) thought process

This project represents how I work in real inherited systems.