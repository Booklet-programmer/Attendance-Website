# UPSA Smart QR Attendance System

PHP 8.2+, PostgreSQL, HTML, CSS and JavaScript application prepared for GitHub and Vercel Container deployment. No XAMPP is required.

## Vercel deployment architecture

- GitHub stores the source repository.
- Vercel deploys the application using `Dockerfile.vercel` and its Container runtime.
- FrankenPHP serves the PHP application.
- PostgreSQL is an external managed database (for example, a PostgreSQL provider such as Neon).
- PHP sessions are stored in PostgreSQL (`app_sessions`), not the container filesystem.
- Uploaded files, reports and other persistent assets must use external object storage; the Vercel container filesystem is not durable.

Vercel's container runtime is stateless. Keep application state in PostgreSQL or another managed backing service.

## Requirements for local development

- PHP 8.3+ with `pdo_pgsql`
- PostgreSQL 14+
- Composer 2
- Docker Desktop (optional, for testing the same container image locally)

## Database setup

1. Create a PostgreSQL database.
2. Execute `database/schema.sql` against it.
3. Configure environment variables in a local `.env` file or in Vercel Project Settings.

Required environment variables:

- `APP_ENV=production`
- `APP_URL=https://your-project.vercel.app`
- `APP_TIMEZONE=Africa/Accra`
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `QR_TTL_SECONDS=600`

Use the exact connection requirements from your database provider. Some managed providers require TLS/SSL; add the provider's required PDO PostgreSQL SSL options if applicable.

## Deploy from GitHub to Vercel

1. Push this repository to GitHub. Do not commit `.env`, database passwords, real student data, or generated secrets.
2. In Vercel, import the GitHub repository.
3. Vercel should detect `Dockerfile.vercel` and use its Container runtime. If asked, choose the Container framework/preset.
4. Add the environment variables listed above in Vercel Project Settings. Add them to Production and Preview as appropriate.
5. Deploy.
6. Check the deployment logs and test the login page and database connection.

The root `Dockerfile.vercel` starts FrankenPHP on Vercel's `$PORT`. `Caddyfile` points the web root at `public/`.

## Initial Head Administrator

The setup script is intended for a trusted local terminal, not a public web route:

```bash
composer install
cp .env.example .env
php scripts/create_admin.php
```

The script uses an interactive password prompt and PHP `password_hash()`. Run it against the same hosted database by supplying the database provider's connection values in your local `.env`. Never expose this script through a public URL.

## QR workflow

A lecturer creates a session. The dedicated QR page displays only the QR code. The browser requests a short-lived random token and refreshes it periodically. The student scans the QR, signs in, and attendance is inserted once per student/session.

## Important status

This is an early working foundation, not a completed university production system. Before real deployment, complete and test student registration/import, lecturer onboarding and approval, academic management, course/class allocations, reporting, session closure, rate limiting, audit coverage, privacy review, backup/restore, and end-to-end QR tests. Do not use real student information until those controls have been reviewed.

Browser geolocation is not included as proof of attendance. Browser location can be inaccurate or manipulated and should only be introduced after institutional approval and a privacy assessment.
