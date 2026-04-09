<?php namespace App\Controllers;

use App\Models\SmsModel;
use App\Models\SmsRecipientModel;
use App\Models\SchoolModel;

class SmsGateway extends BaseController
{
    private $maxNumbersPerRequest = 100;
    private $maxRequestsPerHour = 10;
    
    public function index()
    {
        // Simple rate limiting check
        $clientIP = $this->request->getIPAddress();
        $cacheKey = 'sms_gateway_' . str_replace('.', '_', $clientIP);
        
        if (!function_exists('cache')) {
            // Fallback if cache is not available
            return view('sms_gateway');
        }
        
        $requestCount = cache($cacheKey) ?: 0;
        
        if ($requestCount >= $this->maxRequestsPerHour) {
            return view('sms_gateway_error', [
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => 3600 // 1 hour
            ]);
        }
        
        return view('sms_gateway');
    }

    public function testSMS()
    {
        // Test endpoint for curl testing
        $phone = $this->request->getPost('phone') ?? '250788123456';
        $message = $this->request->getPost('message') ?? 'Test SMS from SMS Gateway at ' . date('Y-m-d H:i:s');
        
        log_message('info', 'SMS Gateway: Test SMS request - Phone: ' . $phone);
        
        // Clean and validate phone number
        $originalPhone = $phone;
        $phone = $this->cleanPhoneNumber($phone);
        
        if (!$phone) {
            log_message('error', 'SMS Gateway: Test failed - Invalid phone format: ' . $originalPhone);
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid phone number format',
                'original_phone' => $originalPhone
            ]);
        }

        // Send test SMS
        $result = [];
        $success = $this->sendSMS($phone, $message, $result);
        
        log_message('info', 'SMS Gateway: Test SMS result - Success: ' . ($success ? 'true' : 'false'));
        
        return $this->response->setJSON([
            'success' => $success,
            'phone' => $phone,
            'message' => $message,
            'result' => $result,
            'timestamp' => date('Y-m-d H:i:s'),
            'debug_info' => [
                'original_phone' => $originalPhone,
                'cleaned_phone' => $phone,
                'message_length' => strlen($message),
                'api_response' => $result
            ]
        ]);
    }

    public function sendSingleSMS()
    {
        $message = $this->request->getPost('message');
        $phone = $this->request->getPost('phone');
        
        // Log the attempt
        log_message('info', 'SMS Gateway: Single SMS attempt - Phone: ' . $phone . ', Message: ' . substr($message, 0, 50) . '...');
        
        if (empty($message)) {
            log_message('error', 'SMS Gateway: Empty message');
            return $this->response->setJSON(['error' => 'Message cannot be empty']);
        }

        if (empty($phone)) {
            log_message('error', 'SMS Gateway: Empty phone number');
            return $this->response->setJSON(['error' => 'Phone number cannot be empty']);
        }

        // Clean and validate phone number
        $originalPhone = $phone;
        $phone = $this->cleanPhoneNumber($phone);
        
        if (!$phone) {
            log_message('error', 'SMS Gateway: Invalid phone format - ' . $originalPhone);
            return $this->response->setJSON(['error' => 'Invalid phone number format. Use Rwanda format: 2507xxxxxxxx or 07xxxxxxxx']);
        }

        log_message('info', 'SMS Gateway: Sending SMS to ' . $phone . ' with message length: ' . strlen($message));

        // Send single SMS
        $result = [];
        $success = $this->sendSMS($phone, $message, $result);
        
        if ($success) {
            log_message('info', 'SMS Gateway: SMS sent successfully to ' . $phone);
            return $this->response->setJSON([
                'success' => true,
                'phone' => $phone,
                'message' => 'SMS sent successfully',
                'debug' => [
                    'original_phone' => $originalPhone,
                    'cleaned_phone' => $phone,
                    'message_length' => strlen($message),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            log_message('error', 'SMS Gateway: Failed to send SMS to ' . $phone . ' - ' . ($result['content'] ?? 'Unknown error'));
            return $this->response->setJSON([
                'success' => false,
                'phone' => $phone,
                'error' => $result['content'] ?? 'Failed to send SMS',
                'debug' => [
                    'original_phone' => $originalPhone,
                    'cleaned_phone' => $phone,
                    'api_response' => $result,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }

    public function sendBulkSMS()
    {
        // Rate limiting check
        $clientIP = $this->request->getIPAddress();
        $cacheKey = 'sms_gateway_' . str_replace('.', '_', $clientIP);
        
        log_message('info', 'SMS Gateway: Bulk SMS attempt from IP: ' . $clientIP);
        
        if (function_exists('cache')) {
            $requestCount = cache($cacheKey) ?: 0;
            cache()->save($cacheKey, $requestCount + 1, 3600); // 1 hour expiry
            
            if ($requestCount >= $this->maxRequestsPerHour) {
                log_message('error', 'SMS Gateway: Rate limit exceeded for IP: ' . $clientIP);
                return $this->response->setJSON(['error' => 'Rate limit exceeded. Please try again later.']);
            }
        }
        
        $message = $this->request->getPost('message');
        $file = $this->request->getFile('excel_file');
        
        log_message('info', 'SMS Gateway: Processing bulk SMS - Message length: ' . strlen($message));
        
        if (empty($message)) {
            log_message('error', 'SMS Gateway: Empty message in bulk SMS');
            return $this->response->setJSON(['error' => 'Message cannot be empty']);
        }

        if (!$file || !$file->isValid()) {
            log_message('error', 'SMS Gateway: Invalid file upload');
            return $this->response->setJSON(['error' => 'Please upload a valid Excel file']);
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            log_message('error', 'SMS Gateway: File too large - ' . ($file->getSize() / 1024 / 1024) . 'MB');
            return $this->response->setJSON(['error' => 'File size too large. Maximum 5MB allowed.']);
        }

        // Check file type
        $allowedTypes = ['xlsx', 'xls', 'csv'];
        $fileExt = strtolower($file->getExtension());
        
        if (!in_array($fileExt, $allowedTypes)) {
            log_message('error', 'SMS Gateway: Invalid file type - ' . $fileExt);
            return $this->response->setJSON(['error' => 'Please upload a valid Excel file (.xlsx, .xls, .csv)']);
        }

        // Process the Excel file
        $numbers = $this->processExcelFile($file);
        
        log_message('info', 'SMS Gateway: Found ' . count($numbers) . ' valid phone numbers in file');
        
        if (empty($numbers)) {
            log_message('error', 'SMS Gateway: No valid phone numbers found in file');
            return $this->response->setJSON(['error' => 'No valid phone numbers found in the file']);
        }

        // Limit number of SMS per request
        if (count($numbers) > $this->maxNumbersPerRequest) {
            log_message('error', 'SMS Gateway: Too many numbers - ' . count($numbers) . ' (max: ' . $this->maxNumbersPerRequest . ')');
            return $this->response->setJSON(['error' => 'Too many phone numbers. Maximum ' . $this->maxNumbersPerRequest . ' allowed per request.']);
        }

        // Send SMS to all numbers
        $results = $this->sendSMSToMultiple($numbers, $message);
        
        log_message('info', 'SMS Gateway: Bulk SMS completed - Sent: ' . $results['sent'] . ', Failed: ' . $results['failed']);
        
        return $this->response->setJSON([
            'success' => true,
            'total' => count($numbers),
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'details' => $results['details'],
            'debug' => [
                'file_name' => $file->getName(),
                'file_size' => $file->getSize(),
                'numbers_processed' => count($numbers),
                'message_length' => strlen($message),
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    private function processExcelFile($file)
    {
        $numbers = [];
        $filePath = $file->getTempName();
        
        try {
            // Load the spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            // Look for phone numbers in the first column (column A)
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellValue = $worksheet->getCell('A' . $row)->getValue();
                
                if (!empty($cellValue)) {
                    // Clean and validate phone number
                    $phone = $this->cleanPhoneNumber($cellValue);
                    if ($phone && !in_array($phone, $numbers)) {
                        $numbers[] = $phone;
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Excel processing error: ' . $e->getMessage());
        }
        
        return $numbers;
    }

    private function cleanPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading zeros and add country code if needed
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = '25' . $phone;
        } elseif (strlen($phone) == 9 && substr($phone, 0, 1) != '2') {
            $phone = '250' . $phone;
        }
        
        // Validate Rwanda phone numbers (should start with 250 and be 12 digits)
        if (strlen($phone) == 12 && substr($phone, 0, 3) == '250') {
            return $phone;
        }
        
        return null;
    }

    private function sendSMSToMultiple($numbers, $message)
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($numbers as $phone) {
            $result = [];
            $success = $this->sendSMS($phone, $message, $result);
            
            if ($success) {
                $results['sent']++;
                $results['details'][] = [
                    'phone' => $phone,
                    'status' => 'success',
                    'message' => 'SMS sent successfully'
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'phone' => $phone,
                    'status' => 'failed',
                    'message' => $result['content'] ?? 'Failed to send SMS'
                ];
            }
            
            // Small delay to prevent API rate limiting
            usleep(100000); // 0.1 second
        }

        return $results;
    }
}
