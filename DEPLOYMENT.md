# ParkEase Deployment Guide (Native PHP on Render)

## Prerequisites
- GitHub Account
- Render Account (sign up at render.com)

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

## Step 2: Deploy on Render (Native PHP - No Docker)

### 2.1 Create PostgreSQL Database
1. Go to [render.com](https://render.com) → Dashboard
2. Click **New** → **PostgreSQL**
3. Configure:
   - **Name**: `parkease-db`
   - **Database Name**: `parkease`
   - **User**: `parkease`
4. Click **Create Database**
5. Wait for status to be "Available", then copy the **Internal Database URL**

### 2.2 Create Web Service
1. Click **New** → **Web Service**
2. Connect your GitHub repository
3. Configure:
   - **Name**: `parkease`
   - **Environment**: `PHP`
   - **Build Command**: `composer install --no-dev --optimize-autoloader --no-interaction`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
4. Click **Create Web Service**

### 2.3 Configure Environment Variables
In your web service dashboard, go to **Environment** tab and add:

```
APP_NAME=ParkEase
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

DB_CONNECTION=pgsql
DB_HOST=your-postgres-host.render.com
DB_PORT=5432
DB_DATABASE=parkease
DB_USERNAME=parkease
DB_PASSWORD=your-postgres-password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

**Important**: Replace the DB_* values with your PostgreSQL credentials from step 2.1.

### 2.4 Run Migrations
1. In your web service dashboard, click **Shell**
2. Run:
```bash
php artisan migrate --force
```

### 2.5 Generate App Key
In the same shell:
```bash
php artisan key:generate
```

### 2.6 Build Frontend
```bash
npm install
npm run build
```

---

## Step 3: Access Your App

After deployment completes, visit `https://your-app-name.onrender.com`

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

## Troubleshooting

### "No application key"
Run: `php artisan key:generate`

### Database connection error
Check DB_* environment variables are correct

### Assets not loading
Run: `npm run build`

---

## Notes
- **Free Tier**: Services sleep after 15 min of inactivity. First request may take ~30 seconds
- **SSL**: Provided automatically by Render
- **Database**: Free PostgreSQL stays active
