# BookPlug

A book-selling platform. Admins publish books (digital PDF and/or physical
paperback); readers browse, buy via M-Pesa, download the PDF or track a
physical delivery.

- **Frontend**: Vue 3 + Vite + Tailwind CSS + Pinia + Vue Router
- **Backend**: Laravel 12 + MySQL 9 + Sanctum tokens + Safaricom Daraja (M-Pesa)

---

## Repo layout

```
book_plug/
├── frontend/          Vue SPA (public store + admin console)
└── backend/           Laravel JSON API
```

---

## Local development

### 1. Backend

```bash
cd backend
cp .env.example .env
php artisan key:generate

# Create the database and run migrations
mysql -uroot -e "CREATE DATABASE bookplug CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed

# Link the public storage disk (covers & PDFs)
php artisan storage:link

# Serve
php artisan serve --port=8080
```

Seed accounts:

| Role   | Email                 | Password  |
|--------|-----------------------|-----------|
| Admin  | admin@bookplug.io     | password  |
| Reader | reader@bookplug.io    | password  |

### 2. Frontend

```bash
cd frontend
cp .env.example .env       # sets VITE_API_URL=http://localhost:8080/api
npm install
npm run dev                # http://localhost:5180
```

### 3. M-Pesa (optional)

Fill these in `backend/.env` to enable STK Push:

```
MPESA_ENV=sandbox            # or production
MPESA_CONSUMER_KEY=…
MPESA_CONSUMER_SECRET=…
MPESA_SHORTCODE=…
MPESA_PASSKEY=…
MPESA_CALLBACK_URL=https://<public-https-host>/api/mpesa/callback
```

**Daraja production rejects http:// and localhost callback URLs.** During
local development, tunnel your backend with ngrok/cloudflared and set the
tunnel URL as `MPESA_CALLBACK_URL`.

---

## Production build

```bash
# Frontend
cd frontend && npm run build     # emits static site to frontend/dist

# Backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
php artisan migrate --force
```

Serve `frontend/dist/` from any static host (nginx, Cloudflare Pages,
Netlify), and point it at the API host via `VITE_API_URL` at build time.

Point nginx (or Apache) at `backend/public` as the document root and
forward all traffic to `index.php`.

---

## Feature matrix

### Public
- Browse catalog with search / category / format / rating / sort filters
- Book detail with digital + physical purchase options and bundle upsell
- Free preview reader (8-page mock — swap for PDF.js when a real PDF is uploaded)
- Cart in localStorage, checkout with M-Pesa STK Push
- Register / login (Sanctum bearer token, hydrated on page load)
- Personal library with reading progress, orders, PDF downloads

### Admin (`role=admin`)
- Live dashboard: revenue, orders, users, catalog size, pending deliveries
- Books CRUD with cover + PDF uploads (multipart)
- Category management (blocked from deletion when books reference it)
- Order pipeline with inline status changes
- Delivery tracker with courier + tracking-number editing
- Users list with lifetime-spend, role promotion

### Backend guarantees
- Sanctum-issued API tokens; `admin` middleware for privileged routes
- Rate-limited registration/login (10/min) and M-Pesa initiation (5/min)
- Order transaction wraps line item + shipping-address + total math
- M-Pesa callback verifies `CheckoutRequestID` before granting entitlement
- Library entitlement gates PDF downloads (403 without ownership)
- CORS restricted to the configured frontend origin
- JSON 401 for unauthenticated API requests (no login-redirect blowups)

---

## Deployment checklist

Environment-specific — not shipped in the repo:

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure a real database backup schedule
- [ ] Terminate TLS at a reverse proxy (nginx + Certbot, Cloudflare, etc.)
- [ ] Point `MPESA_CALLBACK_URL` at your public HTTPS backend
- [ ] Move file storage to S3/R2 for scale (`FILESYSTEM_DISK=s3`)
- [ ] Front the app with a CDN (long-cache `/assets/*`, no-cache `index.html`)
- [ ] Configure error monitoring (Sentry, Bugsnag) in Laravel and Vue
- [ ] Configure Laravel scheduler + worker for queues (optional today)
- [ ] Rotate the M-Pesa production credentials that were shared in chat

---

## Architecture notes

**Auth flow**: Vue calls `POST /api/auth/login`, stores the returned bearer
in `localStorage`, and attaches it via an axios request interceptor. On boot
the app hits `GET /api/auth/me` to hydrate. A 401 from any endpoint clears
the token and bounces to `/login`.

**Payment flow**: user submits checkout → `POST /api/orders` returns a
pending order → `POST /api/orders/{ref}/mpesa/stk` fires STK Push and
records a `mpesa_transactions` row → Daraja calls back to
`POST /api/mpesa/callback` → callback grants `library_items` for every
digital line and creates a `deliveries` row for physical ones → frontend
polls `GET /api/mpesa/transactions/{id}` until status flips to success or
failure.

**File gating**: cover images are public (served from `storage/app/public`
via the symlink). PDFs are stored on the same disk but exposed only through
`GET /api/books/{slug}/pdf`, which checks `library_items` for the
authenticated user before streaming.
