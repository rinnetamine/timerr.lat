# Timerr
**Author: Artjoms Dvils DP 3-4**

A Laravel-based web platform that combines the ideas of Fiverr (freelance services) and TimeBank (using time as currency).
Timerr allows users to offer and exchange services using time credits instead of traditional currency.



## Overview

Timerr is a modern web application built with Laravel that enables users to:
- Create and manage job listings
- Submit job applications with attachments
- Exchange services using time credits
- Administer and moderate submissions

## Key Features

- **User Authentication**: Secure login system with role-based access control
- **Job Listings**: Create, edit, and manage service listings
- **Submission System**: Users can submit applications with attachments
- **Admin Dashboard**: Manage submissions, approve/reject applications
- **File Management**: File upload and storage system
- **Credit System**: Credit system for service exchange

## System Requirements

### Docker
- Docker Desktop or Docker Engine with Docker Compose

### Manual local setup
- PHP >= 8.2
- Composer
- Laravel >= 10.0
- SQLite
- Node.js and npm
- Git

## Installation Guide

### 1. Clone the Repository
```bash
git clone https://github.com/rinnetamine/timerr.lat.git
cd timerr.lat
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

The application uses SQLite by default. The database file will be created automatically at `database/database.sqlite`.

### 4. Run Database Migrations
```bash
php artisan migrate
```

### 5. Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Docker Setup

The project can be started with Docker only; PHP, Composer, Node.js, npm, and SQLite do not need to be installed on the host machine.

```bash
docker compose up --build

# The app will be available at http://localhost:8000
```

Docker uses SQLite by default and stores the database and uploaded files in Docker volumes, so no separate database service is required.

Useful commands:

```bash
# Stop the app
docker compose down

# Stop the app and remove the local Docker database/storage volumes
docker compose down -v
```
