<?php
/**
 * ekiliSense Installation Wizard
 * Run this file once to set up the application
 * 
 * Access: http://your-domain.com/install.php
 * 
 * After installation, delete this file for security!
 */

// Check if already installed
if (file_exists('.env') && filesize('.env') > 0) {
    die('
        <h1>Already Installed</h1>
        <p>ekiliSense appears to be already installed.</p>
        <p>If you need to reinstall, please delete or rename the .env file first.</p>
        <p><strong>Security Note:</strong> Delete this install.php file!</p>
    ');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        // Test database connection
        $host = $_POST['db_host'] ?? 'localhost';
        $username = $_POST['db_username'] ?? '';
        $password = $_POST['db_password'] ?? '';
        $database = $_POST['db_name'] ?? '';
        
        try {
            $conn = new mysqli($host, $username, $password, $database);
            if ($conn->connect_error) {
                throw new Exception($conn->connect_error);
            }
            
            // Store in session for next step
            session_start();
            $_SESSION['install_data'] = $_POST;
            
            header('Location: install.php?step=3');
            exit;
        } catch (Exception $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Create .env file and run migrations
        session_start();
        $data = $_SESSION['install_data'] ?? [];
        
        if (empty($data)) {
            header('Location: install.php?step=1');
            exit;
        }
        
        // Create .env file
        $envContent = "# ekiliSense Configuration\n\n";
        $envContent .= "# Database\n";
        $envContent .= "DB_HOST={$data['db_host']}\n";
        $envContent .= "DB_USERNAME={$data['db_username']}\n";
        $envContent .= "DB_PASSWORD={$data['db_password']}\n";
        $envContent .= "DB_NAME={$data['db_name']}\n\n";
        $envContent .= "# Application\n";
        $envContent .= "APP_NAME=ekiliSense\n";
        $envContent .= "APP_ENVIRONMENT=" . ($data['environment'] ?? 'production') . "\n";
        $envContent .= "APP_URL=" . ($data['app_url'] ?? 'https://sense.ekilie.com') . "\n\n";
        $envContent .= "# Payment (Update these with your actual keys)\n";
        $envContent .= "PESAPAL_CONSUMER_KEY=your_key_here\n";
        $envContent .= "PESAPAL_CONSUMER_SECRET=your_secret_here\n";
        $envContent .= "PESAPAL_ENVIRONMENT=sandbox\n\n";
        
        if (file_put_contents('.env', $envContent)) {
            $success = "Configuration file created successfully!";
            
            // Try to run migrations
            try {
                require_once 'includes/init.php';
                require_once 'database/migrate.php';
                
                $migrationsPath = __DIR__ . '/database/migrations';
                $runner = new MigrationRunner($db, $migrationsPath);
                $runner->up();
                
                $success .= " Database migrations completed!";
                $step = 4; // Success step
            } catch (Exception $e) {
                $error = "Migrations failed: " . $e->getMessage() . ". You may need to run migrations manually.";
            }
        } else {
            $error = "Failed to create .env file. Please check write permissions.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ekiliSense Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-bottom: 3px solid #ddd;
            color: #999;
            font-size: 14px;
        }
        
        .step.active {
            border-color: #667eea;
            color: #667eea;
            font-weight: 600;
        }
        
        .step.completed {
            border-color: #10b981;
            color: #10b981;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-box ul {
            margin-left: 20px;
        }
        
        .info-box li {
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .success-icon {
            text-align: center;
            font-size: 64px;
            color: #10b981;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($step < 4): ?>
        <h1>ekiliSense Installation</h1>
        <p class="subtitle">Let's get your school management system up and running!</p>
        
        <div class="steps">
            <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
                1. Welcome
            </div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
                2. Database
            </div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">
                3. Install
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <div class="info-box">
                <h3>Before you begin, make sure you have:</h3>
                <ul>
                    <li>PHP 8.0 or higher installed</li>
                    <li>MySQL 8.0 or higher running</li>
                    <li>Database credentials ready</li>
                    <li>Write permissions in this directory</li>
                </ul>
            </div>
            
            <form method="GET" action="install.php">
                <input type="hidden" name="step" value="2">
                <button type="submit" class="btn">Start Installation →</button>
            </form>
            
        <?php elseif ($step == 2): ?>
            <form method="POST" action="install.php?step=2">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <p class="help-text">Usually 'localhost' for local installations</p>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" value="ekiliSense_db" required>
                    <p class="help-text">Create this database before installing</p>
                </div>
                
                <div class="form-group">
                    <label for="db_username">Database Username</label>
                    <input type="text" id="db_username" name="db_username" required>
                </div>
                
                <div class="form-group">
                    <label for="db_password">Database Password</label>
                    <input type="password" id="db_password" name="db_password">
                </div>
                
                <div class="form-group">
                    <label for="app_url">Application URL</label>
                    <input type="url" id="app_url" name="app_url" value="<?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="environment">Environment</label>
                    <select id="environment" name="environment">
                        <option value="production">Production</option>
                        <option value="development">Development</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Test Connection & Continue →</button>
            </form>
            
        <?php elseif ($step == 3): ?>
            <div class="info-box">
                <h3>Ready to install!</h3>
                <p style="color: #666; font-size: 14px;">
                    This will create your configuration file and set up the database tables.
                </p>
            </div>
            
            <form method="POST" action="install.php?step=3">
                <button type="submit" class="btn">Run Installation →</button>
            </form>
            
        <?php elseif ($step == 4): ?>
            <div class="success-icon">✓</div>
            <h1 style="text-align: center;">Installation Complete!</h1>
            <p class="subtitle" style="text-align: center;">ekiliSense is ready to use</p>
            
            <div class="info-box">
                <h3>Important Security Steps:</h3>
                <ul>
                    <li><strong>Delete this install.php file immediately!</strong></li>
                    <li>Change default credentials after first login</li>
                    <li>Configure your payment gateway settings in .env</li>
                    <li>Set up SSL/HTTPS for production</li>
                </ul>
            </div>
            
            <a href="onboarding/" style="text-decoration: none;">
                <button type="button" class="btn">Get Started →</button>
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
