
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
* **Authentication:** Laravel Sanctum (Bearer Token)
* **Architecture:** Domain-Driven Design (DDD) approach / Clean Architecture with Service & Repository Patterns
* **Database:** MySQL / PostgreSQL (utilizing pessimistic locking and database transactions)
* **Testing:** PHPUnit (Comprehensive Feature and Integration test suites)

The codebase strictly adheres to **Clean Code** principles, using explicit, English-named variables and methods, keeping business logic completely decoupled from HTTP layers.

---

## 📐 Architecture Layers & Data Flow

To ensure high cohesion, low coupling, and clear separation of concerns, the application is structured into the following decoupled layers:


```

[ HTTP Request ] ➔ [ Form Request (Validation) ] ➔ [ DTO ] ➔ [ Controller ]
│
[ Model (Rich Domain) ] ➔ [ Repository ] 🠔 [ AccountEventService (Transaction) ]
│
[ Client Response ] 🠔 [ API Resource ] 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 🠔 ┘

```

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

### 1. Preemptive Route Protection (Laravel Sanctum)
All financial endpoints are guarded by `auth:sanctum` middleware. Requests without a valid Bearer Token are intercepted and rejected with a `401 Unauthorized` status before processing any application parameters.

### 2. Form Request Validation & Anti-SQL Injection
Input validation is decoupled from controllers using custom Form Requests (`BalanceRequest` and `AccountEventRequest`).
* Parameters are validated and strictly cast (e.g., `account_id` is enforced as an `integer`).
* By using Laravel's native validation layered with Eloquent ORM, the application enforces **Parameterized Queries (Prepared Statements)** via PDO. This ensures user input is never interpreted as executable database commands, **completely neutralizing SQL Injection (SQLi) vectors**.

### 3. Output Integrity Protection (API Resources)
Data encapsulation is protected via Eloquent Resources (`BalanceResource` and `BankAccountResource`). This layer sanitizes internal database structures, hides sensitive backend information, and casts money parameters into strict primitives (like `float` decimals) before outputting the JSON response.

### 4. Custom Exception Interception
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

### State Management (Public Route / No Prefix)

* `POST /reset` - Clears the application state by restarting the database before running test suites.
* **Response:** `200 OK`



### Balance Actions (Authenticated / /api Prefix)

* `GET /api/balance?account_id={id}` - Retrieves the current balance for an account.
* **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
* **Response (Existing Account):** `200 OK` (Body: `20`)
* **Response (Non-existing Account / Invalid Input):** `404 Not Found` (Body: `0`)



### Transactional Events (Authenticated / /api Prefix)

* `POST /api/event` - Single endpoint handling dynamic financial events (`deposit`, `withdraw`, and `transfer`).
* **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`



---

## 🧪 Running Automated Tests

The comprehensive test suite is built on top of PHPUnit, ensuring data integrity, behavioral consistency under error states, structured data outputs, type casting accuracy, and log generation auditing.

### Run All Tests

To execute all automated feature and security tests inside the Dockerized container, run:

```bash
./vendor/bin/sail artisan test

```

### Run Specific Test Modules

The application divides domain actions into separate targeted suites:

* **Balance Module:** Covers happy paths, unauthenticated actions, missing accounts, and malicious SQL Injection payloads.
```bash
./vendor/bin/sail artisan test --filter=BankAccountBalanceTest

```


* **Deposit Module:** Covers balance updates, type casting validation, account initialization, missing fields, and negative value constraints.
```bash
./vendor/bin/sail artisan test --filter=AccountDepositTest

```


* **Withdraw Module:** Covers authentication verification, correct account targeting, debit calculation integrity, missing account fallbacks, and insufficient funds rejections.
```bash
./vendor/bin/sail artisan test --filter=AccountWithdrawTest

```


* **Transfer Module:** Covers multi-account happy paths, atomicity rollbacks during failures, target account isolation, missing parameter validations, and non-existent account creation thresholds.
```bash
./vendor/bin/sail artisan test --filter=AccountTransferTest

```



---

## 🚀 Postman Integration & Usage Guide

To test endpoints manually or build custom collections within Postman, configure the parameters using the specifications below.

### 🔑 Authorization Setup

1. Open your Postman request.
2. Navigate to the **Authorization** tab.
3. Select **Bearer Token** from the `Type` dropdown menu.
4. Paste your Sanctum personal access token into the `Token` input field.
5. In the **Headers** tab, ensure you add:
* `Accept: application/json`



---

### 📥 0. Reset API State (No Prefix)

* **Method:** `POST`
* **URL:** `http://localhost/reset`
* **Expected Response (`200 OK`):**
```json
"OK"

```



---

### 📥 1. Create/Deposit Event (Deposit - /api Prefix)

* **Method:** `POST`
* **URL:** `http://localhost/api/event`
* **Body Format:** `raw (JSON)`
* **Payload Example:**
```json
{
    "type": "deposit",
    "destination": "100",
    "amount": 10.00
}

```


* **Expected Response (`201 Created`):**
```json
{
    "destination": {
        "id": "100",
        "balance": 10
    }
}

```



---

### 📤 2. Withdraw Event (Withdraw - /api Prefix)

* **Method:** `POST`
* **URL:** `http://localhost/api/event`
* **Body Format:** `raw (JSON)`
* **Payload Example:**
```json
{
    "type": "withdraw",
    "origin": "100",
    "amount": 5.00
}

```


* **Expected Response (`201 Created`):**
```json
{
    "origin": {
        "id": "100",
        "balance": 5
    }
}

```


* **Expected Response for Non-Existing Account or Insufficient Funds (`404 Not Found`):**
```json
0

```



---

### 🔀 3. Transfer Event (Transfer - /api Prefix)

* **Method:** `POST`
* **URL:** `http://localhost/api/event`
* **Body Format:** `raw (JSON)`
* **Payload Example:**
```json
{
    "type": "transfer",
    "origin": "100",
    "amount": 15.00,
    "destination": "300"
}

```


* **Expected Response (`201 Created`):**
```json
{
    "origin": {
        "id": "100",
        "balance": 0
    },
    "destination": {
        "id": "300",
        "balance": 15
    }
}

```



---

### 💰 4. Get Account Balance (/api Prefix)

* **Method:** `GET`
* **URL:** `http://localhost/api/balance?account_id=100`
* **Expected Response (`200 OK`):**
```json
20

```



---

## ⚡ Getting Started (Local Setup)

### Prerequisites

Ensure you have **Docker Desktop**, **Git**, and **WSL2** (if on Windows) active. No local PHP or Composer installation is required.

### Installation Steps

```bash
# 1. Clone & enter repository
git clone [https://github.com/gabeefadel/bank_account_api.git](https://github.com/gabeefadel/bank_account_api.git)
cd bank_account_api

# 2. Setup environment variables
cp .env.example .env

# 3. Bootstrap Composer inside temporary container
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install

# 4. Spin up environment containers in the background
./vendor/bin/sail up -d

# 5. Generate cryptographic application keys
./vendor/bin/sail artisan key:generate

# 6. Run database structure schema migrations
./vendor/bin/sail artisan migrate

```

---

## 🛠️ Useful Commands

* **Stop the active environment:** `./vendor/bin/sail down`
* **Regenerate Composer class mapping (Autoload):** `./vendor/bin/sail composer dump-autoload`
* **Access interactive database terminal (Tinker):** `./vendor/bin/sail artisan tinker`

```

```