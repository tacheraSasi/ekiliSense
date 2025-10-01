<?php
/**
 * Webhook Controller
 * Manages webhooks for third-party integrations - NEW SAAS FEATURE
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class WebhookController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Register a new webhook
     * POST /api/v1/webhooks
     */
    public function create() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['url', 'events']);
        
        $url = Response::sanitize($input['url']);
        $events = $input['events']; // Array of event types
        $secret = bin2hex(random_bytes(32));
        $webhookId = uniqid('webhook_', true);
        
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Response::error('Invalid webhook URL', 400);
        }
        
        $eventsJson = json_encode($events);
        
        $stmt = $this->conn->prepare("
            INSERT INTO webhooks (webhook_id, school_uid, url, events, secret, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param("sssss", $webhookId, $schoolUid, $url, $eventsJson, $secret);
        
        if ($stmt->execute()) {
            Response::success([
                'webhook_id' => $webhookId,
                'url' => $url,
                'secret' => $secret,
                'events' => $events
            ], 'Webhook created successfully', 201);
        } else {
            Response::error('Failed to create webhook', 500);
        }
    }
    
    /**
     * List webhooks
     * GET /api/v1/webhooks
     */
    public function list() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT webhook_id, url, events, is_active, created_at, last_triggered
            FROM webhooks 
            WHERE school_uid = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $webhooks = [];
        while ($row = $result->fetch_assoc()) {
            $webhooks[] = [
                'id' => $row['webhook_id'],
                'url' => $row['url'],
                'events' => json_decode($row['events'], true),
                'is_active' => (bool)$row['is_active'],
                'created_at' => $row['created_at'],
                'last_triggered' => $row['last_triggered']
            ];
        }
        
        Response::success($webhooks);
    }
    
    /**
     * Delete webhook
     * DELETE /api/v1/webhooks/{id}
     */
    public function delete($webhookId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            DELETE FROM webhooks 
            WHERE webhook_id = ? AND school_uid = ?
        ");
        $stmt->bind_param("ss", $webhookId, $schoolUid);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            Response::success([], 'Webhook deleted successfully');
        } else {
            Response::error('Webhook not found', 404);
        }
    }
    
    /**
     * Trigger webhook (internal method)
     */
    public static function trigger($conn, $schoolUid, $event, $data) {
        // Get all active webhooks for this school and event
        $stmt = $conn->prepare("
            SELECT webhook_id, url, secret, events
            FROM webhooks 
            WHERE school_uid = ? AND is_active = 1
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($webhook = $result->fetch_assoc()) {
            $events = json_decode($webhook['events'], true);
            
            if (in_array($event, $events) || in_array('*', $events)) {
                self::sendWebhook($conn, $webhook, $event, $data);
            }
        }
    }
    
    /**
     * Send webhook request
     */
    private static function sendWebhook($conn, $webhook, $event, $data) {
        $payload = [
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        $signature = hash_hmac('sha256', json_encode($payload), $webhook['secret']);
        
        $ch = curl_init($webhook['url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Webhook-Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Update last triggered time
        $updateStmt = $conn->prepare("
            UPDATE webhooks 
            SET last_triggered = NOW() 
            WHERE webhook_id = ?
        ");
        $updateStmt->bind_param("s", $webhook['webhook_id']);
        $updateStmt->execute();
        
        // Log webhook delivery
        $status = ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'failed';
        $logStmt = $conn->prepare("
            INSERT INTO webhook_logs (webhook_id, event, status, http_code, response)
            VALUES (?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("sssis", $webhook['webhook_id'], $event, $status, $httpCode, $response);
        $logStmt->execute();
    }
}
