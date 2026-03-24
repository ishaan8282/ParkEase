# ParkEase Deployment Guide

## Prerequisites
- GitHub Account
- Render Account (sign up at render.com)

---

## Step 1: Push to GitHub

### 1.1 Initialize Git (if not already done)
```bash
cd /var/www/html/ParkEase2/parkease
git init
git add .
git commit -m "Initial commit - ParkEase Parking App"
```

### 1.2 Create GitHub Repository
1. Go to [github.com](https://github.com)
2. Click "+" → "New repository"
3. Name it `ParkEase` or `parking-app`
4. Don't initialize with README (we have files)
5. Click "Create repository"

### 1.3 Push to GitHub
```bash
# Add your GitHub repo as remote (replace with your actual repo URL)
git remote add origin https://github.com/YOUR_USERNAME/ParkEase.git

# Push to GitHub
git branch -M main
git push -u origin main
```

---

## Step 2: Deploy to Render

### 2.1 Create Database on Render
1. Log in to [render.com](https://render.com)
2. Click "New" → "PostgreSQL"
3. Configure:
   - **Name**: `parkease-db`
   - **Database Name**: `parkease`
   - **User**: `parkease`
4. Click "Create Database"
5. Wait for it to provision, then copy:
   - **Internal Database URL** (you'll need this)

### 2.2 Deploy Web Service
1. Click "New" → "Web Service"
2. Connect your GitHub repository
3. Configure:
   - **Name**: `parkease`
   - **Environment**: `PHP`
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
4. Click "Create Web Service"

### 2.3 Configure Environment Variables
In Render dashboard, go to your web service's "Environment" tab and add:

```
APP_NAME=ParkEase
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

DB_CONNECTION=pgsql
DB_HOST=<your-postgres-host>
DB_PORT=5432
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-database-user>
DB_PASSWORD=<your-database-password>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 2.4 Run Migrations
1. In Render dashboard, go to your web service
2. Click "Shell" to open a terminal
3. Run:
```bash
php artisan migrate --force
```

---

## Step 3: Generate Application Key
In Render shell:
```bash
php artisan key:generate
```

---

## Step 4: Build Frontend Assets
In Render shell:
```bash
npm install
npm run build
```

---

## Common Issues

### "No application key configured"
Run: `php artisan key:generate`

### Database connection errors
Check that DB_* variables match your PostgreSQL credentials exactly.

### Static assets not loading
Make sure `npm run build` completed successfully and your `APP_URL` is correct.

---

## Quick Commands Reference

```bash
# Local development
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev

# Deploy
git add .
git commit -m "Update"
git push origin main
```

---

## Notes
- **Free Tier**: Render's free tier puts services to sleep after 15 minutes of inactivity. First request after sleep may take ~30 seconds.
- **Database**: Free PostgreSQL instance stays active.
- **SSL**: Render provides free SSL automatically.
