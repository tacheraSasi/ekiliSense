<?php

// === CONFIG ===
define('ERROR_EMAIL_TO', 'tacherasasi@gmail.com');
define('ERROR_EMAIL_FROM', 'support@ekilie.com');
define('SEND_ERRORS', false); // Will Toggle this on in production
define('ERROR_SUBJECT_PREFIX', '[PROD ERROR]');
define('RATE_LIMIT_SECONDS', 60);
define('RATE_LIMIT_CACHE', __DIR__ . '/.last_error_time');

// === handlers ===
set_exception_handler('handle_exception');
set_error_handler('handle_error');
register_shutdown_function('handle_shutdown');

function handle_exception($exception)
{
    $message = "Uncaught Exception: " . $exception->getMessage();
    $trace = $exception->getTraceAsString();
    log_and_email_error($message, $trace);
}

function handle_error($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) return;
    $trace = "In $file on line $line";
    log_and_email_error("PHP Error: $message", $trace);
}

function handle_shutdown()
{
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = "Fatal Error: {$error['message']}";
        $trace = "In {$error['file']} on line {$error['line']}";
        log_and_email_error($message, $trace);
    }
}

function log_and_email_error($message, $trace)
{
    if (!SEND_ERRORS || !should_send_email()) return;

    $time = date('Y-m-d H:i:s');
    $subject = ERROR_SUBJECT_PREFIX . " $message";
    $body = <<<EOT
[$time]

$message

Stack trace:
$trace

REQUEST:
{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}
IP: {$_SERVER['REMOTE_ADDR']}
User Agent: {$_SERVER['HTTP_USER_AGENT']}

POST Data:
{print_r($_POST, true)}

GET Data:
{print_r($_GET, true)}
EOT;

    $headers = [
        'From: ' . ERROR_EMAIL_FROM,
        'Content-Type: text/plain; charset=UTF-8'
    ];

    mail(ERROR_EMAIL_TO, $subject, $body, implode("\r\n", $headers));
    touch(ERROR_SUBJECT_PREFIX . ' sent');
    update_rate_limit();
}

function should_send_email()
{
    $last = @file_get_contents(RATE_LIMIT_CACHE);
    if (!$last) return true;
    return (time() - (int)$last) > RATE_LIMIT_SECONDS;
}

function update_rate_limit()
{
    file_put_contents(RATE_LIMIT_CACHE, time());
}
