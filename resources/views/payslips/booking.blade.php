<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Receipt #{{ $booking->booking_ref }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a2e;
            margin: 0;
            padding: 30px;
            font-size: 13px;
            line-height: 1.5;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #00d4ff;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .brand {
            font-size: 24px;
            font-weight: 800;
            color: #060910;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .brand span {
            color: #00d4ff;
        }
        .receipt-badge {
            float: right;
            text-align: right;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .receipt-ref {
            font-family: monospace;
            font-size: 14px;
            color: #0098b8;
            font-weight: bold;
        }
        .clear {
            clear: both;
        }
        .section-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .val {
            color: #0f172a;
            font-weight: 600;
            font-size: 13px;
        }
        .item-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .item-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px;
            text-align: left;
        }
        .item-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-box {
            margin-top: 15px;
            float: right;
            width: 250px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .grand-total {
            border-top: 2px solid #0f172a;
            padding-top: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #008f5d;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-paid {
            background: #dcfce7;
            color: #15803d;
        }
        .badge-cash {
            background: #fef3c7;
            color: #b45309;
        }
        .badge-pending {
            background: #fee2e2;
            color: #b91c1c;
        }
        .badge-waived {
            background: #f1f5f9;
            color: #475569;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
        }
        .rupee {
            font-weight: 400 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="receipt-badge">
            <h1 class="receipt-title">PARKING SLIP</h1>
            <div class="receipt-ref">REF: {{ $booking->booking_ref }}</div>
            <div style="margin-top: 4px; color: #64748b; font-size: 11px;">
                Issued: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
        <div class="brand">Park<span>Ease</span></div>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            Smart Parking Management System
        </div>
        <div class="clear"></div>
    </div>

    <!-- Parking Space & Slot info -->
    <div class="section-box">
        <div class="section-title">Facility & Slot Details</div>
        <table>
            <tr>
                <td width="50%">
                    <div class="label">Parking Space</div>
                    <div class="val">{{ $booking->parkingSpace->name ?? 'N/A' }}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        {{ $booking->parkingSpace->address ?? '' }}, {{ $booking->parkingSpace->city ?? '' }}
                    </div>
                </td>
                <td width="25%">
                    <div class="label">Assigned Slot</div>
                    <div class="val" style="color: #6366f1;">
                        {{ $booking->parkingSlot->slot_number ?? 'Slot #'.$booking->parking_slot_id }}
                    </div>
                </td>
                <td width="25%">
                    <div class="label">Vehicle Type</div>
                    <div class="val" style="text-transform: capitalize;">
                        {{ $booking->vehicle_type ?? ($booking->parkingSlot->type ?? 'Car') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Customer & Vehicle -->
    <div class="section-box">
        <div class="section-title">Customer & Vehicle Information</div>
        <table>
            <tr>
                <td width="35%">
                    <div class="label">Customer Name</div>
                    <div class="val">{{ $booking->customer_name ?: ($booking->user->name ?? 'Walk-In Guest') }}</div>
                </td>
                <td width="35%">
                    <div class="label">Contact Phone</div>
                    <div class="val">{{ $booking->customer_phone ?: ($booking->user->phone ?? 'N/A') }}</div>
                </td>
                <td width="30%">
                    <div class="label">Vehicle Plate #</div>
                    <div class="val" style="font-family: monospace; font-size: 14px;">
                        {{ $booking->vehicle_number ?? 'N/A' }}
                    </div>
                </td>
            </tr>
            @if($booking->customer_email)
            <tr>
                <td colspan="3" style="padding-top: 8px;">
                    <div class="label">Email</div>
                    <div class="val">{{ $booking->customer_email }}</div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Schedule & Check-in Details -->
    <div class="section-box">
        <div class="section-title">Timing & Status</div>
        <table>
            <tr>
                <td width="33%">
                    <div class="label">Check In (Entry)</div>
                    <div class="val">{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y, h:i A') }}</div>
                </td>
                <td width="33%">
                    <div class="label">Check Out (Expected)</div>
                    <div class="val">{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y, h:i A') }}</div>
                </td>
                <td width="34%">
                    <div class="label">Booking Status</div>
                    <div class="val" style="text-transform: uppercase;">
                        {{ str_replace('_', ' ', $booking->status) }}
                    </div>
                </td>
            </tr>
            @if($booking->actual_check_out)
            <tr>
                <td colspan="3" style="padding-top: 6px;">
                    <div class="label">Actual Exit Recorded</div>
                    <div class="val" style="color: #0098b8;">
                        {{ \Carbon\Carbon::parse($booking->actual_check_out)->format('d M Y, h:i A') }}
                    </div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Payment & Charges Breakdown -->
    <table class="item-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center;">Rate / Basis</th>
                <th style="text-align: right;">Amount (<span class="rupee">₹</span>)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Parking Slot Usage</strong><br>
                    <small style="color: #64748b;">
                        From {{ \Carbon\Carbon::parse($booking->check_in)->format('h:i A') }} to {{ \Carbon\Carbon::parse($booking->check_out)->format('h:i A') }}
                    </small>
                </td>
                <td style="text-align: center;">
                    <span class="rupee">₹</span>{{ number_format($booking->parkingSpace->price_per_hour ?? 0, 2) }}/hr
                </td>
                <td style="text-align: right; font-weight: 600;">
                    <span class="rupee"><span class="rupee">₹</span></span>{{ number_format($booking->amount, 2) }}
                </td>
            </tr>
            @if($booking->platform_fee > 0)
            <tr>
                <td>Service & Platform Fee</td>
                <td style="text-align: center;">Standard</td>
                <td style="text-align: right;"><span class="rupee">₹</span>{{ number_format($booking->platform_fee, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Totals Table -->
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td width="60%" style="vertical-align: top;">
                <div style="font-size: 12px; color: #475569;">
                    <strong>Payment Mode:</strong>
                    @if($booking->payment_status === 'paid')
                        <span class="badge badge-paid">PAID (ONLINE/CARD)</span>
                    @elseif($booking->payment_status === 'cash')
                        <span class="badge badge-cash">PAID BY CASH</span>
                    @elseif($booking->payment_status === 'waived')
                        <span class="badge badge-waived">FEE WAIVED</span>
                    @else
                        <span class="badge badge-pending">PAYMENT PENDING</span>
                    @endif
                </div>
                @if($booking->notes)
                <div style="margin-top: 10px; font-size: 11px; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; padding: 6px 10px; border-radius: 4px;">
                    <strong>Gate Notes:</strong> {{ $booking->notes }}
                </div>
                @endif
            </td>
            <td width="40%" style="text-align: right;">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: right; color: #64748b; padding: 4px 0;">Subtotal:</td>
                        <td style="text-align: right; font-weight: 600; padding: 4px 0;"><span class="rupee"><span class="rupee">₹</span></span>{{ number_format($booking->amount, 2) }}</td>
                    </tr>
                    @if($booking->platform_fee > 0)
                    <tr>
                        <td style="text-align: right; color: #64748b; padding: 4px 0;">Platform Fee:</td>
                        <td style="text-align: right; font-weight: 600; padding: 4px 0;"><span class="rupee"><span class="rupee">₹</span></span>{{ number_format($booking->platform_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="text-align: right; font-size: 15px; font-weight: bold; border-top: 2px solid #0f172a; padding-top: 8px;">
                            Total Paid:
                        </td>
                        <td style="text-align: right; font-size: 16px; font-weight: 800; color: #059669; border-top: 2px solid #0f172a; padding-top: 8px;">
                            <span class="rupee"><span class="rupee">₹</span></span>{{ number_format($booking->total_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for using ParkEase! Please retain this slip for slot verification and gate departure.</p>
        <p>This is a computer-generated receipt. No signature required.</p>
    </div>
</body>
</html>
