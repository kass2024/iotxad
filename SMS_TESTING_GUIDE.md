# SMS Gateway Testing & Debugging Guide

## 🧪 **Testing Methods**

### 1. **Web Interface Testing**
- **URL**: `https://iotxad.com/sms-gateway`
- **Single SMS**: Enter phone + message → Send
- **Bulk SMS**: Upload Excel file + message → Send
- **Test Button**: Click "Test SMS" for quick testing

### 2. **Curl Testing**
```bash
# Test SMS endpoint
curl -X POST "https://iotxad.com/SmsGateway/testSMS" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "phone=250788123456&message=Test SMS from SMS Gateway"

# Single SMS endpoint
curl -X POST "https://iotxad.com/SmsGateway/sendSingleSMS" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "phone=250788123456&message=Hello World"
```

### 3. **Debug Information**
- Click "Debug Info" buttons in results
- View detailed API responses
- Check server logs for detailed information

## 🔍 **Debugging Features**

### **Enhanced Logging**
All SMS attempts are logged with:
- Phone numbers (original and cleaned)
- Message content (first 50 chars)
- Success/failure status
- API responses
- Timestamps

### **Debug Information Display**
- **Original Phone**: Input phone number
- **Cleaned Phone**: Formatted phone number
- **Message Length**: Character count
- **API Response**: Full SMS API response
- **Timestamp**: When SMS was sent

### **Error Handling**
- **Validation Errors**: Invalid phone numbers, empty messages
- **File Errors**: Invalid file types, size limits
- **API Errors**: SMS provider failures
- **Network Errors**: Connection issues

## 📊 **Success/Error Messages**

### **Success Response**
```json
{
  "success": true,
  "phone": "250788123456",
  "message": "SMS sent successfully",
  "debug": {
    "original_phone": "0788123456",
    "cleaned_phone": "250788123456",
    "message_length": 25,
    "timestamp": "2026-04-09 18:13:00"
  }
}
```

### **Error Response**
```json
{
  "success": false,
  "phone": "250788123456",
  "error": "Failed to send SMS",
  "debug": {
    "original_phone": "0788123456",
    "cleaned_phone": "250788123456",
    "api_response": {
      "code": 400,
      "content": "Insufficient balance"
    },
    "timestamp": "2026-04-09 18:13:00"
  }
}
```

## 🛠️ **Troubleshooting Steps**

### **1. Check Server Logs**
```bash
# View SMS gateway logs
tail -f writable/logs/log-*.php | grep "SMS Gateway"
```

### **2. Test with Debug Info**
1. Send a test SMS
2. Click "Debug Info" button
3. Review the debug information
4. Check API response codes

### **3. Verify SMS API Configuration**
- Check SMS API credentials in `.env`
- Verify SwiftQOM API key
- Test API connectivity

### **4. Phone Number Validation**
- Rwanda format: `2507xxxxxxxx` or `07xxxxxxxx`
- International format: `+2507xxxxxxxx`
- Check phone number formatting in debug info

### **5. Common Issues**

#### **SMS Not Sending**
- Check SMS API balance
- Verify phone number format
- Check network connectivity
- Review API response in debug

#### **File Upload Issues**
- Verify file format (.xlsx, .xls, .csv)
- Check file size (< 5MB)
- Ensure phone numbers in Column A
- Validate phone number formats

#### **Rate Limiting**
- 10 requests per IP per hour
- 100 numbers per bulk request
- Check cache configuration

## 📋 **Testing Checklist**

### **Pre-Deployment**
- [ ] Test single SMS with valid phone
- [ ] Test single SMS with invalid phone
- [ ] Test bulk SMS with Excel file
- [ ] Test with empty message
- [ ] Test with invalid file type
- [ ] Test rate limiting
- [ ] Check debug information display

### **Post-Deployment**
- [ ] Verify web interface loads
- [ ] Test curl commands
- [ ] Check server logs
- [ ] Monitor SMS delivery
- [ ] Verify error handling

## 🚨 **Monitoring**

### **Success Metrics**
- SMS sent count
- Delivery success rate
- Response time
- User satisfaction

### **Error Tracking**
- Failed SMS attempts
- API errors
- Validation failures
- Rate limiting hits

### **Log Analysis**
```bash
# Count SMS attempts
grep "SMS Gateway" writable/logs/log-*.php | wc -l

# Find errors
grep "SMS Gateway.*error" writable/logs/log-*.php

# Check success rate
grep "SMS Gateway.*successfully" writable/logs/log-*.php | wc -l
```

## 📞 **Support**

### **Debug Information to Collect**
1. **Phone Number**: Original input
2. **Debug Info**: Full JSON response
3. **Timestamp**: When error occurred
4. **Browser**: Chrome/Firefox/Safari
5. **Error Message**: Exact error text

### **Contact Information**
- Check server logs first
- Provide debug information
- Include curl command output
- Screenshot of error (if applicable)

---

**Note**: Always test with a real phone number to verify SMS delivery. Debug information helps identify issues quickly.
