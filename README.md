# Student Affairs API — Graduation Project

A RESTful backend API built with **Laravel 9** to power a mobile application for the Student Affairs department at the Faculty of Computers and Information, Zagazig University. The app allows students to submit requests, receive notifications, access academic records, and communicate with faculty — replacing a fully manual, paper-based process.

> **Status:** Completed & deployed — built as a graduation project (2024/2025)

---

## Features

- **Authentication** — Registration with OTP verification, login, forgot/reset password, token-based API auth
- **Student Requests** — Submit and track university and housing requests with status updates
- **Notifications** — Push notifications via Firebase Cloud Messaging (FCM), per-user and broadcast
- **Academic Records** — Grade statements, permit statements, course enrollment, student ranking
- **Complaints System** — Submit complaints and receive admin replies
- **Expenses** — Upload and view student expense records
- **Official Forms** — Upload, retrieve, and fill printable university forms
- **Faculty Members** — Directory of faculty staff
- **Admin Dashboard Data** — Enrollment stats, weekly request trends, user/course counts
- **Regulations & Timeline** — Upload regulation files and manage academic calendar

---

## Tech Stack

| Technology | Purpose | Why |
|---|---|---|
| Laravel 9 | Backend framework | MVC structure, Eloquent ORM, routing, migrations |
| MySQL | Database | Relational data with foreign key relationships |
| Laravel Token Auth (`auth:api`) | API authentication | Simple token guard suited for mobile app clients |
| Firebase Cloud Messaging | Push notifications | Delivers notifications even when the app is closed, unlike WebSocket-based solutions |
| Guzzle HTTP | HTTP client | Outbound requests to external services |
| Laravel CORS | Cross-origin handling | Allows the mobile client to communicate with the API |
| Azure App Service | Deployment | Cloud hosting with environment variable management |
| GitHub Actions | CI/CD | Automated deployment pipeline |

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── API/          # One controller per domain (Auth, Courses, Notifications, etc.)
├── Models/               # Eloquent models with relationships
database/
├── migrations/           # Schema version control
routes/
└── api.php               # All API endpoints, grouped by auth requirement
```

---

## API Overview

All endpoints are prefixed with `/api`. Protected routes require an `Authorization: Bearer <token>` header.

**Public (no token required)**
- `POST /register` — Register with student ID
- `POST /verify-otp` — Verify registration OTP
- `POST /login` — Login and receive token
- `POST /forgot-password` — Request password reset link
- `POST /reset-password` — Reset password

**Protected (token required)**
- User profile, course data, enrollments, grade/permit statements
- Submit and track university requests
- Push notification management
- Complaints and faculty directory
- Forms, expenses, regulations, timeline
- Admin stats and weekly request reports

---

## Setup & Installation

```bash
# Clone the repository
git clone https://github.com/MustfaAshraf/Graduation_Project.git
cd Graduation_Project

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up your database credentials in .env, then run migrations
php artisan migrate

# Add your Firebase credentials JSON file to:
# storage/app/firebase/ (path configured in .env)

# Start the development server
php artisan serve
```

---

## What I'd Improve

This project was built under graduation deadline pressure. Looking back at it with fresh eyes, here's what I'd do differently:

- **Auth middleware** — Routes were not properly protected during initial development. Fixed post-graduation to wrap all protected endpoints in `auth:api` middleware group.
- **RESTful conventions** — Routes followed an RPC style (`POST /delete-course`) instead of REST (`DELETE /courses/{id}`). I'd use Laravel's `apiResource()` to generate proper resourceful routes.
- **Background queues** — Firebase notifications are sent synchronously (`QUEUE_CONNECTION=sync`), blocking the response until FCM replies. I'd move notification dispatch to a queued job for better performance.
- **Caching** — Frequently-read data like course lists and faculty members hit the database on every request. Redis with a short TTL would reduce DB load significantly.
- **Role-based access control (RBAC)** — Admin-only routes (delete user, send-to-all notifications) share the same middleware as student routes. A proper role/permission system (e.g. Spatie Laravel Permission) would enforce who can do what.
- **API versioning** — No `/v1/` prefix means breaking changes would affect all clients immediately. Versioning the API from the start is the right approach.
- **Tests** — PHPUnit is configured but no tests were written. Feature tests for the auth flow and critical endpoints would catch regressions early.

---

## Author

**Mustafa Ashraf**
Backend Developer — PHP/Laravel & Node.js/Express
[GitHub](https://github.com/MustfaAshraf)
