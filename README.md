
# Bank Account API

A production-ready, highly structured RESTful API designed to manage essential banking operations. This project implements clean architectural patterns to ensure scalability, robust data consistency, and strict business rule validation.

---

## 🚀 Core Features

* **Account Management:** Handle secure operations for bank accounts.
* **Deposits:** Safely increase account balances with strict input validation.
* **Withdrawals:** Process debits while ensuring sufficient funds and enforcing account limits.
* **Transfers:** Atomically move values between accounts, guaranteeing transaction safety (ACID principles).
* **Balance & Statements:** Fetch real-time balances and transactional history.

---

## 🛠️ Tech Stack & Architecture

* **Framework:** PHP (Laravel / Laravel Sail)
* **Architecture:** Service Pattern / Clean Architecture approach
* **Database:** MySQL / PostgreSQL (handling atomic transactions)
* **Testing:** PHPUnit (Unit and Integration tests)

The codebase strictly adheres to **Clean Code** principles, using explicit, English-named variables and methods, keeping business logic completely decoupled from HTTP layers.

---

## 📐 Key Design Patterns Implemented

### 1. Service Pattern
All business rules (e.g., *“Can this account withdraw this amount?”*) are encapsulated inside dedicated Service classes, ensuring controllers remain thin, clean, and testable.

### 2. Polymorphic Event Handler
The `/api/event` architecture utilizes a dynamic routing pattern. Based on the payload's `"type"` field, the request is cleanly dispatched to its respective domain Service, maintaining high cohesion and separation of concerns.

### 3. Database Transactions (Atomic Operations)
For the `transfer` feature, the API guarantees that money deducted from the origin account is successfully deposited into the destination account. If any step fails, the entire operation safely rolls back.

---

## 📖 API Endpoints & Event Specification

### State Management
* `POST /api/reset` - Resets the state of the application before running test suites.
  * **Response:** `200 OK`

### Balance Actions
* `GET /api/balance?account_id={id}` - Retrieves the current balance for an account.
  * **Response (Existing Account):** `200 OK` (Body: `20`)
  * **Response (Non-existing Account):** `404 Not Found` (Body: `0`)

---

### Transactional Events
* `POST /api/event` - Single endpoint handling dynamic financial events (`deposit`, `withdraw`, and `transfer`).

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
git clone https://github.com/gabeefadel/bank_account_api.git
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

#### 6. Run Database Migrations

Run the database migrations to set up your tables:

```bash
./vendor/bin/sail artisan migrate

```

---

## 🛠️ Useful Commands

Here are the most common commands you will use during development:

* **Stop the environment:**
```bash
./vendor/bin/sail down

```


* **Run tests:**
```bash
./vendor/bin/sail test

```


* **Access Database / Tinker:**
```bash
./vendor/bin/sail artisan tinker

```



```

```