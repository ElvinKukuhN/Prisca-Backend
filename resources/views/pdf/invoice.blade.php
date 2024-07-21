<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .details,
        .items {
            margin-bottom: 20px;
        }

        .details table,
        .items table {
            width: 100%;
            border-collapse: collapse;
        }

        .details table th,
        .details table td,
        .items table th,
        .items table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .details table th,
        .items table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
            <p>{{ $payment->no_invoice }}</p>
        </div>

        <div class="details">
            <h2>Invoice Details</h2>
            <table>
                <tr>
                    <th>Status</th>
                    <td>{{ $payment->status }}</td>
                </tr>
                <tr>
                    <th>Total Payment</th>
                    <td>Rp {{ $total_bayar }}</td>
                </tr>
                <tr>
                    <th>Payment Due Date</th>
                    <td>{{ $batas_bayar }}</td>
                </tr>
            </table>
        </div>

        <div class="details">
            <h2>Buyer Details</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <td>{{ $buyer->name }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $buyer->telp }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $companyAddress }}</td>
                </tr>
            </table>
        </div>

        <div class="details">
            <h2>Vendor Details</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <td>{{ $vendor->name }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $vendor->telp }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $vendor->alamat }}</td>
                </tr>
                <tr>
                    <th>Bank</th>
                    <td>{{ $vendor->bank }}</td>
                </tr>
                <tr>
                    <th>Account Number</th>
                    <td>{{ $vendor->no_rek }}</td>
                </tr>
            </table>
        </div>

        <div class="items">
            <h2>Line Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lineItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ $item->price }}</td>
                            <td>Rp {{ $item->amount }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>&copy; 2024 Your Company Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
