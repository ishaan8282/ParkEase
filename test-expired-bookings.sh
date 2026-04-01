#!/bin/bash

# Test script to verify expired bookings release system
# Run this script to check if everything is working correctly

echo "=========================================="
echo "ParkEase - Expired Bookings Test Script"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the parkease directory"
    echo "   Current directory: $(pwd)"
    exit 1
fi

echo "✓ Running from correct directory"
echo ""

# Step 1: Check if scheduler is configured
echo "Step 1: Checking scheduler configuration..."
php artisan schedule:list | grep "bookings:release-expired"
if [ $? -eq 0 ]; then
    echo "✓ Scheduler is configured for bookings:release-expired"
else
    echo "⚠ Warning: Scheduler may not be configured"
fi
echo ""

# Step 2: Count expired bookings
echo "Step 2: Counting expired bookings..."
EXPIRED_COUNT=$(php artisan tinker --execute="
use App\Models\Booking;
echo Booking::whereIn('status', ['confirmed', 'checked_in'])
    ->where('check_out', '<', now())
    ->count();
" 2>/dev/null)

if [ -z "$EXPIRED_COUNT" ]; then
    echo "⚠ Could not count expired bookings (tinker may not be available)"
else
    echo "Found $EXPIRED_COUNT expired booking(s)"
fi
echo ""

# Step 3: Run manual release
echo "Step 3: Running manual release of expired bookings..."
php artisan bookings:release-all-expired-now
echo ""

# Step 4: Verify results
echo "Step 4: Verifying results..."
EXPIRED_COUNT_AFTER=$(php artisan tinker --execute="
use App\Models\Booking;
echo Booking::whereIn('status', ['confirmed', 'checked_in'])
    ->where('check_out', '<', now())
    ->count();
" 2>/dev/null)

if [ -z "$EXPIRED_COUNT_AFTER" ]; then
    echo "⚠ Could not verify results"
else
    echo "Expired bookings remaining: $EXPIRED_COUNT_AFTER"
    if [ "$EXPIRED_COUNT_AFTER" -eq 0 ]; then
        echo "✓ All expired bookings have been released!"
    else
        echo "⚠ Some expired bookings still remain"
    fi
fi
echo ""

# Step 5: Check available slots
echo "Step 5: Checking available slots..."
AVAILABLE_SLOTS=$(php artisan tinker --execute="
use App\Models\ParkingSlot;
echo ParkingSlot::where('status', 'available')->count();
" 2>/dev/null)

TOTAL_SLOTS=$(php artisan tinker --execute="
use App\Models\ParkingSlot;
echo ParkingSlot::count();
" 2>/dev/null)

if [ -z "$AVAILABLE_SLOTS" ] || [ -z "$TOTAL_SLOTS" ]; then
    echo "⚠ Could not check slot availability"
else
    echo "Available slots: $AVAILABLE_SLOTS / $TOTAL_SLOTS"
fi
echo ""

echo "=========================================="
echo "Test Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Set up the Laravel scheduler (see SCHEDULER_SETUP.md)"
echo "2. Monitor the scheduler logs: tail -f storage/logs/scheduler.log"
echo "3. Run this script periodically to verify everything is working"
echo ""
