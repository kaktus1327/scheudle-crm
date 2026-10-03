# Kino Grafik

Staff scheduling app for a cinema. Managers build weekly schedules (Friday to Thursday, the way cinemas plan their repertoire), employees submit availability, trade shifts and track their hours and pay. Mobile-first, because nobody checks their schedule on a desktop.

> Screenshots coming soon.

## Features

**Employee**

- Dashboard with hours worked, expected gross pay, a 6-month chart and a weekday/weekend split
- Weekly schedule with own shifts highlighted, switchable between the original and the current (after trades) version
- Availability for the next unpublished week, with hours per day or "can't work"
- Shift market: private swap or giveaway to a colleague, or an open offer that anyone can claim (first come, first served). Every trade needs manager approval.
- Claiming empty (yellow) shifts left open in the schedule
- Messages from the manager with attachments and required acknowledgement
- Own documents (sanitary book, student ID) with expiry dates
- Archive of past schedules

**Manager**

- Drag & drop schedule builder (with a tap-to-assign fallback on phones)
- Templates (calm / busy week, or save your own), with a warning when assigning outside availability ("add anyway")
- Auto-fill that spreads hours proportionally to each person's availability, then publish
- Trade approvals, highlighting trades where the taker gave no availability
- Messages with read / acknowledgement tracking
- Employees: CRUD with soft delete, positions (floor, bar, café), hourly rates, one-time passwords sent by email, password reset
- Document types with expiry reminders (daily scheduled job) and a filterable list

## Security

- Two roles enforced by route middleware **and** policies. Every manager route is covered by tests that assert a 403 for employees.
- API Resources control exactly which fields reach the browser. Colleagues' emails, phone numbers and hourly rates are never sent to employees.
- Sensitive attributes (`role`, `hourly_rate`, `must_change_password`) are not mass assignable.
- No public registration. Accounts are created by a manager with a one-time password that must be changed on first login.
- Attachments live on a private disk and are downloaded through an authorized route.
- Login throttling and security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`).

## Stack

- Laravel 12, PHP 8.4+
- Inertia.js 2 + React 19 + TypeScript
- Tailwind CSS 4, shadcn/ui, dnd-kit, Recharts, date-fns
- PostgreSQL, Mailpit (local mail)
- Pest, Pint, ESLint, Prettier, GitHub Actions

## Getting started

Requires Docker. The app runs on [Laravel Sail](https://laravel.com/docs/sail).

```bash
git clone https://github.com/kaktus1327/scheudle-crm.git && cd scheudle-crm
cp .env.example .env

# install PHP dependencies without a local PHP
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest composer install --ignore-platform-reqs

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

- App: http://localhost:8080
- Mailpit (one-time passwords, reminders): http://localhost:8025

### Demo accounts

The seeder creates 20 weeks of history, a draft for next week, pending trades, messages and documents.

| Role     | Email               | Password   |
| -------- | ------------------- | ---------- |
| Manager  | kierownik@kino.test | `password` |
| Employee | pracownik@kino.test | `password` |

In the local environment, the login page has a one-click fill for both accounts.

## Tests and code style

```bash
./vendor/bin/sail pest
./vendor/bin/sail bin pint
npm run lint && npm run types && npm run format
```

## Project structure

```
app/
  Enums/            Role, ScheduleStatus, TradeType, TradeStatus
  Http/Controllers/ Employee/, Manager/, shared (dashboard, archive)
  Http/Resources/   what the frontend is allowed to see
  Policies/         authorization for every model
  Services/         AutoScheduler, ShiftTradeService, HoursCalculator, ...
  Support/          CinemaWeek (Friday–Thursday value object)
resources/js/
  layouts/          mobile bottom nav / desktop sidebar
  pages/            employee/, manager/, archive/, auth/, settings/
  components/       schedule grid, trade cards, UI primitives
```
