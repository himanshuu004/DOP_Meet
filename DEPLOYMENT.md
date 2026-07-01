# DOP Meet — Deployment Guide

**Domain:** https://neoranewbie.in  
**GitHub:** https://github.com/himanshuu004/DOP_Meet.git  
**Deploy URL:** https://neoranewbie.in/deploy.php?key=meeting123

---

## Folder layout on cPanel (shared hosting)

```
/home/himanshu/
├── dop_meet/              ← Laravel app (NOT web-accessible)
│   ├── app/
│   ├── public/
│   ├── .env               ← create on server only
│   ├── deploy.php
│   ├── setup.php
│   └── git-init-server.php
└── public_html/           ← OR point domain docroot to dop_meet/public
```

**Recommended:** cPanel → **Domains** → set document root to `/home/himanshu/dop_meet/public`

---

## Step 1 — Create MySQL database (cPanel)

1. **MySQL® Databases**
2. Create database: `meeting` (note full name if prefixed, e.g. `himanshu_meeting`)
3. Add user `himanshu` with ALL PRIVILEGES on that database

---

## Step 2 — Upload / clone project

### Option A: Git clone (best)

```bash
cd ~
git clone https://github.com/himanshuu004/DOP_Meet.git dop_meet
cd dop_meet
```

Or open in browser (one-time):

```
https://neoranewbie.in/git-init-server.php?key=meeting123
```

### Option B: FTP / File Manager

Upload all files to `/home/himanshu/dop_meet/` except `.env`.

---

## Step 3 — Create `.env` on server

In `/home/himanshu/dop_meet/.env`:

```env
APP_NAME="DOP Meet"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://neoranewbie.in

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=meeting
DB_USERNAME=himanshu
DB_PASSWORD=your-db-password

DEPLOY_SECRET=meeting123

ADMIN_EMAIL=admin@neoranewbie.in
ADMIN_PASSWORD=choose-a-strong-password
```

Then run (Terminal or setup.php):

```bash
php artisan key:generate
```

---

## Step 4 — Permissions

Set writable (755 or 775):

- `storage/` (recursive)
- `bootstrap/cache/` (recursive)

---

## Step 5 — One-time setup

Open in browser:

```
https://neoranewbie.in/setup.php?key=meeting123
```

This runs migrations, seeds admin user, storage link, and caches config.

**Default login after seed:**

- Email: `admin@neoranewbie.in` (or your `ADMIN_EMAIL`)
- Password: value of `ADMIN_PASSWORD` in `.env`

Delete or rename `setup.php` and `git-init-server.php` after success.

---

## Step 6 — Point domain to Laravel

**Method 1 (recommended):** Document root → `/home/himanshu/dop_meet/public`

**Method 2:** Copy `dop_meet/public/*` into `public_html/` and edit `index.php` paths to `../dop_meet/`

---

## Ongoing deploy workflow

### On your MacBook (local)

```bash
./push.sh "Your commit message"
```

### On server (browser)

```
https://neoranewbie.in/deploy.php?key=meeting123
```

Runs: git pull → composer install → migrate → clear/cache config & routes.

---

## App URLs

| URL | Purpose |
|-----|---------|
| `/login` | Staff login |
| `/attendance` | Entry form |
| `/admin/attendance` | Dashboard + filters |
| `/admin/attendance/export` | Download Excel (all entries) |

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 error | Check `storage/` permissions; set `APP_DEBUG=true` briefly |
| DB connection failed | Verify full DB name/user in cPanel; try `DB_HOST=127.0.0.1` |
| Deploy 403 | Check `DEPLOY_SECRET` in `.env` matches URL `?key=` |
| Blank page after deploy | Run setup.php again; check `storage/logs/laravel.log` |
