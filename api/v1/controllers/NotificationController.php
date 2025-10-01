<?php
/**
 * Notification Controller
 * Manages user notifications
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class NotificationController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * List notifications
     * GET /api/v1/notifications
     */
    public function list() {
        $userId = AuthMiddleware::getUserId();
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND school_uid = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->bind_param("ss", $userId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        Response::success($notifications);
    }
    
    /**
     * Mark notification as read
     * POST /api/v1/notifications/mark-read
     */
    public function markRead() {
        $userId = AuthMiddleware::getUserId();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['notification_id']);
        
        $notificationId = Response::sanitize($input['notification_id']);
        
        $stmt = $this->conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE notification_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ss", $notificationId, $userId);
        
        if ($stmt->execute()) {
            Response::success([], 'Notification marked as read');
        } else {
            Response::error('Failed to update notification', 500);
        }
    }
}
