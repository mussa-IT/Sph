<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $billingHistory->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
            background: #ffffff;
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 60px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        
        .company-address {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-number {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .invoice-meta {
            font-size: 14px;
            color: #6b7280;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 12px;
        }
        
        .status-paid {
            background: #10b981;
            color: white;
        }
        
        .status-pending {
            background: #f59e0b;
            color: white;
        }
        
        .status-failed {
            background: #ef4444;
            color: white;
        }
        
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 40px;
        }
        
        .bill-to, .bill-from {
            flex: 1;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        
        .customer-info {
            font-size: 14px;
            color: #111827;
            line-height: 1.6;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        
        .items-table th {
            text-align: left;
            padding: 12px 16px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
        
        .items-table td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        
        .items-table .item-name {
            font-weight: 600;
            color: #111827;
        }
        
        .items-table .item-description {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .totals-section {
            max-width: 400px;
            margin-left: auto;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .total-row.subtotal {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .total-row.grand-total {
            background: #111827;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }
        
        .payment-info {
            margin-top: 40px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .payment-info strong {
            color: #111827;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: 700;
            color: #f3f4f6;
            opacity: 0.3;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ $billingHistory->status === 'completed' ? 'PAID' : strtoupper($billingHistory->status) }}</div>
    
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-logo">🚀 Smart Project Hub</div>
                <div class="company-name">Smart Project Hub Inc.</div>
                <div class="company-address">
                    123 Tech Street<br>
                    San Francisco, CA 94102<br>
                    United States<br>
                    Email: billing@smartprojecthub.com<br>
                    Phone: +1 (555) 123-4567
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-number">Invoice #{{ $billingHistory->invoice_number }}</div>
                <div class="invoice-meta">
                    Date: {{ $billingHistory->getFormattedDate() }}<br>
                    Due: {{ $billingHistory->processed_at?->format('M j, Y') ?? 'Upon receipt' }}
                </div>
                <div class="status-badge status-{{ $billingHistory->status }}">
                    {{ $billingHistory->getStatusLabel() }}
                </div>
            </div>
        </div>
        
        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="customer-info">
                    <strong>{{ $billingHistory->user->name }}</strong><br>
                    {{ $billingHistory->user->email }}<br>
                    @if($billingHistory->user->location)
                        {{ $billingHistory->user->location }}<br>
                    @endif
                    @if($billingHistory->user->website)
                        {{ $billingHistory->user->website }}
                    @endif
                </div>
            </div>
            
            <div class="bill-from">
                <div class="section-title">Bill From</div>
                <div class="customer-info">
                    <strong>Smart Project Hub Inc.</strong><br>
                    123 Tech Street<br>
                    San Francisco, CA 94102<br>
                    United States<br>
                    Tax ID: 12-3456789
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-name">{{ $billingHistory->description }}</div>
                        @if($billingHistory->subscription)
                            <div class="item-description">
                                {{ $billingHistory->subscription->plan->name }} Plan - 
                                {{ $billingHistory->subscription->getFormattedBillingCycle() }} billing
                            </div>
                        @endif
                    </td>
                    <td class="text-right">1</td>
                    <td class="text-right">${{ number_format($billingHistory->amount, 2) }}</td>
                    <td class="text-right">${{ number_format($billingHistory->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row subtotal">
                <span>Subtotal</span>
                <span>${{ number_format($billingHistory->amount, 2) }}</span>
            </div>
            
            @if($billingHistory->amount > 0)
                <div class="total-row">
                    <span>Tax (0%)</span>
                    <span>$0.00</span>
                </div>
            @endif
            
            <div class="total-row grand-total">
                <span>Total</span>
                <span>{{ $billingHistory->getFormattedAmount() }}</span>
            </div>
        </div>
        
        <!-- Payment Information -->
        @if($billingHistory->status === 'completed')
            <div class="payment-info">
                <strong>Payment Information:</strong><br>
                Payment Method: {{ ucfirst($billingHistory->payment_gateway) }}<br>
                Transaction ID: {{ $billingHistory->gateway_transaction_id ?? 'N/A' }}<br>
                Payment Date: {{ $billingHistory->processed_at->format('M j, Y g:i A') }}<br>
                @if($billingHistory->receipt_url)
                    Receipt available online at: {{ $billingHistory->receipt_url }}
                @endif
            </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business! This is a computer-generated invoice and does not require a signature.</p>
            <p>Questions? Contact us at billing@smartprojecthub.com | +1 (555) 123-4567</p>
            <p>© {{ date('Y') }} Smart Project Hub Inc. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
