
# Bank Account API

A production-ready, highly structured RESTful API designed to manage essential banking operations. This project implements clean architectural patterns to ensure scalability, robust data consistency, and strict business rule validation.

---

## 🚀 Core Features

* **Account Management:** Handle secure operations for bank accounts.
* **Deposits:** Safely increase account balances with strict input validation.
* **Withdrawals:** Process debits while ensuring sufficient funds and enforcing account limits.
* **Transfers:** Atomically move values between accounts, guaranteeing transaction safety (ACID principles).
* **Balance & Statements:** Fetch real-time balances and transactional history under strict security constraints.

---

## 🛠️ Tech Stack & Architecture

* **Framework:** PHP 8.3 (Laravel 11 / Laravel Sail)
* **Authentication:** Laravel Sanctum (Bearer Token)
* **Architecture:** Service Pattern / Clean Architecture approach
* **Database:** MySQL / PostgreSQL (handling atomic transactions)
* **Testing:** PHPUnit (Feature and Integration tests)

The codebase strictly adheres to **Clean Code** principles, using explicit, English-named variables and methods, keeping business logic completely decoupled from HTTP layers.

---

## 🛡️ Security Architecture & Anti-SQLi

This API implements a multi-layered security approach to protect financial data and avoid common infrastructure vulnerabilities:

### 1. Preemptive Route Protection (Laravel Sanctum)
All financial endpoints (including balance checks and transactional events) are guarded by `auth:sanctum` middleware. Requests without a valid Bearer Token are intercepted and rejected with a `401 Unauthorized` status before processing any application parameters.

### 2. Form Request Validation & Anti-SQL Injection
Input validation is decoupled from controllers using custom Form Requests (`BalanceRequest` and `AccountEventRequest`). 
* Parameters are validated and strictly cast (e.g., `account_id` is enforced as an `integer`).
* By using Laravel's native validation layered with Eloquent ORM (`BankAccount::findOrFail`), the application enforces **Parameterized Queries (Prepared Statements)** via PDO. This ensures user input is never interpreted as executable database commands, **completely neutralizing SQL Injection (SQLi) vectors**.

### 3. Output Integrity Protection (API Resources)
Data encapsulation is protected via Eloquent Resources (`BalanceResource` and `BankAccountResource`). This layer sanitizes internal database structures, hides sensitive backend information, and casts money parameters into strict primitives (like `float` decimals) before outputting the JSON response.

### 4. Custom Exception Interception
To meet strict interface specifications without breaking compliance, validation exceptions and missing database records are unified under custom interceptors, forcing safe fallback structures (`404 Not Found` with a body of `0`) instead of exposing infrastructure logs to the client.

---

## 📐 Key Design Patterns Implemented

### 1. Service Pattern
All business rules (e.g., *“Can this account withdraw this amount?”*) are encapsulated inside dedicated Service classes under `app/Services/`, ensuring controllers remain thin, clean, and testable.

### 2. Polymorphic Event Handler
The `/api/event` architecture utilizes a dynamic routing pattern. Based on the payload's `"type"` field, the request is cleanly dispatched to its respective domain Service, maintaining high cohesion and separation of concerns.

### 3. Database Transactions (Atomic Operations)
For the `transfer` feature, the API guarantees that money deducted from the origin account is successfully deposited into the destination account. If any step fails, the entire operation safely rolls back.

---

## 📖 API Endpoints & Event Specification

### State Management
* `POST /reset` - Resets the state of the application before running test suites (Public endpoint).
  * **Response:** `200 OK`

### Balance Actions (Authenticated)
* `GET /api/balance?account_id={id}` - Retrieves the current balance for an account.
  * **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
  * **Response (Existing Account):** `200 OK` (Body: `20`)
  * **Response (Non-existing Account / Invalid Input):** `404 Not Found` (Body: `0`)

---

### Transactional Events (Authenticated)
* `POST /api/event` - Single endpoint handling dynamic financial events (`deposit`, `withdraw`, and `transfer`).
  * **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`

#### 1. Deposit
Used to initialize an account or add funds to an existing one.
* **Payload Example:**
```json
{
  "type": "deposit",
  "destination": "100",
  "amount": 10
}

```

* **Response (`201 Created`):**

```json
{
  "destination": {
    "id": "100",
    "balance": 20
  }
}

```

#### 2. Withdraw

Executes a debit operation on an existing account.

* **Payload Example:**

```json
{
  "type": "withdraw",
  "origin": "100",
  "amount": 5
}

```

* **Response (`201 Created`):**

```json
{
  "origin": {
    "id": "100",
    "balance": 15
  }
}

```

* **Response (Non-existing Account):** `404 Not Found` (Body: `0`)

#### 3. Transfer

Atomically moves values between an origin account and a destination account.

* **Payload Example:**

```json
{
  "type": "transfer",
  "origin": "100",
  "amount": 15,
  "destination": "300"
}

```

* **Response (`201 Created`):**

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

* **Response (Non-existing Origin Account):** `404 Not Found` (Body: `0`)

---

## ⚡ Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

Before installing the project, ensure you have the following tools installed on your system:

* **Docker Desktop** (with Docker Compose v2+)
* **Git**
* **WSL2** (If you are developing on Windows)

> 💡 **Note:** You do **not** need PHP or Composer installed locally on your host machine. Everything runs isolated inside Docker containers via Laravel Sail.

---

### Installation & Setup

Follow these steps to set up the development environment:

#### 1. Clone the Repository

```bash
git clone [https://github.com/gabeefadel/bank_account_api.git](https://github.com/gabeefadel/bank_account_api.git)
cd bank_account_api

```

#### 2. Create the Environment File

Copy the example environment file to create your local `.env`:

```bash
cp .env.example .env

```

#### 3. Install Composer Dependencies

Since you might not have Composer installed locally, run the following Docker command to boot a temporary container and install the project dependencies:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install

```

#### 4. Start the Application Containers

Bring up the Docker containers in the background using Laravel Sail:

```bash
./vendor/bin/sail up -d

```

#### 5. Generate Application Key

Generate the secure application encryption key:

```bash
./vendor/bin/sail artisan key:generate

```

#### 6. Run Database Migrations & API Setup

Run the database migrations and register the API routes infrastructure inside the container:

```bash
./vendor/bin/sail artisan migrate

```

---

## 🧪 Running Automated Tests

The test suite is built on top of PHPUnit and covers edge cases, business logic validation, and security vulnerabilities.

### Run All Tests

To run the full suite of automated tests inside the Dockerized environment, execute:

```bash
./vendor/bin/sail artisan test

```

### Run Specific Feature Tests

To run only the tests related to the bank account balance feature (covering happy paths, missing accounts, unauthenticated blocks, and malicious SQL injection payloads), run:

```bash
./vendor/bin/sail artisan test --filter=BankAccountBalanceTest

```

---

## 🛠️ Useful Commands

Here are the most common commands you will use during development:

* **Stop the environment:**

```bash
./vendor/bin/sail down

```

* **Clear application and route cache:**

```bash
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan config:clear

```

* **Regenerate Composer class map autoloading:**

```bash
./vendor/bin/sail composer dump-autoload

```

* **Access Database / Tinker:**

```bash
./vendor/bin/sail artisan tinker

```

```

```