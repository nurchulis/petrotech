# Petrotechnical Platform UC2

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-316192?style=for-the-badge&logo=postgresql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)

---

## 1. Overview

**Petrotechnical Platform UC2** is an internal cloud infrastructure management platform for **Pertamina UC2**. It provides:

- **VDI Access** — browser-based remote desktop sessions via Apache Guacamole (real RDP) or simulated desktop (dummy mode)
- **License Management** — lifecycle tracking for engineering software licenses (Petrel, Eclipse, Kingdom)
- **VM Monitoring** — real-time and historical CPU/Memory/GPU metrics
- **Storage Monitoring** — 30-day capacity trends for NAS/SAN/Object storage
- **Ticketing** — internal IT helpdesk with assignment and status workflows
- **Analytics** — platform-wide KPI dashboard

---

## 2. Architecture

```
Browser (HTTP)
     │
     ▼
[Nginx Container] → [Laravel App Container]  ←→  [PostgreSQL Container]
     │                       │                           │
     │               GuacamoleService               (petrotech DB)
     │               (REST API client)
     │                       │
     ▼                       ▼
[Guacamole Container :8080] ←→ [guacd Container] ──RDP──► Windows Server
             │
     (guacamole_db in PostgreSQL)
```

**Docker network:** All containers (Laravel, PostgreSQL, Guacamole, guacd) share the `petrotech_petrotech` network.  
Inter-container communication uses **container names** as hostnames — never `localhost` or `host.docker.internal`.

| Container | Hostname (in network) | Port |
|---|---|---|
| `petrotech-app-1` | `app` | — |
| `petrotech-postgres-1` | `postgres` | 5432 |
| `guacamole` | `guacamole` | 8080 |
| `guacd` | `guacd` | 4822 |

### VDI Dual-Mode

| Mode | `is_dummy` | Behaviour |
|---|---|---|
| Dummy | `true` | Fullscreen OS simulation (no infrastructure required) |
| Real RDP | `false` | Live session via Guacamole REST API, embedded as iframe |

RDP passwords are stored **encrypted** using `Crypt::encryptString()` and decrypted only at the model accessor level.

### Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.5) |
| Database | PostgreSQL 18 |
| Auth | Laravel Breeze (session-based) |
| Authorization | Spatie `laravel-permission` (roles: `user`, `admin`, `super_admin`) |
| Activity Log | Spatie `laravel-activitylog` |
| Frontend | Tabler UI + ApexCharts |
| RDP Gateway | Apache Guacamole (Docker) |

---

## 3. Docker Setup

### 3.1 Laravel Stack

```bash
docker compose up -d --build
```

Starts: `app`, `nginx`, `postgres`, `redis`.

Run first-time setup inside the app container:

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

### 3.2 Guacamole Stack

Guacamole runs in a **separate compose file** and joins the same Docker network.

```bash
# Start Guacamole services
docker compose -f docker-compose.guacamole.yml up -d
```

**`docker-compose.guacamole.yml` key config:**

```yaml
environment:
  GUACD_HOSTNAME: guacd          # guacd container name
  POSTGRESQL_HOSTNAME: postgres  # PostgreSQL container name (same network)
  POSTGRESQL_DATABASE: guacamole_db
  POSTGRESQL_USERNAME: postgres
  POSTGRESQL_PASSWORD: "1234"
networks:
  - petrotech_petrotech           # shared external network
```

> ⚠️ `POSTGRESQL_HOSTNAME` must be the **container name** (`postgres`), not `localhost` or `host.docker.internal`.

---

## 4. Database Setup

### 4.1 Laravel Database

The `petrotech` database is created automatically by the PostgreSQL container. Run migrations:

```bash
docker compose exec app php artisan migrate --seed
```

### 4.2 Guacamole Database (first time only)

```bash
# Generate schema SQL (use --platform linux/amd64 on Apple Silicon)
docker run --rm --platform linux/amd64 guacamole/guacamole \
  /opt/guacamole/bin/initdb.sh --postgresql > initdb.sql

# Create the Guacamole database
docker compose exec postgres psql -U postgres -c "CREATE DATABASE guacamole_db;"

# Import schema
docker compose exec -T postgres psql -U postgres -d guacamole_db < initdb.sql
```

Verify:

```bash
docker compose exec postgres psql -U postgres -d guacamole_db -c "\dt"
# Should list ~20 guacamole_* tables
```

---

## 5. Laravel Configuration

### `.env` (required variables)

```dotenv
APP_NAME="Petrotechnical Platform"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=http://SERVER_IP

DB_CONNECTION=pgsql
DB_HOST=postgres          # container name, not localhost
DB_PORT=5432
DB_DATABASE=petrotech
DB_USERNAME=postgres
DB_PASSWORD=1234

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Apache Guacamole
GUACAMOLE_URL=http://SERVER_IP:8080/guacamole
GUACAMOLE_USERNAME=guacadmin
GUACAMOLE_PASSWORD=guacadmin
```

### `config/services.php`

```php
'guacamole' => [
    'url'      => env('GUACAMOLE_URL'),
    'username' => env('GUACAMOLE_USERNAME'),
    'password' => env('GUACAMOLE_PASSWORD'),
],
```

