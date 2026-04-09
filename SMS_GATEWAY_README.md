# Bulk SMS Gateway

A public SMS sending interface that allows sending bulk SMS messages by uploading an Excel file with phone numbers.

## Features

- **Excel File Upload**: Support for .xlsx, .xls, and .csv files
- **Phone Number Validation**: Automatically formats and validates Rwanda phone numbers
- **Bulk SMS Sending**: Send SMS to multiple recipients simultaneously
- **Real-time Progress**: See sending progress and results
- **Character Counter**: Track SMS message length (160 character limit)
- **Modern UI**: Beautiful, responsive interface with drag-and-drop support

## Access URL

```
https://your-domain.com/sms-gateway
```

## How to Use

### 1. Prepare Your Excel File
- Create an Excel file with phone numbers in the first column (Column A)
- Phone numbers can be in formats:
  - 2507xxxxxxxx (preferred)
  - 07xxxxxxxx
  - +2507xxxxxxxx
- Each row should contain one phone number

### 2. Upload and Send
1. Visit the SMS gateway URL
2. Upload your Excel file (drag & drop or click to browse)
3. Type your SMS message (max 160 characters)
4. Click "Send Bulk SMS"
5. Monitor the sending progress and results

## SMS API Integration

The gateway uses your existing SMS API configuration:

- **SwiftQOM API**: Primary SMS service
- **Phone Number Format**: Rwanda numbers (250xxxxxxxxx)
- **Rate Limiting**: Built-in delays to prevent API abuse
- **Error Handling**: Detailed error reporting for failed messages

## Technical Details

### Controller: `SmsGateway.php`
- **File Processing**: Uses PhpSpreadsheet library
- **Phone Validation**: Cleans and validates phone numbers
- **SMS Sending**: Integrates with existing `sendSMS()` method
- **Error Handling**: Comprehensive error tracking

### View: `sms_gateway.php`
- **Modern Design**: Bootstrap 5 with custom styling
- **Drag & Drop**: File upload with visual feedback
- **Character Counter**: Real-time SMS length tracking
- **Progress Tracking**: AJAX-based sending with live updates

### Route Configuration
```php
$routes->get('sms-gateway', 'SmsGateway::index');
$routes->post('SmsGateway/sendBulkSMS', 'SmsGateway::sendBulkSMS');
```

## Security Considerations

- **Public Access**: The gateway is publicly accessible
- **File Validation**: Only Excel files are accepted
- **Rate Limiting**: Built-in delays prevent spam
- **Input Sanitization**: All inputs are validated and cleaned

## File Structure

```
app/
 Controllers/
  SmsGateway.php          # Main controller
 Views/
  sms_gateway.php         # Frontend interface
 Config/
  Routes.php              # Route configuration
```

## Dependencies

- **PhpSpreadsheet**: For Excel file processing
- **Bootstrap 5**: For UI components
- **Font Awesome**: For icons
- **Existing SMS API**: Uses your current SMS sending infrastructure

## Troubleshooting

### File Upload Issues
- Ensure file format is .xlsx, .xls, or .csv
- Check file size (recommended < 5MB)
- Verify phone numbers are in the first column

### SMS Sending Issues
- Check SMS API credentials in BaseController
- Verify phone number format (250xxxxxxxxx)
- Monitor error messages in results section

### Performance Issues
- Large files may take longer to process
- Consider splitting very large contact lists
- Monitor API rate limits

## Customization

### Styling
- Modify `sms_gateway.php` for custom colors and layout
- Update CSS variables for theme changes

### SMS Content
- Character limit can be adjusted in the view
- Message validation can be enhanced in controller

### Phone Validation
- Add support for other country formats
- Modify `cleanPhoneNumber()` method

## Support

For issues or questions:
1. Check the browser console for JavaScript errors
2. Review server logs for PHP errors
3. Verify SMS API configuration
4. Test with a small contact list first

---

**Note**: This gateway uses your existing SMS infrastructure and billing. Monitor usage to avoid unexpected charges.
