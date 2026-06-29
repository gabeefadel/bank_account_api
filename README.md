
# Bank Account API

A production-ready, highly structured RESTful API designed to manage essential banking operations. This project implements clean architectural patterns to ensure scalability, robust data consistency, and strict business rule validation.

---

## 🚀 Core Features

* **Account Management:** Handle secure operations for bank accounts.
* **Deposits:** Safely increase account balances or initialize new accounts with strict input validation.
* **Withdrawals:** Process debits while ensuring sufficient funds, avoiding overdraft conditions, and updating logs.
* **Transfers:** Move values atomically between accounts, guaranteeing transaction safety (ACID principles).
* **Balance & Statements:** Fetch real-time balances and transactional history under strict security constraints.

---

## 🛠️ Tech Stack & Architecture

* **Framework:** PHP 8.3 (Laravel 11 / Laravel Sail)
* **Architecture:** Domain-Driven Design (DDD) approach / Clean Architecture with Service & Repository Patterns
* **Database:** MySQL / PostgreSQL (utilizing pessimistic locking and database transactions)
* **Testing:** PHPUnit (Comprehensive Feature and Integration test suites)

The codebase strictly adheres to **Clean Code** principles, using explicit, English-named variables and methods, keeping business logic completely decoupled from HTTP layers.

---

## 📐 Architecture Layers & Data Flow

To ensure high cohesion, low coupling, and clear separation of concerns, the application is structured into the following decoupled layers:

### 1. HTTP Layer (Controllers & Form Requests)

* **Form Requests (`app/Http/Requests/`):** Intercept incoming queries or JSON payloads. They handle data sanitation, enforce data types, block malicious inputs, and control custom failure overrides.
* **Controllers (`app/Http/Controllers/`):** Act strictly as thin orchestrators. They do not contain business logic or query databases directly; they map validated request data into a DTO, trigger the appropriate Service, and inject the result into an API Resource.

### 2. Application Layer (DTOs & Enums)

* **Data Transfer Objects (`app/DTOs/`):** Immutable, type-safe structures (`AccountEventDTO`) that carry sanitized parameters deep into the domain layer, shielding business logic from raw HTTP payloads.
* **Enums (`app/Enums/`):** Enforce strict type safety for application events (`AccountEventType`), removing magic numbers or string matching anomalies across the codebase.

### 3. Domain Layer (Services & Rich Models)

* **Domain Services (`app/Services/`):** Coordinate execution flows and enforce database isolation boundaries. The `AccountEventService` wraps operations inside explicit database transactions, making them ACID compliant.
* **Rich Domain Models (`app/Models/`):** The core business rules live here. Unlike anemic models, the `BankAccount` model encapsulates its own structural states and self-validates adjustments via native domain methods like `credit()` and `debit()`.

### 4. Persistence Layer (Repositories)

* **Repositories (`app/Repositories/`):** Encapsulate database infrastructure mechanisms. The `BankAccountRepository` manages database records and implements pessimistic locking via `lockForUpdate()` to prevent race conditions during concurrent financial events.

---

## 🛡️ Security Architecture & Anti-SQLi

This API implements a multi-layered security approach to protect financial data and avoid common infrastructure vulnerabilities:

### 1. Form Request Validation & Anti-SQL Injection

Input validation is decoupled from controllers using custom Form Requests (`BalanceRequest` and `AccountEventRequest`).

* Parameters are validated and strictly cast.
* By using Laravel's native validation layered with Eloquent ORM, the application enforces **Parameterized Queries (Prepared Statements)** via PDO. This ensures user input is never interpreted as executable database commands, **completely neutralizing SQL Injection (SQLi) vectors**.

### 2. Output Integrity Protection (API Resources)

Data encapsulation is protected via Eloquent Resources (`BalanceResource` and `BankAccountResource`). This layer sanitizes internal database structures and casts money parameters into strict primitives (like `float` decimals) before outputting the JSON response.

### 3. Custom Exception Interception

To meet strict interface specifications without breaking compliance, validation exceptions and missing database records are unified under custom interceptors, forcing safe fallback structures (`404 Not Found` with a body of `0`) instead of exposing infrastructure logs to the client.

---

## 📝 Auditorial & Logging Specification

Every operational event triggers strict auditorial documentation handled by the dedicated `TransactionLogService`. Logs are structured as single-line **JSON strings in English** to facilitate external monitoring integration.

### Success Log Format

Triggered automatically when a transaction passes all business rules and commits to the database:

```json
{"status":"success","event":"transfer","message":"Bank transfer from account 100 in the amount of 15.00 to account 300 on date 2026-06-28 21:30:00","timestamp":"2026-06-28 21:30:00","details":{"amount":15,"origin":"100","destination":"300"}}

```

### Failure Log Format

Triggered outside the transaction boundary if a rule fails, ensuring the attempt is archived while database changes safely roll back:

```json
{"status":"failed","event":"withdraw","message":"Failed withdrawal of 50.00 from account 100 on date 2026-06-28 21:31:12. Reason: Insufficient funds.","timestamp":"2026-06-28 21:31:12","reason":"Insufficient funds.","details":{"amount":50,"origin":"100","destination":null}}

```

---

## 📖 API Endpoints & Event Specification

### Reset Application State

* `POST /reset` - Clears the application state by restarting the database.
* **Response:** `200 OK`

### Balance Actions

* `GET /balance?account_id={id}` - Retrieves the current balance for an account.
* **Response (Existing Account):** `200 OK` (Body: `20`)
* **Response (Non-existing Account / Invalid Input):** `404 Not Found` (Body: `0`)

### Transactional Events

* `POST /event` - Single endpoint handling dynamic financial events (`deposit`, `withdraw`, and `transfer`).

---

## 🧪 Running Automated Tests

The comprehensive test suite is built on top of PHPUnit, ensuring data integrity, behavioral consistency under error states, structured data outputs, type casting accuracy, and log generation auditing.

### Run All Tests

```bash
./vendor/bin/sail artisan test

```

---

## 📥 Payloads Examples

### 1. Deposit Event

* **URL:** `POST http://localhost/event`
* **Payload:**

```json
{"type":"deposit","destination":"100","amount":10}

```

### 2. Withdraw Event

* **URL:** `POST http://localhost/event`
* **Payload:**

```json
{"type":"withdraw","origin":"100","amount":5}

```

### 3. Transfer Event

* **URL:** `POST http://localhost/event`
* **Payload:**

```json
{"type":"transfer","origin":"100","amount":15,"destination":"300"}

```

---

## ⚡ Getting Started (Local Setup)

### Installation Steps

```bash
# 1. Clone & enter repository
git clone https://github.com/gabeefadel/bank_account_api.git
cd bank_account_api

# 2. Setup environment variables
cp .env.example .env

# 3. Bootstrap dependencies
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install

# 4. Spin up environment
./vendor/bin/sail up -d

# 5. Migrate database
./vendor/bin/sail artisan migrate

```