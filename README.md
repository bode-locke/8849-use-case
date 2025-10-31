# Laravel AYON Connector - Demo

A Laravel application demonstrating synchronization of Talents with AYON production tracker through both a web interface and Artisan commands.

## 📋 Overview

This project showcases a unidirectional synchronization system (Laravel → AYON) for managing Talents (users) with full CRUD capabilities and automatic background synchronization.

## 🛠 Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS (Laravel Vue Starter Kit)
- **Database**: MySQL 8.0
- **Containerization**: Docker 

## 🚀 Prerequisites

- Docker
- Git

## 📥 Installation

### 1. Setup AYON (Local)

First, you need to run AYON locally:
```bash
# Clone AYON Docker repository
git clone https://github.com/ynput/ayon-docker
cd ayon-docker

# Start AYON
docker compose up -d
```

AYON will be available at: **http://localhost:5000**

During first launch, AYON will prompt you to create an admin account. Complete the setup and generate an API key:
1. Go to **Settings** → **Studio Settings** → **API Keys**
2. Create a new API key
3. Copy the key for later use

### 2. Setup Laravel Application
```bash
# Clone the repository
git clone <your-repository-url>
cd 8849-use-case

# Copy environment file
cp .env.example .env

# Start Docker containers
docker compose up -d --build

# Enter the app container
docker compose exec app bash

# Install composer dependencies
composer install

# Generate App Key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Install NPM dependencies
npm install 

# Build 
npm run build
```

### 3. Configure AYON Connection

Edit your `.env` file and add the AYON API key:
```env
AYON_API_URL=http://host.docker.internal:5000
AYON_API_KEY=your-api-key-here
AYON_ON_DELETE=deactivate
```

**Important**: Use `host.docker.internal:5000` instead of `localhost:5000` to allow the Docker container to reach AYON running on your host machine.

## 🎯 Usage

### Web Interface

1. Navigate to **http://localhost**
2. Click on the **Login** button
3. Log in with the seeded admin account:
   - **Email**: `john.doe@example.com`
   - **Password**: `password`
4. Go to the **Talents** section

You will see **3 Talents with "pending" status** ready to be synchronized.

**Available actions:**
- ✅ **Create** new talents
- ✏️ **Edit** existing talents
- 🗑️ **Delete** talents (permanently removes from both Laravel and AYON)
- ⏸️ **Deactivate** talents (marks as inactive in AYON and in Laravel)

### Verify in AYON

To see the synchronized users in AYON:

1. Go to **http://localhost:5000**
2. Press **U+U** (keyboard shortcut) or navigate to the **Users** section
3. You should see your synchronized talents listed as AYON users

### Artisan Command (Batch Sync)

You can also synchronize talents using the Artisan command:
```bash
docker compose exec app bash

# Sync all talents
php artisan production-tracker:sync-talents

# Sync only talents with a specific status
php artisan production-tracker:sync-talents --status=pending
php artisan production-tracker:sync-talents --status=synced
php artisan production-tracker:sync-talents --status=inactive
php artisan production-tracker:sync-talents --status=error

```

## 🏗 Architecture

### Project Structure
```
app/
├── Console/
│   └── Commands/
│       └── TrackerSyncTalents.php  # Artisan command to (re)sync talents with AYON
│
├── Enums/
│   ├── TalentRole.php              # Defines available roles for talents
│   └── TalentStatus.php            # Defines synchronization states (Pending, Synced, Inactive, Error)
│
├── Http/
│   ├── Controllers/
│   │   └── TalentsController.php   # Main controller for talent CRUD and AYON sync
│   │
│   ├── Middleware/
│   │   └── UserIsAdmin.php
│   │
│   └── Requests/
│       └── TalentRequest.php       # Validation rules for creating/updating talents
│
├── Models/
│   └── Talent.php                  # Talent Eloquent model
│
└── Services/
│   ├── Contracts/
│   │   └── TalentAyonSyncServiceInterface.php   
│   │
│   └── TalentAyonSyncService/ #Service to handle Ayon Connector package
│   
│ 
├── packages/
│   └── laravel-ayon-connector/
│       ├── config/
│       │   └── ayon.php                      # Config file for the package
│       ├── src/
│       │   ├── Contracts/
│       │   │   └── AyonClientInterface.php   # Interface for AyonClient
│       │   ├── Exceptions/
│       │   │   └── AyonSyncException.php     # AYON-specific exceptions
│       │   ├── Services/
│       │   │   └── AyonClient.php            # Service for AYON API communication
│       │   └── AyonConnectorServiceProvider.php
│       └── tests/
│           └── Unit/
│               └── AyonClientTest.php        # Unit tests for the AYON client

```


### Manual Test Flow

1. **Create a Talent**
   - Go to http://localhost/talents
   - Click "New Talent"
   - Fill in the form (first name, last name, email, role)
   - Submit
   - Status should change from "pending" to "synced"

2. **Verify in AYON**
   - Go to http://localhost:5000
   - Press **U+U** or navigate to Users
   - You should see the newly created user

3. **Update a Talent**
   - Edit any talent
   - Change the name or role
   - Submit
   - Check AYON to see the updated information

4. **Deactivate vs Delete**
   - Click "Delete" on any talent
   - Modal appears with two options:
     - **Deactivate**: User becomes inactive in AYON
     - **Delete**: User is permanently removed from AYON and Laravel

## 📄 License

MIT

## 👤 Author

Benjamin - Use Case Demo for Recruitment

---
