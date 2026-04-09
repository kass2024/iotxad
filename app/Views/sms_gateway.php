<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk SMS Gateway - IOTXAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            padding-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 20px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            background: #e9ecef;
            border-color: #764ba2;
        }
        .upload-area.dragover {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .character-count {
            font-size: 0.9em;
            color: #6c757d;
        }
        .result-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .result-item.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .result-item.failed {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 3rem;
            color: #667eea;
        }
        .instructions {
            background: #e8f5e8;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .instructions h6 {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="logo">
                    <i class="fas fa-sms"></i>
                    <h2 class="mt-2 text-white">SMS Gateway</h2>
                    <p class="text-white-50">Send SMS to single or multiple contacts</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>SMS Gateway</h4>
                    </div>
                    <div class="card-body">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="smsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-sms" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>Single SMS
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-sms" type="button" role="tab">
                                    <i class="fas fa-users me-2"></i>Bulk SMS
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="smsTabsContent">
                            <!-- Single SMS Tab -->
                            <div class="tab-pane fade show active" id="single-sms" role="tabpanel">
                                <div class="instructions">
                                    <h6><i class="fas fa-info-circle me-2"></i>Single SMS Instructions:</h6>
                                    <ul class="mb-0">
                                        <li>Enter a single phone number</li>
                                        <li>Phone number format: 2507xxxxxxxx or 07xxxxxxxx</li>
                                        <li>Maximum 160 characters per SMS</li>
                                    </ul>
                                </div>

                                <form id="singleSmsForm">
                                    <div class="mb-4">
                                        <label for="single_phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="single_phone" name="phone" 
                                            placeholder="Enter phone number (e.g., 250788123456)" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="single_message" class="form-label">
                                            <i class="fas fa-comment me-2"></i>Message
                                        </label>
                                        <textarea class="form-control" id="single_message" name="message" rows="4" 
                                            placeholder="Enter your SMS message here..." maxlength="160" required></textarea>
                                        <div class="character-count">
                                            <span id="singleCharCount">0</span> / 160 characters
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Send Single SMS
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Bulk SMS Tab -->
                            <div class="tab-pane fade" id="bulk-sms" role="tabpanel">
                                <div class="instructions">
                                    <h6><i class="fas fa-info-circle me-2"></i>Bulk SMS Instructions:</h6>
                                    <ul class="mb-0">
                                        <li>Upload an Excel file with phone numbers in the first column (Column A)</li>
                                        <li>Phone numbers should be in format: 2507xxxxxxxx or 07xxxxxxxx</li>
                                        <li>Maximum 160 characters per SMS</li>
                                        <li>Supported formats: .xlsx, .xls, .csv</li>
                                        <li>Maximum 100 numbers per request</li>
                                    </ul>
                                </div>

                                <form id="bulkSmsForm" enctype="multipart/form-data">
                                    <div class="mb-4">
                                        <label for="excel_file" class="form-label">
                                            <i class="fas fa-file-excel me-2"></i>Upload Excel File
                                        </label>
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                                            <h5>Drop your Excel file here or click to browse</h5>
                                            <p class="text-muted">Supported formats: .xlsx, .xls, .csv</p>
                                            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" style="display: none;">
                                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('excel_file').click()">
                                                <i class="fas fa-folder-open me-2"></i>Choose File
                                            </button>
                                        </div>
                                        <div id="fileInfo" class="mt-2"></div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="bulk_message" class="form-label">
                                            <i class="fas fa-comment me-2"></i>Message
                                        </label>
                                        <textarea class="form-control" id="bulk_message" name="message" rows="4" 
                                            placeholder="Enter your SMS message here..." maxlength="160" required></textarea>
                                        <div class="character-count">
                                            <span id="bulkCharCount">0</span> / 160 characters
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Send Bulk SMS
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="loading" id="loading">
                            <div class="spinner"></div>
                            <p class="mt-3">Sending SMS messages... Please wait.</p>
                        </div>

                        <div id="results" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter
        document.getElementById('message').addEventListener('input', function() {
            const length = this.value.length;
            document.getElementById('charCount').textContent = length;
            
            if (length > 140) {
                document.getElementById('charCount').style.color = '#dc3545';
            } else if (length > 120) {
                document.getElementById('charCount').style.color = '#ffc107';
            } else {
                document.getElementById('charCount').style.color = '#6c757d';
            }
        });

        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('excel_file');
        const fileInfo = document.getElementById('fileInfo');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                              'application/vnd.ms-excel', 
                              'text/csv'];
            
            if (!validTypes.includes(file.type)) {
                fileInfo.innerHTML = '<div class="alert alert-danger">Please upload a valid Excel file</div>';
                return;
            }

            fileInfo.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>File selected:</strong> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                </div>
            `;
        }

        // Single SMS form submission
        document.getElementById('singleSmsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            
            loading.style.display = 'block';
            results.innerHTML = '';
            
            try {
                const response = await fetch('<?= site_url('SmsGateway/sendSingleSMS') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                loading.style.display = 'none';
                
                if (data.success) {
                    results.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>SMS Sent Successfully!</h5>
                            <p><strong>To:</strong> ${data.phone}</p>
                            <p><strong>Message:</strong> ${data.message}</p>
                        </div>
                    `;
                    // Clear form
                    this.reset();
                    document.getElementById('singleCharCount').textContent = '0';
                } else {
                    results.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.error}
                        </div>
                    `;
                }
            } catch (error) {
                loading.style.display = 'none';
                results.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred while sending SMS. Please try again.
                    </div>
                `;
            }
        });

        // Bulk SMS form submission
        document.getElementById('bulkSmsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            
            loading.style.display = 'block';
            results.innerHTML = '';
            
            try {
                const response = await fetch('<?= site_url('SmsGateway/sendBulkSMS') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                loading.style.display = 'none';
                
                if (data.success) {
                    results.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Bulk SMS Sending Complete!</h5>
                            <p>Total: ${data.total} | Sent: ${data.sent} | Failed: ${data.failed}</p>
                        </div>
                        <div class="mt-3">
                            <h6>Details:</h6>
                            ${data.details.map(item => `
                                <div class="result-item ${item.status}">
                                    <strong>${item.phone}:</strong> ${item.message}
                                </div>
                            `).join('')}
                        </div>
                    `;
                    // Clear file info and message
                    fileInfo.innerHTML = '';
                    document.getElementById('bulk_message').value = '';
                    document.getElementById('bulkCharCount').textContent = '0';
                } else {
                    results.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.error}
                        </div>
                    `;
                }
            } catch (error) {
                loading.style.display = 'none';
                results.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred while sending SMS. Please try again.
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
