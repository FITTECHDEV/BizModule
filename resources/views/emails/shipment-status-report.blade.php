<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Status Report</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }
        .email-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
        }
        .header {
            background-color: #003366;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
        }
        .content {
            padding: 30px 25px;
            line-height: 1.6;
        }
        .greeting {
            margin-bottom: 20px;
            font-size: 16px;
        }
        .footer {
            padding: 20px 25px;
            background-color: #f8f8f8;
            border-top: 1px solid #dddddd;
            font-size: 14px;
        }
        .signature {
            margin-top: 15px;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
            }
            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Shipment Status Report</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                <strong>Dear Customer,</strong>
            </div>
            
            <p>
                Kindly find attached the shipment status report for <strong>{{ $serviceType }}</strong> shipments as of <strong>Today i.e. {{ $reportDate }}</strong>.
            </p>
            
            <p>
                The report includes all <strong>{{ $serviceType }} shipments</strong> along with their respective milestone statuses.
            </p>
            
            <p>
                We appreciate your time in reviewing the information provided. Please feel free to contact us with any queries or suggestions.
            </p>
        </div>
        
        <div class="footer">
            <div class="signature">
                Best regards,<br>
                <strong>Customer Care</strong><br>
                <strong>UG</strong>
            </div>
        </div>
    </div>
</body>
</html>