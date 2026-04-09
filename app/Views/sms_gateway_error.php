<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Limit Exceeded - SMS Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .countdown {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .btn-retry {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-retry:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Rate Limit Exceeded</h2>
        <p class="text-muted"><?= $error ?></p>
        
        <div class="countdown" id="countdown">
            <i class="fas fa-clock"></i>
            <span id="timer">Loading...</span>
        </div>
        
        <p class="text-muted">You can try again after the countdown completes.</p>
        
        <button class="btn btn-retry" id="retryBtn" disabled>
            <i class="fas fa-redo me-2"></i>Retry Now
        </button>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                For security reasons, we limit the number of SMS requests per hour.
            </small>
        </div>
    </div>

    <script>
        const retryAfter = <?= $retry_after ?? 3600 ?>; // seconds
        const retryBtn = document.getElementById('retryBtn');
        const timerElement = document.getElementById('timer');
        
        let secondsLeft = retryAfter;
        
        function updateCountdown() {
            const hours = Math.floor(secondsLeft / 3600);
            const minutes = Math.floor((secondsLeft % 3600) / 60);
            const seconds = secondsLeft % 60;
            
            let timeString = '';
            if (hours > 0) {
                timeString += `${hours}h `;
            }
            if (minutes > 0 || hours > 0) {
                timeString += `${minutes}m `;
            }
            timeString += `${seconds}s`;
            
            timerElement.textContent = timeString;
            
            if (secondsLeft <= 0) {
                retryBtn.disabled = false;
                timerElement.textContent = 'Ready!';
                document.querySelector('.countdown i').className = 'fas fa-check-circle';
            } else {
                secondsLeft--;
                setTimeout(updateCountdown, 1000);
            }
        }
        
        retryBtn.addEventListener('click', function() {
            if (!this.disabled) {
                window.location.reload();
            }
        });
        
        // Start countdown
        updateCountdown();
    </script>
</body>
</html>
