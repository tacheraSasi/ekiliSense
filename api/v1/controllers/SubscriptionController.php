<?php

/**
 * Subscription Controller
 * Manages SaaS subscription plans and billing - NEW SAAS FEATURE
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SubscriptionController
{

    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get current subscription
     * GET /api/v1/subscription/current
     */
    public function getCurrent()
    {
        $schoolUid = AuthMiddleware::getSchoolUid();

        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                p.display_name as plan_name,
                p.price,
                p.features,
                p.max_students,
                p.max_teachers
            FROM school_subscriptions s
            LEFT JOIN subscription_plans p ON s.plan_id = p.id
            WHERE s.school_uid = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::success([
                'status' => 'no_subscription',
                'plan' => 'free',
                'message' => 'No active subscription found'
            ]);
        }

        $subscription = $result->fetch_assoc();

        // Calculate usage
        $usage = $this->getUsage($schoolUid);

        Response::success([
            'subscription_id' => $subscription['subscription_id'],
            'plan' => [
                'name' => $subscription['plan_name'],
                'price' => $subscription['price'],
                'features' => json_decode($subscription['features'], true),
                'max_students' => $subscription['max_students'],
                'max_teachers' => $subscription['max_teachers']
            ],
            'status' => $subscription['status'],
            'start_date' => $subscription['start_date'],
            'end_date' => $subscription['end_date'],
            'auto_renew' => (bool)$subscription['auto_renew'],
            'usage' => $usage
        ]);
    }

    /**
     * Get available subscription plans
     * GET /api/v1/subscription/plans
     */
    public function getPlans()
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM subscription_plans 
            WHERE is_active = 1 
            ORDER BY price ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = [
                'plan_id' => $row['id'],
                'name' => $row['display_name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'currency' => $row['currency'],
                'billing_period' => $row['billing_period'],
                'features' => json_decode($row['features'], true),
                'max_students' => $row['max_students'],
                'max_teachers' => $row['max_teachers'],
                'max_classes' => $row['max_classes']
            ];
        }

        Response::success($plans);
    }

    /**
     * Subscribe to a plan
     * POST /api/v1/subscription/subscribe
     */
    public function subscribe()
    {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);

        Response::validateRequired($input, ['plan_id']);

        $planId = Response::sanitize($input['plan_id']);

        // Get plan details
        $planStmt = $this->conn->prepare("SELECT * FROM subscription_plans WHERE id = ?");
        $planStmt->bind_param("i", $planId);
        $planStmt->execute();
        $plan = $planStmt->get_result()->fetch_assoc();

        if (!$plan) {
            Response::error('Plan not found', 404);
        }        // Deactivate current subscription
        $deactivateStmt = $this->conn->prepare("
            UPDATE subscriptions 
            SET status = 'cancelled' 
            WHERE school_uid = ? AND status = 'active'
        ");
        $deactivateStmt->bind_param("s", $schoolUid);
        $deactivateStmt->execute();

        // Create new subscription
        $subscriptionId = uniqid('sub_', true);
        $startDate = date('Y-m-d');

        // Calculate end date based on billing cycle
        switch ($plan['billing_cycle']) {
            case 'monthly':
                $endDate = date('Y-m-d', strtotime('+1 month'));
                break;
            case 'yearly':
                $endDate = date('Y-m-d', strtotime('+1 year'));
                break;
            default:
                $endDate = date('Y-m-d', strtotime('+1 month'));
        }

        $stmt = $this->conn->prepare("
            INSERT INTO school_subscriptions 
            (school_uid, plan_id, status, start_date, end_date)
            VALUES (?, ?, 'trial', ?, ?)
        ");
        $stmt->bind_param("siss", $schoolUid, $planId, $startDate, $endDate);

        if ($stmt->execute()) {
            // In a real system, integrate with payment gateway here
            Response::success([
                'subscription_id' => $this->conn->insert_id,
                'plan' => $plan['display_name'],
                'amount' => $plan['price'],
                'currency' => $plan['currency'],
                'status' => 'trial',
                'payment_url' => '/payment/process/' . $this->conn->insert_id
            ], 'Subscription created. Please complete payment.', 201);
        } else {
            Response::error('Failed to create subscription', 500);
        }
    }

    /**
     * Cancel subscription
     * POST /api/v1/subscription/cancel
     */
    public function cancel()
    {
        $schoolUid = AuthMiddleware::getSchoolUid();

        $stmt = $this->conn->prepare("
            UPDATE subscriptions 
            SET status = 'cancelled', auto_renew = 0 
            WHERE school_uid = ? AND status = 'active'
        ");
        $stmt->bind_param("s", $schoolUid);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            Response::success([], 'Subscription cancelled successfully');
        } else {
            Response::error('No active subscription found', 404);
        }
    }

    /**
     * Get billing history
     * GET /api/v1/subscription/billing-history
     */
    public function getBillingHistory()
    {
        $schoolUid = AuthMiddleware::getSchoolUid();

        $stmt = $this->conn->prepare("
            SELECT 
                invoice_id,
                amount,
                currency,
                status,
                payment_date,
                payment_method,
                created_at
            FROM invoices
            WHERE school_uid = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        Response::success($history);
    }

    /**
     * Get usage statistics
     */
    private function getUsage($schoolUid)
    {
        // Get student count
        $studentsStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM students WHERE school_uid = ?");
        $studentsStmt->bind_param("s", $schoolUid);
        $studentsStmt->execute();
        $studentCount = $studentsStmt->get_result()->fetch_assoc()['count'];

        // Get teacher count
        $teachersStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM teachers WHERE School_unique_id = ?");
        $teachersStmt->bind_param("s", $schoolUid);
        $teachersStmt->execute();
        $teacherCount = $teachersStmt->get_result()->fetch_assoc()['count'];

        // Calculate storage (simplified - would need actual file storage calculation)
        $storageUsed = 0; // GB

        return [
            'students' => (int)$studentCount,
            'teachers' => (int)$teacherCount,
            'storage_gb' => $storageUsed
        ];
    }
}
