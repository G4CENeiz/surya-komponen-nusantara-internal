# Installation Guide — HRIS PT Surya Komponen Nusantara

## Prerequisites

| Tool | Minimum Version | Notes |
|------|----------------|-------|
| **Docker Desktop** | 4.x | Must have Docker Compose v2 support |
| **Node.js** | 20+ | For building frontend assets |
| **npm** | 10+ | Comes with Node.js |
| **Git** | 2.x | To clone the repository |
| **PHP** | 8.5 | Only needed for local dev without Docker |

---

## Quick Start (Docker / Laravel Sail)

This is the **recommended** way. Everything runs inside Docker containers — no need to install PHP, MySQL, or Redis on your host machine.

### 1. Clone the Repository

```bash
git clone https://github.com/G4CENeiz/surya-komponen-nusantara-internal.git
cd surya-komponen-nusantara-internal
```

### 2. Install PHP Dependencies

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd)":/var/www/html \
  -w /var/www/html \
  laravelsail/php8.5-composer:latest \
  composer install --ignore-platform-reqs
```

> If you already have `composer` installed locally, you can simply run `composer install --ignore-platform-reqs` instead.

### 3. Create Environment File

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
docker run --rm \
  -v "$(pwd)":/var/www/html \
  -w /var/www/html \
  laravelsail/php8.5-composer:latest \
  php artisan key:generate
```

### 5. Start Docker Containers

```bash
sh sail up -d
```

This starts the following services:

| Service | Port | Purpose |
|---------|------|---------|
| **laravel.test** | `80` | PHP/Laravel application |
| **mysql** | `3306` | MySQL 8.4 database |
| **redis** | `6379` | Cache & sessions |
| **phpmyadmin** | `8080` | Database management UI |
| **deepface** | `5005` → `5000` | AI face recognition service |

### 6. Run Database Migrations & Seeders

```bash
sh sail artisan migrate --seed
```

This creates all tables and seeds:
- Roles (`employee`, `hr`, `accounting`)
- Departments, Job Classes, Workplaces
- 1,080+ employee records
- Test attendance, overtime, and leave data

### 7. Build Frontend Assets

```bash
sh sail npm install
sh sail npm run build
```

### 8. Create Storage Symlink

```bash
sh sail artisan storage:link
```

### 9. Clear All Caches

```bash
sh sail artisan optimize:clear
```

### 10. Verify Installation

Open your browser and go to:

| Panel | URL |
|-------|-----|
| **Employee** | http://localhost |
| **HRD** | http://localhost/hrd/login |
| **Accounting** | http://localhost/accounting/login |
| **phpMyAdmin** | http://localhost:8080 |

---

## Default Login Credentials

| Role | Email | Password | Panel |
|------|-------|----------|-------|
| **Employee** | `employee@example.com` | `password` | http://localhost |
| **HRD** | `hr@example.com` | `password` | http://localhost/hrd/login |
| **Accounting** | `accounting@example.com` | `password` | http://localhost/accounting/login |

---

## Cloudflare Tunnel (Public Access)

To expose the app to the public internet via Cloudflare Tunnel:

### 1. Get a Tunnel Token

