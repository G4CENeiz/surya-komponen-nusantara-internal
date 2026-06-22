# Docker Setup

Multi-stage Docker configuration for the Surya Komponen Nusantara Internal application.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Docker Compose                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  app (Nginx + PHP-FPM)                              │   │
│  │  - Serves Laravel application                        │   │
│  │  - Port: 8080                                        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  queue (PHP-FPM)                                     │   │
│  │  - Processes background jobs                         │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  scheduler (PHP-FPM)                                 │   │
│  │  - Runs Laravel scheduler every minute               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  face-recognition (Python + FastAPI)                 │   │
│  │  - DeepFace for face verification                    │   │
│  │  - Port: 8001                                        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  db (MySQL 8.0)                                      │   │
│  │  - Port: 3306                                        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  redis (Redis 7)                                     │   │
│  │  - Cache + Session + Queue                           │   │
│  │  - Port: 6379                                        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## Quick Start

### Development

```bash
# Copy environment file
cp .env.docker .env

# Build and start containers
docker compose up -d

# Install dependencies
docker compose exec app composer install
docker compose exec app bun install

# Generate application key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# Seed database
docker compose exec app php artisan db:seed

# Build frontend assets
docker compose exec app bun run build

# Access the application
open http://localhost:8080
```

### Production

```bash
# Build production image
docker compose -f docker-compose.yml build app

# Start in production mode
docker compose up -d --build

# Run migrations
docker compose exec app php artisan migrate --force

# Cache configuration
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Services

| Service | Port | Description |
|---------|------|-------------|
| app | 8080 | Laravel application (Nginx + PHP-FPM) |
| face-recognition | 8001 | Face recognition API (DeepFace + FastAPI) |
| db | 3306 | MySQL database |
| redis | 6379 | Redis (cache, session, queue) |

## Face Recognition Service

The face recognition microservice runs as a separate container and provides:

- `/verify` - Verify if two faces belong to the same person
- `/embedding` - Get face embedding vector
- `/verify-file` - Verify using uploaded files
- `/embedding-file` - Get embedding from uploaded file

### API Usage

```bash
# Verify two faces (base64)
curl -X POST http://localhost:8001/verify \
  -H "Content-Type: application/json" \
  -d '{"image1": "base64...", "image2": "base64..."}'

# Get face embedding
curl -X POST http://localhost:8001/embedding \
  -H "Content-Type: application/json" \
  -d '{"image": "base64..."}'
```

## Common Commands

```bash
# View logs
docker compose logs -f app
docker compose logs -f face-recognition

# Restart a service
docker compose restart app

# Stop all services
docker compose down

# Stop and remove volumes
docker compose down -v

# Rebuild a specific service
docker compose build app --no-cache

# Access container shell
docker compose exec app bash
docker compose exec face-recognition bash

# Run Artisan commands
docker compose exec app php artisan <command>

# Run queue worker manually
docker compose exec app php artisan queue:work

# Check queue status
docker compose exec app php artisan queue:monitor
```

## Volumes

| Volume | Purpose |
|--------|---------|
| storage | Laravel storage directory |
| node_modules | Node.js dependencies |
| db | MySQL data |
| redis | Redis data |
| face-models | Face recognition model weights |

## Environment Variables

Key environment variables to configure:

| Variable | Description | Default |
|----------|-------------|---------|
| APP_URL | Application URL | http://localhost:8080 |
| DB_HOST | Database host | db |
| DB_DATABASE | Database name | sanken |
| REDIS_HOST | Redis host | redis |
| FACE_RECOGNITION_URL | Face recognition API URL | http://face-recognition:8000 |

## Troubleshooting

### Container won't start
```bash
docker compose logs <service-name>
```

### Permission issues
```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 755 /var/www/html/storage
```

### Face recognition slow on first request
The DeepFace models need to be downloaded on first use. This is normal and takes 1-2 minutes.

### Clear all caches
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```
