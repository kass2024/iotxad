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

    public function sendSingleSMS()
    {
        $message = $this->request->getPost('message');
        $phone = $this->request->getPost('phone');
        
        if (empty($message)) {
            return $this->response->setJSON(['error' => 'Message cannot be empty']);
        }

        if (empty($phone)) {
            return $this->response->setJSON(['error' => 'Phone number cannot be empty']);
        }

        // Clean and validate phone number
        $phone = $this->cleanPhoneNumber($phone);
        
        if (!$phone) {
            return $this->response->setJSON(['error' => 'Invalid phone number format. Use Rwanda format: 2507xxxxxxxx or 07xxxxxxxx']);
        }

        // Send single SMS
        $result = [];
        $success = $this->sendSMS($phone, $message, $result);
        
        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'phone' => $phone,
                'message' => 'SMS sent successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'phone' => $phone,
                'error' => $result['content'] ?? 'Failed to send SMS'
            ]);
        }
    }

    public function sendBulkSMS()
    {
        // Rate limiting check
        $clientIP = $this->request->getIPAddress();
        $cacheKey = 'sms_gateway_' . str_replace('.', '_', $clientIP);
        
        if (function_exists('cache')) {
            $requestCount = cache($cacheKey) ?: 0;
            cache()->save($cacheKey, $requestCount + 1, 3600); // 1 hour expiry
            
            if ($requestCount >= $this->maxRequestsPerHour) {
                return $this->response->setJSON(['error' => 'Rate limit exceeded. Please try again later.']);
            }
        }
        
        $message = $this->request->getPost('message');
        $file = $this->request->getFile('excel_file');
        
        if (empty($message)) {
            return $this->response->setJSON(['error' => 'Message cannot be empty']);
        }

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['error' => 'Please upload a valid Excel file']);
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setJSON(['error' => 'File size too large. Maximum 5MB allowed.']);
        }

        // Check file type
        $allowedTypes = ['xlsx', 'xls', 'csv'];
        $fileExt = strtolower($file->getExtension());
        
        if (!in_array($fileExt, $allowedTypes)) {
            return $this->response->setJSON(['error' => 'Please upload a valid Excel file (.xlsx, .xls, .csv)']);
        }

        // Process the Excel file
        $numbers = $this->processExcelFile($file);
        
        if (empty($numbers)) {
            return $this->response->setJSON(['error' => 'No valid phone numbers found in the file']);
        }

        // Limit number of SMS per request
        if (count($numbers) > $this->maxNumbersPerRequest) {
            return $this->response->setJSON(['error' => 'Too many phone numbers. Maximum ' . $this->maxNumbersPerRequest . ' allowed per request.']);
        }

        // Send SMS to all numbers
        $results = $this->sendSMSToMultiple($numbers, $message);
        
        return $this->response->setJSON([
            'success' => true,
            'total' => count($numbers),
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'details' => $results['details']
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
