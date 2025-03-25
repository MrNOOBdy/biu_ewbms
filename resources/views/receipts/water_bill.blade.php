<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" media="screen">
    <style>
        @media screen {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #f5f5f5;
            }
            .receipt-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .print-buttons {
                text-align: center;
                margin: 20px 0;
            }
            .print-buttons button {
                padding: 10px 20px;
                margin: 0 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }
            .print-button {
                background: #4CAF50;
                color: white;
            }
            .download-button {
                background: #2196F3;
                color: white;
            }
        }

        /* Common styles */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
        .logo-container img {
            height: 80px;
            width: auto;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .receipt-header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .receipt-header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .receipt-details td:first-child {
            font-weight: bold;
            width: 200px;
        }
        .amount-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .amount-section h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
        }

        @media print {
            @page {
                size: A4;
                orientation: landscape;
                margin: 0.5cm;
            }
            body {
                font-family: Arial, sans-serif;
                background: white;
                padding: 0;
                margin: 20px;
                font-size: 12pt;
            }
            .receipt-container {
                box-shadow: none;
                padding: 0;
                max-width: none;
                margin: 0;
            }
            .print-buttons, 
            .fas,
            button {
                display: none !important;
            }
            .logo-container {
                margin: 15px;
            }
            .logo-container img {
                height: 60px;
                width: auto;
            }
            .receipt-header {
                border-bottom: 2px solid #000;
            }
            .receipt-details td {
                border-bottom: 1px solid #000;
            }
            .amount-section {
                margin: 20px;
                border: 1px solid #000;
                background: none;
                page-break-inside: avoid;
            }
            .footer {
                page-break-inside: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="bill-receipt">
        <div class="print-buttons">
            <button class="print-button" onclick="printBillReceipt()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button class="download-button" onclick="downloadBillReceipt()">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>

        <div class="receipt-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo">
                <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo">
            </div>
            <h1>Water Bill Receipt</h1>
            <p>{{ config('app.name', 'Water Billing System') }}</p>
            <p>Receipt Date: {{ now()->format('F d, Y') }}</p>
            <p>Receipt No: {{ str_pad($bill->consread_id, 8, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td>Customer ID:</td>
                    <td>{{ $bill->consumer->customer_id }}</td>
                </tr>
                <tr>
                    <td>Block:</td>
                    <td>Block {{ $bill->consumer->block_id }}</td>
                </tr>
                <tr>
                    <td>Consumer Name:</td>
                    <td>{{ $bill->consumer->firstname }} {{ $bill->consumer->middlename }} {{ $bill->consumer->lastname }}</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>{{ $bill->consumer->address }}, Bien Unido</td>
                </tr>
                <tr>
                    <td>Billing Period:</td>
                    <td>{{ $bill->coverageDate->coverage_date_from }} to {{ $bill->coverageDate->coverage_date_to }}</td>
                </tr>
                <tr>
                    <td>Reading Date:</td>
                    <td>{{ date('F d, Y', strtotime($bill->reading_date)) }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td>{{ date('F d, Y', strtotime($bill->due_date)) }}</td>
                </tr>
            </table>

            <div class="amount-section">
                <h3>Billing Details</h3>
                <table>
                    <tr>
                        <td>Previous Reading:</td>
                        <td>{{ $bill->previous_reading }} cu.m</td>
                    </tr>
                    <tr>
                        <td>Present Reading:</td>
                        <td>{{ $bill->present_reading }} cu.m</td>
                    </tr>
                    <tr>
                        <td>Consumption:</td>
                        <td>{{ $bill->consumption }} cu.m</td>
                    </tr>
                    <tr>
                        <td>Base Amount:</td>
                        <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                    </tr>
                    @if($penaltyAmount > 0)
                    <tr>
                        <td>Penalty Amount:</td>
                        <td>₱{{ number_format($penaltyAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Total Amount Due:</strong></td>
                        <td><strong>₱{{ number_format($bill->total_amount + $penaltyAmount, 2) }}</strong></td>
                    </tr>
                    @if($bill->bill_status == 'PAID')
                    <tr>
                        <td>Amount Tendered:</td>
                        <td>₱{{ number_format($bill->billpay_tendered_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Date:</td>
                        <td>{{ date('F d, Y', strtotime($bill->payment_date)) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
            @if($bill->bill_status != 'PAID')
            <p style="color: #dc3545;"><strong>Please pay on or before the due date to avoid penalties.</strong></p>
            @endif
        </div>
    </div>
</body>
</html>
