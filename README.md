# Darna - Real Estate Marketplace API

Darna is an API-first real-estate marketplace backend built with Laravel 12 for Syrian governorates. It allows licensed real-estate offices to publish and manage properties for sale or rent, while public users can browse and inspect property details.

This project is strictly API-only and returns JSON across all endpoints.

---

## Technology Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** SQLite (default for development) / MySQL
- **Authentication:** Laravel Sanctum (token-based)
- **Caching:** Redis using Predis with automatic database fallback
- **Architecture:** API Resources, Form Requests, Policies, Service layer (`PropertyService`, `PropertyCacheService`)

---

## Setup Instructions

### 1. Prerequisites

- PHP 8.2 or higher (with `pdo_sqlite` or `pdo_mysql`, `mbstring`, `openssl`, `curl`)
- Composer 2.x

### 2. Installation

Clone or open the project folder, then install composer dependencies:

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file and generate the application encryption key:

```bash
cp .env.example .env
php artisan key:generate
```

Verify that `.env` contains the required settings:

```dotenv
APP_NAME=Darna
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

# Cache store defaults to database fallback when Redis is offline
CACHE_STORE=database

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_TIMEOUT=2
```

### 4. Database Migrations and Seeding

Run database migrations:

```bash
php artisan migrate --force
```

Seed the database with initial governorates, users, offices, owners, properties, and polymorphic images:

```bash
php artisan db:seed
```

### 5. Development Tokens

Generate plain Sanctum bearer tokens for seeded accounts at any time:

```bash
php artisan tokens:generate
```

This outputs pre-generated tokens for:
- `admin@darna.test` (Admin)
- `office1@darna.test` (Office 1)
- `office2@darna.test` (Office 2)
- `customer1@darna.test` (Customer)

All seeded user accounts share the password: `password`.

### 6. Starting the API Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

---

## API Versioning (V1 & V2)

The application supports simultaneous V1 and V2 APIs. Both versions share the underlying domain logic via `PropertyService` and `PropertyCacheService` while formatting responses separately through version-specific API Resources.

### Price Representation Difference

- **API V1 (`/api/v1/properties/...`):**
  ```json
  {
    "price": 150000,
    "currency": "USD"
  }
  ```

- **API V2 (`/api/v2/properties/...`):**
  ```json
  {
    "price": {
      "amount": 150000,
      "currency": "USD"
    }
  }
  ```

---

## API Endpoints

### Public Endpoints (V1 and V2)

| Method | Endpoint | Description | Pagination |
|---|---|---|---|
| `GET` | `/api/v1/properties` | Available property listings with filter parameters | Cursor |
| `GET` | `/api/v1/properties/featured` | Featured listings (newest 20 available) | None (Cached 10 min) |
| `GET` | `/api/v1/properties/counts-by-governorate` | Available property count per governorate | None (Cached 10 min) |
| `GET` | `/api/v1/properties/{id}` | Property details with eager-loaded relations | None (Cached 5 min) |

*(All above endpoints are mirrored under `/api/v2/...`)*

### Office / Authenticated Endpoints

| Method | Endpoint | Auth Required | Description |
|---|---|---|---|
| `GET` | `/api/v1/office/properties` | Sanctum (Office) | Office dashboard listings (all statuses) with regular length-aware pagination |
| `POST` | `/api/v1/properties` | Sanctum (Office/Admin) | Create a property (assigned to user's office) |
| `PATCH` | `/api/v1/properties/{id}` | Sanctum (Office/Admin) | Update property (restricted to owning office) |
| `DELETE` | `/api/v1/properties/{id}` | Sanctum (Office/Admin) | Delete property (restricted to owning office) |

---

## Data Privacy & Authorization

1. **Internal Data Protection:** Office commissions and internal notes are strictly omitted from all API Resources.
2. **Owner Phone Visibility:** Owner phone numbers are hidden (`null`) for unauthenticated requests and only exposed when the request contains a valid Sanctum bearer token.
3. **Cross-Office Protection:** Real-estate offices can only modify or delete properties belonging to their own office. Attempting to update another office's property returns `403 Forbidden`.
4. **Pagination Bounds:** Listing requests validate `per_page` (minimum 1, maximum 100). Requesting `per_page > 100` returns `422 Unprocessable Entity` with Arabic validation errors.

---

## Redis Caching and Database Fallback

### What is Cached

| Data | Cache Key | Tag | TTL |
|---|---|---|---|
| Featured listings | `properties_featured` | `property_listings` | 10 min |
| Governorate property counts | `properties_counts_by_governorate` | `property_listings` | 10 min |
| Individual property detail | `property_detail_{id}` | `property_detail_{id}` | 5 min |

### Cache Invalidation

- **Property created:** Flushes `property_listings` tag.
- **Property updated:** Flushes `property_listings` tag and `property_detail_{id}` tag.
- **Property deleted:** Flushes `property_listings` tag and `property_detail_{id}` tag.

### Graceful Fallback

`PropertyCacheService` targets Redis explicitly with a 2-second timeout and 0 retries. When Redis is unavailable, it catches Redis connection exceptions, logs a warning, and executes direct database queries so the application continues functioning seamlessly.

---

## Error Handling

All `/api/*` endpoints return unified JSON error responses:

- **401 Unauthorized:** Missing or invalid Sanctum token (`{"message": "Unauthenticated."}`).
- **403 Forbidden:** Unauthorized action / cross-office mutation (`{"message": "This action is unauthorized."}`).
- **404 Not Found:** Property or route does not exist (`{"message": "Not Found."}`).
- **422 Unprocessable Entity:** Validation failures with field-specific errors and Arabic messages.

---

## Testing with Postman

A Postman collection is included in the project root:
- `darna-api.postman_collection.json`

### Import and Test

1. Import `darna-api.postman_collection.json` into Postman.
2. The collection includes pre-configured collection variables (`baseUrl`, `office1Token`, `office2Token`, `customerToken`, `office1PropertyId`, `office2PropertyId`).
3. Run the requests to verify:
   - V1 vs V2 price format differences.
   - Owner phone visibility for anonymous vs authenticated users.
   - Validation failure when `per_page > 100`.
   - Authorization 403 on cross-office update.
   - Unauthenticated 401 response.
   - Cache invalidation and detail freshness after updating property price.
