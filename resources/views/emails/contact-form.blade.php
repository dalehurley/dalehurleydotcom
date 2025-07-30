<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Form Submission</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #FF750F, #E5670D);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }

        .field {
            margin-bottom: 20px;
        }

        .field-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .field-value {
            background: white;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .message-field {
            white-space: pre-wrap;
            line-height: 1.5;
        }

        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">New Contact Form Submission</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">From your website: DaleHurley.com</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $contactData['name'] }}</div>
        </div>

        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">
                <a href="mailto:{{ $contactData['email'] }}" style="color: #FF750F; text-decoration: none;">
                    {{ $contactData['email'] }}
                </a>
            </div>
        </div>

        <div class="field">
            <div class="field-label">Message:</div>
            <div class="field-value message-field">{{ $contactData['message'] }}</div>
        </div>
    </div>

    <div class="footer">
        <p>This message was sent from the contact form on your website.</p>
        <p>Timestamp: {{ now()->format('F j, Y \a\t g:i A T') }}</p>
    </div>
</body>

</html>
