<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Required — {{ $quotation['quoteNo'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .wrapper {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #4B49AC;
            padding: 24px 32px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 20px;
            margin: 0;
            font-weight: 600;
        }

        .body {
            padding: 32px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .summary-table td {
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #e0e0e0;
        }

        .summary-table td:first-child {
            font-weight: 600;
            background-color: #f4f4fb;
            width: 40%;
            color: #4B49AC;
        }

        .attachment-notice {
            font-size: 13px;
            color: #666;
            margin: 16px 0;
            padding: 10px 14px;
            background-color: #f9f9f9;
            border-left: 3px solid #4B49AC;
            border-radius: 3px;
        }

        .actions {
            text-align: center;
            margin: 28px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            margin: 0 8px;
        }

        .btn-approve {
            background-color: #28a745;
            color: #ffffff;
        }

        .btn-reject {
            background-color: #dc3545;
            color: #ffffff;
        }

        .footer {
            background-color: #f4f4f4;
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <h1>Quotation Approval Request</h1>
        </div>

        <div class="body">

            <p class="greeting">Dear {{ $approverName }},</p>

            <p>
                Quotation <strong>{{ $quotation['quoteNo'] }}</strong> has been submitted
                for your approval. Please review the details below.
            </p>

            <table class="summary-table">
                <tr>
                    <td>Quotation No</td>
                    <td>{{ $quotation['quoteNo'] }}</td>
                </tr>
                <tr>
                    <td>Customer</td>
                    <td>{{ $quotation['customer'] }}</td>
                </tr>
                <tr>
                    <td>Revenue</td>
                    <td>{{ $quotation['currency'] }} {{ number_format($quotation['revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td>Cost</td>
                    <td>{{ $quotation['currency'] }} {{ number_format($quotation['cost'], 2) }}</td>
                </tr>
                <tr>
                    <td>Profit</td>
                    <td>{{ $quotation['currency'] }} {{ number_format($quotation['profit'], 2) }}</td>
                </tr>
                <tr>
                    <td>Margin</td>
                    <td>{{ $quotation['margin'] }}%</td>
                </tr>
            </table>

            <div class="attachment-notice">
                📎 The quotation document has been attached to this email for your review.
            </div>

            <div class="actions">
                <a href="{{ $approveUrl }}" class="btn btn-approve">✔ Approve</a>
                <a href="{{ $rejectUrl }}" class="btn btn-reject">✘ Reject</a>
            </div>

            <p style="font-size:13px; color:#666; text-align:center;">
                Clicking Approve or Reject will open the application where you can
                confirm your decision.
            </p>

        </div>

        <div class="footer">
            This is an automated notification from {{ config('app.name') }}.<br>
            Please do not reply to this email.
        </div>

    </div>
</body>

</html>
