# Dashboard Implementation Guide

## Overview
Professional Dashboard for Laravel 12 GST Invoicing & AI Collections App with multi-user support and comprehensive revenue tracking.

## Features

### 1. Stat Cards (4 Key Metrics)
- **Total Revenue**: Sum of payments received in current month (captured status)
- **Unpaid Amount**: Sum of invoices with payment_status = 'unpaid' or 'partial'
- **Overdue Amount**: Sum of invoices where status = 'sent' AND due_date < today
- **Active Orders**: Count of orders NOT in ['fully_billed', 'cancelled']

Each card includes:
- Color-coded left border accent (green, yellow, red, blue)
- Icon representation
- Quick link to filtered list page
- Responsive 2x2 mobile, 4x1 desktop layout

### 2. Overdue Alert Banner
Appears only when overdue_invoices > 0:
- Red alert with warning icon
- Shows count of overdue invoices
- Lists each overdue invoice with:
  - Invoice number
  - Client name
  - Days overdue
  - Amount due
  - "Send Reminder" button (for AI workflow)

### 3. Recent Invoices Table
Latest 5 invoices with columns:
- Invoice # (clickable → invoice detail)
- Client Name
- Amount
- Status (paid/partial/unpaid badge)
- Due Date (formatted)

Responsive: Horizontal scroll on mobile

### 4. Top Clients
Top 5 clients by total billed amount:
- Client name
- State
- Total billed (₹ format)
- Invoice count

Card-based layout for readability.

### 5. AI Follow-up Activity (Core Feature 🔥)
Latest 10 invoice call logs with:
- Invoice #
- Client Name
- Amount Due
- Days Overdue
- Last Contact (diffForHumans)
- Promised Payment Date
- Confidence Badge (high→green, medium→yellow, low→red)

**Interactive Modal**:
- Click any row to view full call details
- Shows:
  - Complete conversation transcript
  - AI notes
  - Call timing
  - Promised payment details

## Data Scoping
All queries are scoped to authenticated user via:
- `Invoice::where('user_id', $userId)`
- Related models filtered through invoice relationships

## Database Structure

### Key Tables & Relationships
```
invoices
├── user_id (scoping)
├── client_id
├── invoice_number
├── due_date
├── status ('draft', 'sent', etc)
├── payment_status ('paid', 'unpaid', 'partial')
├── amount_due
└── total

payments
├── invoice_id
├── amount
├── status ('captured', etc)

invoice_call_logs
├── invoice_number (foreign key to invoices.invoice_number)
├── promised_payment_date
├── confidence ('high', 'medium', 'low')
├── notes
├── conversation
├── call_started_at
└── call_ended_at

clients
├── user_id
├── name
└── state
```

## Implementation Details

### DashboardService Methods

#### getTotalRevenue()
```php
- Sums payments with status = 'captured'
- Filtered by current month
- Scoped to auth user
```

#### getUnpaidAmount()
```php
- Sums amount_due for invoices where:
  payment_status IN ('unpaid', 'partial')
- Scoped to auth user
```

#### getOverdueAmount()
```php
- Sums amount_due for invoices where:
  status = 'sent' AND due_date < today
- Scoped to auth user
```

#### getActiveOrdersCount()
```php
- Counts orders NOT IN ('fully_billed', 'cancelled')
- Scoped to auth user
```

#### getRecentInvoices()
```php
- Latest 5 invoices ordered by created_at DESC
- Includes client relationship
- Returns structured array
```

#### getTopClients()
```php
- Groups by client_id with SUM(total) and COUNT(*)
- Ordered by total_billed DESC
- Limited to 5
```

#### getOverdueInvoices()
```php
- All invoices where status='sent' AND due_date<today
- Includes days_overdue calculation
- Ordered by due_date DESC
```

#### getFollowupActivity()
```php
- Latest 10 InvoiceCallLog records
- Eager loads invoice→client relationships
- Calculates days_overdue per log
- Returns structured array with confidence badges
```

## UI/UX Features

### Styling
- Tailwind CSS for responsive design
- Bootstrap 5 compatible color system
- Dark-aware design with slate color palette

### Responsive Breakpoints
- Mobile (< 640px): Single column, horizontal scroll tables
- Tablet (640px - 1024px): 2x2 grid for cards
- Desktop (> 1024px): 4x1 grid for cards, 3-column layout

### Interactivity
- Hover states on all clickable elements
- Modal popup for call detail inspection
- Smooth transitions and animations
- Click row → open modal for call details

### Currency & Date Formatting
- Currency: ₹ (Indian Rupees)
- Format: ₹X,XX,XXX.XX (number_format with 2 decimals)
- Dates: Carbon formatting + diffForHumans for relative time
- Date display: "d M Y" (e.g., "15 Apr 2026")

## Routes Integration

The dashboard is accessed via:
```
GET /dashboard → DashboardController@__invoke
```

Related routes for quick access:
```
/invoices?filter=unpaid → Unpaid invoices
/invoices?filter=overdue → Overdue invoices
/orders → Active orders list
/invoices/{id} → Invoice detail page
```

## Security Considerations

1. **Multi-user Isolation**: All queries filtered by `Auth::user()->id`
2. **Middleware**: Controller uses `auth:sanctum` middleware
3. **Authorization**: Only authenticated users can access
4. **Data Privacy**: Client info visible only to invoice owner

## Performance Optimizations

1. **Eager Loading**: Uses `with()` for relationships
2. **Query Limiting**: `limit()` on large result sets
3. **Selective Columns**: Only fetches needed columns with `select()`
4. **Index Strategy**: Queries optimized for indexes on:
   - user_id
   - status
   - payment_status
   - due_date
   - created_at

## Future Enhancements

1. **Date Range Filter**: Add filter for custom date ranges
2. **Export Reports**: CSV/PDF export of dashboard data
3. **Charts & Analytics**: Chart.js integration for trend visualization
4. **Notifications**: Real-time alerts for new overdue invoices
5. **AI Suggestions**: Predictive insights based on call logs
6. **Bulk Actions**: Mark multiple reminders as sent

## Testing

### Unit Tests
```php
- Test getTotalRevenue() calculation
- Test unpaid amount filtering
- Test overdue detection
- Test order status filtering
```

### Integration Tests
```php
- Test dashboard loads for authenticated user
- Test data scoped to user
- Test modal interaction
- Test responsive layout
```

## Troubleshooting

### No data appearing?
1. Check user has invoices: `Invoice::where('user_id', auth()->id())->count()`
2. Verify payment status values
3. Check call logs exist: `InvoiceCallLog::count()`

### Modal not opening?
1. Verify JavaScript is loaded
2. Check browser console for errors
3. Ensure JSON encoding works: `json_encode($log)`

### Styling issues?
1. Clear Tailwind cache: `npm run build`
2. Check asset compilation: `npm run dev`
3. Verify layout.app extends correctly
