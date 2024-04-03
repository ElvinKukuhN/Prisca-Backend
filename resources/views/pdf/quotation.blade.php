<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1;
            background-color: #f8f8f8;
            color: #333;
        }

        .quotation {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }

        .header,
        .footer {
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1e90ff;
            text-align: center;
        }

        .address {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

        .content {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .content .info {
            flex: 1;
        }

        .title-2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1e90ff;
        }

        p {
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .total-row,
        .discount-row {
            font-weight: bold;
        }

        .discount-row {
            font-style: italic;
            color: #888;
        }

        .footer p {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="quotation">
        <div class="header">
            <div class="title">Quotation</div>
        </div>
        <div>
            <div class="content">
                <div class="info">
                    <div class="title-2">Quotation By:</div>
                    <p>{{ $vendor_name }}</p>
                    <p>{{ $vendor_address }}</p>
                </div>
                <div class="info">
                    <div class="title-2">Quotation To:</div>
                    <p>{{ $company_name }}</p>
                    <p>{{ $company_address }}</p>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Qty.</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotationItem as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 2, ',', '.') }}</td>
                            <td>{{ number_format($item->quantity * $item->price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3">Total:</td>
                        <td>{{ number_format($quotationItem->sum('amount'), 2, ',', '.') }}</td>
                    </tr>
                </tfoot>

            </table>
        </div>
        <div class="footer">
            <p>Note: All prices are exclusive of taxes.</p>
            <p>Payment terms: 50% advance and 50% on completion.</p>
            <p>Validity: 15 days from date of issue.</p>
        </div>
    </div>
</body>

</html>
