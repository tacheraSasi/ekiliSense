# SMS Notification Setup Guide

This guide will help you configure and use SMS notifications in ekiliSense.

## Overview

The SMS notification feature allows you to send announcements via SMS to teachers and parents using the Notify Africa API. You can choose to send notifications via:
- Email only
- SMS only
- Both Email and SMS

## Prerequisites

1. A Notify Africa API account
2. API token from Notify Africa
3. Configured sender ID

## Configuration Steps

### 1. Create or Update `.env` File

If you don't have a `.env` file in the root directory, create one by copying `.env.example`:

```bash
cp .env.example .env
```

### 2. Configure SMS API Settings

Open the `.env` file and update the following SMS configuration values:

```env
# SMS Configuration (Optional - for 2FA and notifications)
SMS_PROVIDER=notify_africa

# Notify Africa API Configuration
NOTIFY_AFRICA_API_URL=https://example.com/v2
NOTIFY_AFRICA_API_TOKEN=your_actual_api_token_here
NOTIFY_AFRICA_SENDER_ID=your_sender_id
```

Replace:
- `https://example.com/v2` with your actual Notify Africa API URL
- `your_actual_api_token_here` with your Notify Africa API token
- `your_sender_id` with your configured sender ID

### 3. Ensure Phone Numbers Are Configured

Make sure phone numbers are properly configured in your system:
- **Teachers**: `teacher_active_phone` field in the database
- **Parents**: `parent_phone` field in the database

Phone numbers should be in international format (e.g., 255712345678 for Tanzania).

## Using SMS Notifications

### Sending Notifications

1. Navigate to **Console → Announcements**
2. Fill in the notification form:
   - **Title**: Subject of your notification
   - **Message**: Content of your notification
3. Select **Notification Type**:
   - **Email**: Send via email only (default)
   - **SMS (Premium)**: Send via SMS only
   - **BOTH (Premium)**: Send via both email and SMS
4. Select **Send To**:
   - **Teacher**: All teachers
   - **Parent**: All parents
   - **Class Teacher**: All class teachers
5. Click **Send Notification**

### SMS Message Format

SMS messages are automatically formatted as:

```
[Title]

Dear [Name],

[Message]

Best regards,
[School Name] @ekiliSense
```

**Note**: SMS messages are limited to 160 characters. Longer messages will be automatically truncated with "..." at the end.

## Troubleshooting

### SMS Not Sending

1. **Check API Token**: Ensure your `NOTIFY_AFRICA_API_TOKEN` is correctly set in `.env`
2. **Check Phone Numbers**: Verify that phone numbers are stored in the database
3. **Check Logs**: Look for error messages in your PHP error log
4. **API Status**: Verify that the Notify Africa API is accessible

### Common Issues

- **"SMS API token not configured"**: Update your `.env` file with a valid API token
- **"Invalid phone number format"**: Ensure phone numbers contain only digits
- **No SMS received**: Check that the phone numbers are in the correct international format

## API Integration Details

The SMS feature uses the Notify Africa API with the following specifications:

**Endpoint**: `POST {base_url}/send-sms`

**Headers**:
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {api_token}
```

**Payload**:
```json
{
  "sender_id": 1,
  "schedule": "none",
  "sms": "Message content",
  "recipients": [
    {"number": 255712345678}
  ]
}
```

## Best Practices

1. **Test First**: Send test messages to verify your configuration
2. **Monitor Costs**: SMS messages may incur costs - monitor your usage
3. **Phone Number Validation**: Ensure phone numbers are valid before sending
4. **Message Length**: Keep messages concise to avoid truncation
5. **Bulk Sending**: Be aware of API rate limits when sending to many recipients

## Support

For issues related to:
- **ekiliSense SMS Feature**: Check the application logs and verify configuration
- **Notify Africa API**: Contact Notify Africa support
- **Phone Number Issues**: Verify data in your database

## Security Notes

- **Never commit** your `.env` file with actual API tokens to version control
- Keep your API token secure and don't share it
- Regularly rotate your API tokens for security
- Use environment variables for all sensitive configuration

---

For more information, see:
- [ekiliSense Documentation](../../README.md)
- [Notify Africa API Documentation](https://notifyafrica.com/docs)
