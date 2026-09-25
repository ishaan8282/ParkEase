# Fixing Expired Bookings Issue

## Problem
Bookings that have expired (check_out time has passed) are still showing as booked, and parking slots are not being released automatically.

## Root Cause
The Laravel scheduler is not running. The `bookings:release-expired` command is scheduled to run every minute, but it requires the Laravel scheduler to be active.

## Immediate Fix

### Step 1: Release All Currently Expired Bookings
Run this command once to fix the current issue:

```bash
cd parkease
php artisan bookings:release-all-expired-now
```

This will:
- Find all bookings where check_out time has passed
- Update their status to 'completed'
- Release the parking slots (set status to 'available')

### Step 2: Verify the Fix
After running the command, check your admin dashboard to confirm that:
- The available slot count has increased
- Expired bookings are now marked as 'completed'

## Permanent Solution: Set Up Laravel Scheduler

The scheduler needs to run every minute to automatically release expired bookings. Choose one of the following methods:

### Method 1: Using Cron (Recommended for Linux/Unix servers)

1. Open your crontab:
```bash
crontab -e
```

2. Add this line:
```bash
* * * * * cd /var/www/html/ParkEase2/parkease && php artisan schedule:run >> /dev/null 2>&1
```

This will run the Laravel scheduler every minute.

### Method 2: Using Supervisor (Recommended for production servers)

1. Install Supervisor:
```bash
sudo apt-get install supervisor
```

2. Create a Supervisor configuration file:
```bash
sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf
```

3. Add this configuration:
```ini
[program:laravel-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/ParkEase2/parkease artisan schedule:run
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/ParkEase2/parkease/storage/logs/scheduler.log
```

4. Update Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler:*
```

### Method 3: Using Windows Task Scheduler (For Windows servers)

1. Open Task Scheduler
2. Create a new Basic Task
3. Set trigger to "Daily" and repeat every 1 minute
4. Set action to "Start a program"
5. Program: `php`
6. Arguments: `artisan schedule:run`
7. Start in: `C:\path\to\your\parkease\directory`

## How It Works

### Automatic Release (Every Minute)
The scheduler runs `bookings:release-expired` every minute, which:
1. Finds bookings with status 'confirmed' or 'checked_in' where check_out < now()
2. Updates booking status to 'completed'
3. Releases the parking slot (sets status to 'available')

### Manual Release (As Needed)
You can also run manually:
```bash
php artisan bookings:release-expired
```

## Verification

### Check if Scheduler is Running
```bash
php artisan schedule:list
```

This will show all scheduled commands and their next run time.

### Check Logs
View scheduler logs:
```bash
tail -f storage/logs/scheduler.log
```

### Monitor Expired Bookings
Check how many expired bookings exist:
```bash
php artisan tinker
```
Then run:
```php
Booking::whereIn('status', ['confirmed', 'checked_in'])
    ->where('check_out', '<', now())
    ->count();
```

## Best Practices

1. **Always run the scheduler** in production to automatically release expired bookings
2. **Monitor logs** to ensure the scheduler is running correctly
3. **Run the manual command** periodically to catch any missed bookings
4. **Set up alerts** if the scheduler fails to run

## Troubleshooting

### Scheduler Not Running
- Check if cron is running: `service cron status`
- Check cron logs: `grep CRON /var/log/syslog`
- Verify the cron job is added: `crontab -l`

### Bookings Still Not Released
- Check if the command runs manually: `php artisan bookings:release-expired`
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify database connection

### Slots Still Showing as Booked
- Run the manual release command: `php artisan bookings:release-all-expired-now`
- Check if the booking has a valid parking_slot_id
- Verify the slot status in the database

## Additional Commands

### List All Scheduled Commands
```bash
php artisan schedule:list
```

### Run Scheduler Manually (for testing)
```bash
php artisan schedule:run
```

### Clear Expired Reservations (5-minute payment window)
```bash
php artisan reservations:release-expired
```

## Support
If issues persist, check:
- Laravel logs: `storage/logs/laravel.log`
- Scheduler logs: `storage/logs/scheduler.log`
- Database for booking status
