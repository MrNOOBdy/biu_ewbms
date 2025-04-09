<!DOCTYPE html>
<html>
<head>
    <title>Application Income Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 20px; }
        .report-title { font-size: 24px; margin-bottom: 10px; }
        .report-date { font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .total-row { font-weight: bold; background-color: #f8f8f8; }
        .print-buttons { text-align: center; margin-bottom: 20px; }
        .print-buttons button { 
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
        }
        @media print {
            .print-buttons { display: none; }
        }
    </style>
</head>
<body>
    <div id="application-income-report">
        <div class="print-buttons">
            <button onclick="printApplicationReport()">Print</button>
            <button onclick="downloadApplicationReport()">Download PDF</button>
        </div>

        <div class="report-header">
            <div class="report-title">Application Income Report</div>
            <div class="report-date">
                Period: {{ $monthFilter ? date('F', mktime(0, 0, 0, $monthFilter, 1)) : 'All Months' }}
                {{ $yearFilter ?: 'All Years' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Block</th>
                    <th>Consumer Name</th>
                    <th>Application Fee</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @if($payment->consumer)
                        <tr>
                            <td>Block {{ $payment->consumer->block_id ?? 'N/A' }}</td>
                            <td>{{ $payment->consumer->firstname }} {{ $payment->consumer->middlename }} {{ $payment->consumer->lastname }}</td>
                            <td>₱{{ number_format($payment->application_fee, 2) }}</td>
                            <td>₱{{ number_format($payment->conn_amount_paid, 2) }}</td>
                            <td>₱{{ number_format($payment->application_fee - $payment->conn_amount_paid, 2) }}</td>
                            <td>{{ $payment->conn_pay_status == 'unpaid' ? 'Not paid yet' : ($payment->updated_at ? $payment->updated_at->format('M d, Y h:i A') : 'Not yet paid') }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No data available</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td>₱{{ number_format($totals['application_fee'], 2) }}</td>
                    <td>₱{{ number_format($totals['amount_paid'], 2) }}</td>
                    <td>₱{{ number_format($totals['balance'], 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
