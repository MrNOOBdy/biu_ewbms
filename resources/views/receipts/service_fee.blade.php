<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Fee Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" media="screen">
    <style>
        /* Screen styles */
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
            margin: 0;
            color: #333;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
        }

        /* Print styles */
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
            a {
                text-decoration: none;
                color: #000;
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
    <div class="receipt-container" id="receipt-{{ $payment->customer_id }}">
        <div class="print-buttons">
            <button class="print-button" onclick="printServiceReceipt('{{ $payment->customer_id }}')">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button class="download-button" onclick="downloadServiceReceipt('{{ $payment->customer_id }}')">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>

        <div class="receipt-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo/logo2.png') }}" alt="Secondary Logo">
                <img src="{{ asset('images/logo/bi-u_logo.png') }}" alt="BI-U Logo">
            </div>
            <h1>Service Fee Receipt</h1>
            <p>{{ config('app.name', 'Water Billing System') }}</p>
            <p>Receipt Date: {{ now()->format('F d, Y') }}</p>
            <p>Receipt No: {{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td>Customer ID:</td>
                    <td>{{ $payment->customer_id }}</td>
                </tr>
                <tr>
                    <td>Block:</td>
                    <td>Block {{ $payment->consumer->block_id }}</td>
                </tr>
                <tr>
                    <td>Consumer Name:</td>
                    <td>{{ $payment->consumer->firstname }} {{ $payment->consumer->middlename }} {{ $payment->consumer->lastname }}</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>{{ $payment->consumer->address }}, Bien Unido</td>
                </tr>
                <tr>
                    <td>Payment Status:</td>
                    <td>{{ ucfirst($payment->service_paid_status) }}</td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td>{{ $payment->updated_at->format('F d, Y') }}</td>
                </tr>
            </table>

            <div class="amount-section">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td>Reconnection Fee:</td>
                        <td>₱{{ number_format($payment->reconnection_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Amount Paid:</td>
                        <td>₱{{ number_format($payment->service_amount_paid, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
        </div>
    </div>
</body>
</html> 