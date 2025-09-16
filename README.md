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

- PHP >= 8.1
- Composer
- Laravel >= 10.0
- MySQL >= 8.0
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

### 3. Configure Database
1. Create a new MySQL database (i am using xampp for this)
2. Rename .env.example to .env
3. Update the database configuration in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TimerrDB
DB_USERNAME=your_username (standart: "root")
DB_PASSWORD=your_password (standart: "empty")
```

### 4. Run Database Migrations
```bash
php artisan migrate
```

### 5. Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