1. Go to [Cloudflare Zero Trust](https://one.dash.cloudflare.com)
2. Navigate to **Networks** → **Tunnels**
3. Create a new tunnel and copy the token

### 2. Configure the Token

Add the token to your `.env` file:

```env
CLOUDFLARE_TUNNEL_TOKEN=your-tunnel-token-here
```

And update the `APP_URL` to match your tunnel domain:

```env
APP_URL=https://your-domain.com
```

### 3. Start the Tunnel

The `cloudflared` service is already configured in `compose.yaml`. It will start automatically with `sail up -d` if the token is set.

If you need to start it manually:

```bash
sh sail up -d cloudflared
```

### 4. Configure the Tunnel Route

In the Cloudflare dashboard, set the tunnel ingress **service** to:

```
http://laravel.test:80
```

---

## Technology Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| **Backend** | Laravel | 13.16 |
| **PHP** | PHP | 8.5 |
| **Admin Panel** | Filament | v5 |
| **CSS** | Tailwind CSS | v4 |
| **Livewire** | Livewire | v4 |
| **RBAC** | Spatie Permission + Filament Shield | — |
| **Face Recognition** | DeepFace (Python) | serengil/deepface |
| **Database** | MySQL | 8.4 |
| **Cache** | Redis | Alpine |
| **Frontend Build** | Vite | v8 |
| **Testing** | Pest PHP | v4 |

---

## Application Architecture

```
┌──────────────────────────────────────────────────────┐
│                    3 Filament Panels                  │
├──────────────────┬───────────────┬───────────────────┤
│   Employee (/)   │  HRD (/hrd)   │  Accounting       │
│                  │               │  (/accounting)    │
├──────────────────┼───────────────┼───────────────────┤
│ • Attendance     │ • Dashboard   │ • Dashboard       │
│   Dashboard      │ • Employees   │ • Payslips        │
│ • Pengajuan      │ • Job Classes │ • Reimbursements  │
│ • Informasi      │ • Leave Req.  │ • Cost Settings   │
│ • Slip Gaji      │ • Attendance  │                   │
│                  │ • Announcements│                   │
│                  │ • Assignments │                   │
│                  │ • Work Locs   │                   │
├──────────────────┴───────────────┴───────────────────┤
│            Shared: DeepFace (Face Recognition)        │
│            Shared: Geolocation Service                │
│            Shared: Attendance Service                 │
└──────────────────────────────────────────────────────┘
```

---

## Database Schema (Key Tables)

| Table | Purpose |
|-------|---------|
| `users` | User accounts with auth |
| `employees` | Employee profiles (linked to users) |
| `job_classes` | Job classification with salary ranges |
| `workplaces` | Office locations with geofence coordinates |
| `attendances` | Clock-in/out records with GPS data |
| `attendance_logs` | Detailed attendance action logs |
| `leave_requests` | Cuti / sakit / lembur submissions |
| `payslips` | Monthly payroll calculations |
| `assignments` | Task assignments from HRD |
| `announcements` | Company announcements |
| `payroll_settings` | Overtime rate, late penalty config |

---

## Common Issues & Fixes

### "Mixed content" errors in browser console

Your `APP_URL` doesn't match the domain you're accessing. Update `.env`:

```env
APP_URL=https://your-actual-domain.com
```

Then clear the config cache:

```bash
sh sail artisan config:clear
sh sail artisan config:cache
```

### DeepFace service not responding

The DeepFace container takes ~60 seconds to start on first boot. Check health:

```bash
docker compose ps deepface
```

Wait until status shows `(healthy)`. The service runs on port `5000` internally.

### 500 Error on HRD Dashboard

Widgets may reference deleted models. Clear all caches:

```bash
sh sail artisan optimize:clear
sh sail composer dump-autoload
```

### Livewire components not updating

Likely a stale Vite manifest or config cache. Run:

```bash
sh sail npm run build
sh sail artisan view:clear
sh sail artisan config:clear
sh sail artisan config:cache
```

### Cannot login through Cloudflare Tunnel

Ensure `APP_URL` matches your tunnel domain and trusted proxies are configured. The app automatically trusts all proxies (needed for Cloudflare).

---

## Development Commands

```bash
# Start all services
sh sail up -d

# Stop all services
sh sail down

# Run artisan commands
sh sail artisan <command>

# Access tinker
sh sail artisan tinker

# Run tests
sh sail artisan test --compact

# Run Pint (code style fixer)
sh sail vendor/bin/pint --dirty --format agent

# Clear all caches
sh sail artisan optimize:clear

# Rebuild frontend
sh sail npm run build

# Watch for frontend changes (dev mode)
sh sail npm run dev
```

---

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `http://localhost` | Application URL (must match access domain) |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `mysql` | Database host (Docker service name) |
| `DB_DATABASE` | `laravel` | Database name |
| `DB_USERNAME` | `sail` | Database username |
| `DB_PASSWORD` | `password` | Database password |
| `DEEPFACE_URL` | `http://deepface:5000` | DeepFace API URL (Docker internal) |
| `CLOUDFLARE_TUNNEL_TOKEN` | *(empty)* | Cloudflare Tunnel token for public access |
| `SESSION_DRIVER` | `database` | Session storage driver |
