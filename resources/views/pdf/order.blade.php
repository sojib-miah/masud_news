<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 14px;
            color: #111827;
            padding: 30px;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
            padding: 0;
        }

        .mb-5 {
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }

        .title {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .invoice-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 12px;
            font-size: 14px;
        }

        .invoice-table td {
            border: 1px solid #d1d5db;
            padding: 12px;
        }

        .summary-table {
            width: 320px;
            border-collapse: collapse;
            margin-top: 30px;
            margin-left: auto;
        }

        .summary-table td {
            padding: 10px;
            border: 1px solid #d1d5db;
        }

        .grand-total td {
            font-size: 18px;
            font-weight: bold;
            background: #f9fafb;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <table width="100%" class="mb-5">
        <tr>

            <!-- LEFT -->
            <td width="50%" valign="top">

                <div class="title">
                    INVOICE
                </div>

                <p>
                    <strong>Invoice ID:</strong>
                    #{{ $order->id }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Date:</strong>
                    {{ $order->created_at->format('d M Y') }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Status:</strong>
                    {{ ucfirst($order->status) }}
                </p>

            </td>

            <!-- RIGHT -->
            <td width="50%" valign="top" class="text-right">

                <div class="company-name">
                    AvnewsBD
                </div>

                <p>Motijil, Dhaka, Bangladesh</p>

                <p>support@example.com</p>

                <p>+880123456789</p>

            </td>

        </tr>
    </table>

    <!-- CUSTOMER -->
    <table width="100%" class="mb-5">
        <tr>

            <td width="50%" valign="top">

                <h3 style="margin-bottom:10px;">
                    Billing Information
                </h3>

                <p>
                    <strong>Name:</strong>
                    {{ $order->name }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Phone:</strong>
                    {{ $order->phone }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Email:</strong>
                    {{ $order->email ?? 'N/A' }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Address:</strong>
                    {{ $order->address ?? 'N/A' }}
                </p>

            </td>

            <td width="50%" valign="top" class="text-right">

                <h3 style="margin-bottom:10px;">
                    Order Information
                </h3>

                <p>
                    <strong>Payment:</strong>
                    {{ $order->payment_method ?? 'Cash On Delivery' }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Delivery:</strong>
                    {{ $order->delivery_type ?? 'Home Delivery' }}
                </p>

                <p style="margin-top:8px;">
                    <strong>Total Products:</strong>
                    {{ $order->items->count() }}
                </p>

            </td>

        </tr>
    </table>

    <!-- PRODUCTS -->
    <table class="invoice-table">

        <thead>
            <tr>
                <th align="left">Product</th>
                <th align="center">Price</th>
                <th align="center">Qty</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($order->items as $item)
                <tr>

                    <td>
                        {{ $item->product->name }}
                    </td>

                    <td align="center">
                        ৳{{ number_format($item->price, 2) }}
                    </td>

                    <td align="center">
                        {{ $item->quantity }}
                    </td>

                    <td align="right">
                        ৳{{ number_format($item->price * $item->quantity, 2) }}
                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    <!-- TOTAL -->
    <table class="summary-table">

        <tr>
            <td>
                Subtotal
            </td>

            <td align="right">
                ৳{{ number_format($order->total, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                Delivery Charge
            </td>

            <td align="right">
                ৳0.00
            </td>
        </tr>

        <tr class="grand-total">
            <td>
                Grand Total
            </td>

            <td align="right">
                ৳{{ number_format($order->total, 2) }}
            </td>
        </tr>

    </table>

    <!-- FOOTER -->
    <div class="footer">
        Thank you for your order from AvnewsBD.
    </div>

</body>

</html>
