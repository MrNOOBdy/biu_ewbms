<!DOCTYPE html>
<html>
<head>
    <title>Balance Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 20px; }
        .report-title { font-size: 24px; margin-bottom: 10px; }
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
    <div id="balance-report">
        <div class="print-buttons">
            <button onclick="printBalanceReport()">Print</button>
            <button onclick="downloadBalanceReport()">Download PDF</button>
        </div>

        <div class="report-header">
            <div class="report-title">Balance Report</div>
            <div class="report-date">Generated on: {{ now()->format('F d, Y') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Block ID</th>
                    <th>Consumer Name</th>
                    <th>Balance Amount</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bills as $bill)
                    <tr>
                        <td>{{ $bill->block_id }}</td>
                        <td>{{ $bill->firstname }} {{ $bill->lastname }}</td>
                        <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                        <td>{{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No data available</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;"><strong>Total Balance:</strong></td>
                    <td colspan="2"><strong>₱{{ number_format($totalBalance, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
