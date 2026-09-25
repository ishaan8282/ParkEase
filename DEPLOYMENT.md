# ParkEase Deployment Guide - Railway

## Prerequisites
- GitHub Account
- Railway Account (sign up at railway.app)

---

## Step 1: Push to GitHub

```bash
cd /var/www/html/ParkEase2/parkease
git init
git add .
git commit -m "Initial commit - ParkEase Parking App"
git remote add origin https://github.com/YOUR_USERNAME/ParkEase.git
git branch -M main
git push -u origin main
```

---

## Step 2: Deploy on Railway

### 2.1 Create Railway Project
1. Go to [railway.app](https://railway.app) → Login
2. Click **New Project**
3. Select **Deploy from GitHub repo**
4. Connect your GitHub repository

### 2.2 Add PostgreSQL Database
1. In Railway dashboard, click **New** → **Database** → **PostgreSQL**
2. Wait for it to provision
3. Copy the **PostgreSQL Connection URL**

### 2.3 Configure Environment Variables
1. Click on your project → **Variables** tab
2. Add these variables:
```
APP_NAME=ParkEase
APP_ENV=production
APP_DEBUG=false
APP_KEY=  # Will generate later
APP_URL=https://your-app-name.up.railway.app

# Use the PostgreSQL connection URL and parse it
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

**To parse the PostgreSQL URL** (format: `postgres://user:pass@host:port/database`):
- Host: after @ to before :
- Port: after : to next /
- Database: after / to end
- Username: after postgres:// to :
- Password: between : and @

### 2.4 Deploy
1. Click **Deploy** on the Railway dashboard
2. Wait for deployment to complete

### 2.5 Run Migrations
1. In Railway dashboard, click your service → **Shell**
2. Run:
```bash
php artisan migrate --force
```

### 2.6 Generate App Key
In the same shell:
```bash
php artisan key:generate
```

### 2.7 Build Frontend
```bash
npm install
npm run build
```

---

## Step 3: Access Your App

Visit `https://your-app-name.up.railway.app`

---

## Quick Commands

```bash
# Local setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Deploy
git add .
git commit -m "Update"
git push origin main
```

---

## Notes
- **Free Tier**: $5 credit/month (enough for small apps)
- **Sleep**: No sleep on paid tiers, sleep on free tier after inactivity
- **SSL**: Provided automatically
- **Database**: PostgreSQL stays active
