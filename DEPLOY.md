# Resmenu deployment checklist

## Order of deploy

1. **Back up** the production database and `uploads/` (cPanel backup or export).
2. **Pull** application code to the server document root.
3. **Run new SQL migrations** in phpMyAdmin (or MySQL CLI) in chronological order, using the `.sql` files from **your local `Resmenu/database/` folder** (that folder is **gitignored** so it is not deployed with the app). Apply each release’s scripts in order. Do not paste untested SQL from chat into production.
4. **Configure secrets** in `config/config.local.php` or server environment variables (never commit `config.local.php`). After this release, set **`APP_HMAC_SECRET`** to a long random string so customer cancel endpoints work.
5. **Smoke test**: place a test order (or use staging), **checkout** with a test payment method, **payment callback** redirect, and (if applicable) **cancel** with a valid signed token from the submit-order API response.

## Migrations relevant to security hardening

(On your machine, these live under `Resmenu/database/` — import via phpMyAdmin when deploying.)

- `migration_public_api_rate_events.sql` — rate limiting for `submit-order` and cancel APIs.
- Other `migration_*.sql` files: run any you have not yet applied (example: `migration_registration_email_suppression.sql`).

## Repository hygiene (if you use Git)

- Confirm **`config.local.php`**, **`.env`**, and **database dumps with real data** were never committed. If a secret ever appeared in history, **rotate** it and treat it as compromised.
- The **`database/`** directory is ignored for deploys; keep migration and dump SQL **locally** (or in a private backup) and run them in phpMyAdmin as needed.

## Runtime flags (see `config/config.example.php`)

- **`TRUST_PROXY_HEADERS`**: set `true` only when the app sits behind a trusted reverse proxy (for example Cloudflare) and spoofed `X-Forwarded-For` is not a concern. When `false`, client IP for rate limits is **`REMOTE_ADDR`** only.
- **`AUTH_SESSION_IDLE_SECONDS`**: idle timeout for admin/manager sessions (default 3600 seconds).
