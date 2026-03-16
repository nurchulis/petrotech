# Petrotechnical Platform UC2

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-316192?style=for-the-badge&logo=postgresql&logoColor=white)

---

## 1. Project Overview
The **Petrotechnical Platform UC2** is an internal cloud infrastructure management platform developed for **Pertamina UC2** (an upstream oil and gas subsidiary). 
The platform centralizes the management and monitoring of critical engineering infrastructure, providing Virtual Desktop Infrastructure (VDI) access, tracking expensive software licenses (like Petrel, Eclipse, Kingdom), monitoring VM telemetry and storage capacity, and providing an internal IT ticketing system.

## 2. Architecture Overview
The platform uses a **Modular Monolith** architecture built on Laravel 12.
- **Controllers**: Thin controllers are responsible for HTTP validation, explicit policy authorization, and calling services. No business logic resides here.
- **Services (Domain Logic)**: Fat service classes (`App\Services\...`) encapsulate all business operations, returning clean arrays or DTOs to controllers.
- **Models (Eloquent)**: Strictly focused on data structure, relationships, casts, and query scopes.
- **Routing & Middleware**: Routes are grouped by access level (e.g., `admin.*`). Authorization is handled via Spatie `laravel-permission`.
- **Frontend**: Blade templates extended from a Tabler UI base. Charting is done natively using ApexCharts.

## 3. Technology Stack
- **Backend Framework:** Laravel 12.54.1 (PHP 8.5)
- **Database:** PostgreSQL 18
- **Authentication:** Laravel Breeze (Session-based)
- **Authorization:** Spatie `laravel-permission` (Roles: `user`, `admin`, `super_admin`)
- **Activity Logging:** Spatie `laravel-activitylog`
- **Frontend:** Vanilla HTML/CSS/JS + Tabler UI (Bootstrap 5 base)
- **Charts:** ApexCharts 3.44 (CDN)

## 4. System Modules

### VDI Access
Provides browser-based management of engineering workstations. Users can connect to running virtual machines. The module includes a simulated fullscreen Remote Desktop (RDP/SSH) view that automatically detects OS types (Windows 11 UI vs Linux/GNOME terminal UI).

### License Management
Lifecycle tracking for expensive engineering software licenses. It tracks license servers, expiry dates, and seat availability, recording all changes and check-outs via an activity log. Admin-only functionality.

### VM Monitoring
Real-time and historical telemetry for virtual machines. Captures CPU, memory, Disk I/O, Network, and GPU utilization. Displays 24-hour interactive trend charts for each VM.

### Storage Monitoring
Monitors capacity and usage trends of NAS, SAN, and Object Storage devices. Captures historical storage snapshots to provide 30-day capacity trends and remaining free space analytics.

### Ticketing System
Internal IT helpdesk tailored for the platform. Supports ticket creation, assignment, priority levels, and open/in_progress/resolved/closed workflows. Features internal notes for admins.

### Analytics & Reporting
Platform-wide executive dashboard aggregating operational KPIs. Provides insights into active VDI sessions, expiring licenses, ticket queue health, and overall VM/Storage utilization over different time periods.

## 5. Installation Guide
Follow these steps to run the project locally on your machine.

### Prerequisites
- PHP 8.2+ (8.5 Recommended)
- Composer 2.x
- PostgreSQL 14+ (18 Recommended)

### Steps
1. Clone the repository:
   ```bash
   git clone <repository_url>
   cd petrotech
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Create environment file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Set up the database (see Database Setup below) and update your `.env` file credentials.
5. Create storage symlink:
   ```bash
   php artisan storage:link
   ```
6. Serve the application:
   ```bash
   php artisan serve
   ```

## 6. Docker Setup
You can optionally run this platform using Docker environments (`docker-compose.yml`).

1. Build and start the containers:
   ```bash
   docker-compose up -d --build
   ```
2. The application will be accessible at `localhost` (Port 80/443).
3. Run initialization commands inside the app container:
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   ```

## 7. Environment Configuration
Required variables in `.env`:
```dotenv
APP_NAME="Petrotechnical Platform"
APP_ENV=local
APP_KEY=...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=petrotech
DB_USERNAME=mac
DB_PASSWORD=1234
```
*Note: Ensure `DB_CONNECTION` is set to `pgsql` since features (e.g. JSONB) rely on PostgreSQL.*

## 8. Database Setup
1. Create a `petrotech` database in PostgreSQL.
2. Run database migrations to construct the normalized tables:
   ```bash
   php artisan migrate
   ```
3. Run the application seeders to populate initial roles, users, VMs, and mock metrics:
   ```bash
   php artisan db:seed
   ```
   *Note: Default users and roles (e.g. `super_admin`) are generated by `RoleSeeder` and `UserSeeder`.*

## 9. Development Workflow
* **Code Conventions**: Follow guidelines in `ai/coding_rules.md`. Thin controllers, fat services.
* **Database Changes**: Always create additive migrations. Never edit previously run migration files. Avoid MySQL-specific syntax.
* **Branching**: Use `feature/module-name`, `fix/issue-name` branching structure. Never work directly on `main`.
* **Testing**: Monitor server logs and browser console (for chart errors) when making Blade template changes.

## 10. Deployment Guide
For production environments (Ubuntu/Nginx/PHP-FPM):
1. **Nginx**: Configure SSL and map root to `/public`.
2. **PHP-FPM**: Ensure `php-pgsql` extension is enabled. Configure memory limit to `256M` and upload limit to `50M`.
3. **Optimizations**:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Set correct permissions: 
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   ```

## 11. Monitoring and Logging
* **Infrastructure**: It's highly recommended to monitor the host servers using Prometheus (Node Exporter, Postgres Exporter) and Grafana.
* **Application Metrics**: Monitor active VDI sessions and expiring licenses via the internal Analytics dashboard, and consider creating a `/metrics` endpoint to expose these to Prometheus.
* **Logs**: Laravel daily logs are stored in `storage/logs/`. Nginx access and error logs should be aggregated using Promtail/Loki or ELK.

## 12. Future Improvements
* **Real Telemetry Ingestion**: Replace the seeded/mocked VM and storage metrics with an API endpoint (`POST /api/metrics`) to ingest real stats from hypervisors or Telegraf agents.
* **SSO Integration**: Integrate with Microsoft Entra ID (Azure AD) or Okta via SAML/OAuth2 instead of using local database credentials.
* **Ticket SLAs**: Add tracking for response and resolution SLAs inside the Ticketing module, integrated with business hours.
* **Automated Provisioning**: Allow supervisors to request new VDI instances that automatically trigger Ansible/Terraform scripts to spin up VMs dynamically.
