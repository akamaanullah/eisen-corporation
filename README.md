# Eisen Corporation — Japanese Used Car Export Platform

PHP MVC web application for vehicle inventory, buyer accounts, and admin operations.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` (XAMPP recommended on Windows)
- Composer

## Local setup (XAMPP)

1. Clone or copy this project to `htdocs/eisen`.
2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Copy environment config:

   ```bash
   copy config\config_local.example.php config\config_local.php
   ```

   Edit `config/config_local.php` with your database credentials.

4. Create the database and base schema:

   ```bash
   mysql -u root -p < config/schema.sql
   ```

   Or import `config/schema.sql` via phpMyAdmin.

5. Run migrations and content seeds:

   ```bash
   php config/migrate_all.php
   ```

6. (Optional) Seed demo users and vehicles:

   ```bash
   php config/seed_users.php
   php config/seed_vehicles.php
   ```

7. Open `http://localhost/eisen/` in your browser.

### Default development accounts

Only created when you run `seed_users.php` in **development** (`APP_ENV=development`):

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@eisen.com | admin123 |
| Buyer | tariq.m@example.com | password123 |

Change these immediately if exposing the app beyond localhost.

## Project structure

```
app/           Core, Controllers, Models, Helpers
config/        Config, migrations, seeds
public/        Web root (index.php, assets, uploads)
views/         PHP templates (front + admin)
```

## Migrations

- **Schema migrations:** `config/migrations/*.php` (tracked in `schema_migrations` table)
- **Runner:** `php config/migrate_all.php`
- Migrations are **idempotent** and safe to re-run.
- Content seeds (blog, sliders, partners, etc.) skip if data already exists.

## Admin panel

- URL: `/admin/login`
- Staff roles: `admin`, `finance_officer`, `caller_agent`
- Buyers use `/login` (not admin)

## Environment variables

Set in `config/config_local.php`:

| Constant | Purpose |
|----------|---------|
| `APP_ENV` | `development` or `production` |
| `DB_*` | Database connection |
| `SMTP_*` | Email (OTP, password reset) |
| `GOOGLE_*` | Google OAuth signup/login |

## Smoke test

```bash
php scratch/smoke_test.php
```

Verifies database connectivity and required schema objects.

## Stack

- PHP (custom MVC router)
- MySQL via PDO
- PHPMailer
- Vanilla JS + CSS (no frontend build step)