> ⚠️ Keys must be `username`/`password` — not `user`/`pass`. `GuacamoleService` reads these exact keys.

---

## 6. Deployment

### Initial Deployment

```bash
# 1. Start Laravel stack
docker compose up -d --build

# 2. Install dependencies & initialize
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# 3. Initialize Guacamole DB (first time only — see Section 4.2)

# 4. Start Guacamole stack
docker compose -f docker-compose.guacamole.yml up -d
```

### Updating the Application

```bash
git pull origin main

docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear

# Restart the container — required to clear PHP opcode cache and config cache
docker compose restart app
```

> **Why restart?** Laravel caches config and routes in memory (opcache). Without a container restart, updated `.env` values and new code are not picked up reliably.

---

## 7. Usage — VDI Flow

### Setting up a Real RDP VM

```bash
docker compose exec app php artisan tinker
```

```php
App\Models\Vm::create([
    'vm_name'          => 'Win-Server-2022',
    'os_type'          => 'Windows Server 2022',
    'application_name' => 'Remote Desktop',
    'status'           => 'running',
    'is_dummy'         => false,
    'rdp_host'         => '103.23.198.194',   // actual VM IP
    'rdp_port'         => 3389,
    'rdp_username'     => 'administrator',
    'rdp_password'     => 'your_password',    // auto-encrypted by model
    'region'           => 'Cloud',
]);
```

### Connection Flow

1. User visits `/vdi` → selects a running VM
2. Clicks **Connect** → `POST /vdi/{vm}/connect`
3. `GuacamoleService` authenticates with Guacamole REST API → creates RDP connection → stores `guacamole_connection_id` in `vdi_sessions`
4. Browser opens `/vdi/{vm}/rdp` → Guacamole client loads in iframe
5. User clicks **Disconnect** → `POST /vdi/sessions/{session}/terminate` → connection deleted from Guacamole → session marked `closed`

---

## 8. Testing

After setting up a real VM record:

1. Browse to `http://SERVER_IP/vdi`
2. Click on the VM → click **Connect**
3. A new tab opens with the Guacamole iframe

**Verify in database:**

```bash
docker compose exec app php artisan tinker --execute="
\$s = App\Models\VdiSession::latest()->first();
echo 'Status: ' . \$s->status . PHP_EOL;
echo 'guacamole_connection_id: ' . \$s->guacamole_connection_id . PHP_EOL;
"
```

Expected output:
```
Status: connecting
guacamole_connection_id: 3
```

**Verify in Guacamole UI:**  
Browse to `http://SERVER_IP:8080/guacamole` → login as `guacadmin` → the connection should appear under **Active Connections**.

**Verify RDP works:**  
The Guacamole iframe should show the Windows desktop within 5–10 seconds.

---

## 9. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `Cannot authenticate with Guacamole` | Wrong `GUACAMOLE_URL`, `USERNAME`, or `PASSWORD` | Check `.env`, ensure keys are `GUACAMOLE_USERNAME`/`GUACAMOLE_PASSWORD`. Clear config cache: `php artisan optimize:clear` + restart container |
| `Invalid Login` in Guacamole UI | Guacamole not connected to PostgreSQL | Verify `POSTGRESQL_HOSTNAME=postgres` in compose, confirm `guacamole_db` schema is imported |
| `Config cache stale` | Old cached config still in use | Run `docker compose exec app php artisan optimize:clear` then `docker compose restart app` |
| Connection created but RDP login fails | Wrong `rdp_username`/`rdp_password` for the target VM | Verify credentials with a native RDP client first |
| `guacamole_connection_id` is null | Connect button not hitting real-mode path | Ensure `is_dummy = false` on the VM record |
| `host.docker.internal` resolution fails | Linux Docker host | Never use `host.docker.internal` — use container names via shared Docker network instead |
| Guacamole iframe blank / 404 | Wrong `GUACAMOLE_URL` — missing `/guacamole` suffix | Set `GUACAMOLE_URL=http://SERVER_IP:8080/guacamole` (with `/guacamole`) |

---

## 10. Development Workflow

- **Code Conventions**: Follow `ai/coding_rules.md`. Thin controllers, fat services.
- **Database Changes**: Always create additive migrations. Never edit previously run files. PostgreSQL only — avoid MySQL-specific syntax.
- **Branching**: `feature/module-name`, `fix/issue-name`. Never commit directly to `main`.
- **AI Context**: All architecture, ERD, deployment, and coding rules are documented in `ai/`.

---

## 11. Future Improvements

- **Real Telemetry Ingestion**: Replace seeded metrics with a `POST /api/metrics` endpoint from hypervisors or Telegraf agents.
- **SSO Integration**: Replace local credentials with Microsoft Entra ID / Okta via SAML/OAuth2.
- **Ticket SLAs**: Response and resolution SLA tracking with business-hours awareness.
- **Automated Provisioning**: Supervisor-triggered VM provisioning via Ansible/Terraform.
- **Guacamole SSO**: Pass Laravel-signed tokens to Guacamole to eliminate separate `guacadmin` credentials in production.
