<?php
/**
 * Subscription Management Class
 * Handles subscription plans, billing, and access control
 */
class Subscription {
    private $db;
    
    // Subscription plan constants
    const PLAN_FREE = 'free';
    const PLAN_BASIC = 'basic';
    const PLAN_PROFESSIONAL = 'professional';
    const PLAN_ENTERPRISE = 'enterprise';
    
    // Plan features and limits
    const PLAN_LIMITS = [
        self::PLAN_FREE => [
            'max_students' => 50,
            'max_teachers' => 5,
            'max_classes' => 5,
            'price' => 0,
            'features' => [
                'basic_dashboard',
                'student_management',
                'teacher_management',
                'class_management'
            ]
        ],
        self::PLAN_BASIC => [
            'max_students' => 200,
            'max_teachers' => 20,
            'max_classes' => 20,
            'price' => 150,
            'features' => [
                'basic_dashboard',
                'student_management',
                'teacher_management',
                'class_management',
                'homework_assignments',
                'basic_analytics',
                'email_support'
            ]
        ],
        self::PLAN_PROFESSIONAL => [
            'max_students' => 1000,
            'max_teachers' => 100,
            'max_classes' => 50,
            'price' => 400,
            'features' => [
                'all_basic_features',
                'parent_portal',
                'advanced_analytics',
                'real_time_notifications',
                'attendance_tracking',
                'exam_management',
                'priority_support'
            ]
        ],
        self::PLAN_ENTERPRISE => [
            'max_students' => -1, // unlimited
            'max_teachers' => -1,
            'max_classes' => -1,
            'price' => 800,
            'features' => [
                'all_professional_features',
                'multi_campus',
                'custom_integrations',
                'api_access',
                'dedicated_support',
                'custom_reports',
                'sla_guarantee'
            ]
        ]
    ];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get school's subscription status
     * @param string $schoolUid School unique ID
     * @return array|null Subscription details
     */
    public function getSubscription($schoolUid) {
        $query = "SELECT s.*, sp.name as plan_name, sp.price, sp.features 
                  FROM school_subscriptions s
                  LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
                  WHERE s.school_uid = ? AND s.status = 'active'
                  ORDER BY s.end_date DESC LIMIT 1";
        
        return $this->db->selectOne($query, [$schoolUid], 's');
    }
    
    /**
     * Check if school can perform action based on subscription limits
     * @param string $schoolUid School unique ID
     * @param string $resource Resource type (students, teachers, classes)
     * @return array ['allowed' => bool, 'current' => int, 'limit' => int, 'plan' => string]
     */
    public function checkLimit($schoolUid, $resource) {
        $subscription = $this->getSubscription($schoolUid);
        
        if (!$subscription) {
            // Default to free plan if no subscription
            $plan = self::PLAN_FREE;
            $limits = self::PLAN_LIMITS[$plan];
        } else {
            $plan = $subscription['plan_name'];
            $limits = self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS[self::PLAN_FREE];
        }
        
        $currentCount = $this->getCurrentCount($schoolUid, $resource);
        $limit = $limits['max_' . $resource] ?? 0;
        
        return [
            'allowed' => $limit === -1 || $currentCount < $limit,
            'current' => $currentCount,
            'limit' => $limit === -1 ? 'unlimited' : $limit,
            'plan' => $plan
        ];
    }
    
    /**
     * Check if school has specific feature access
     * @param string $schoolUid School unique ID
     * @param string $feature Feature name
     * @return bool True if feature is available
     */
    public function hasFeature($schoolUid, $feature) {
        $subscription = $this->getSubscription($schoolUid);
        
        if (!$subscription) {
            $plan = self::PLAN_FREE;
        } else {
            $plan = $subscription['plan_name'];
        }
        
        $features = self::PLAN_LIMITS[$plan]['features'] ?? [];
        
        return in_array($feature, $features) || 
               in_array('all_basic_features', $features) ||
               in_array('all_professional_features', $features);
    }
    
    /**
     * Check if subscription is active and not expired
     * @param string $schoolUid School unique ID
     * @return bool True if subscription is valid
     */
    public function isActive($schoolUid) {
        $subscription = $this->getSubscription($schoolUid);
        
        if (!$subscription) {
            return false;
        }
        
        $endDate = strtotime($subscription['end_date']);
        $now = time();
        
        return $subscription['status'] === 'active' && $endDate > $now;
    }
    
    /**
     * Get days remaining in subscription
     * @param string $schoolUid School unique ID
     * @return int Days remaining (negative if expired)
     */
    public function getDaysRemaining($schoolUid) {
        $subscription = $this->getSubscription($schoolUid);
        
        if (!$subscription) {
            return 0;
        }
        
        $endDate = strtotime($subscription['end_date']);
        $now = time();
        
        return floor(($endDate - $now) / 86400);
    }
    
    /**
     * Create or update subscription
     * @param string $schoolUid School unique ID
     * @param string $planName Plan name
     * @param int $duration Duration in months
     * @return bool Success status
     */
    public function createSubscription($schoolUid, $planName, $duration = 1) {
        $planId = $this->getPlanId($planName);
        
        if (!$planId) {
            return false;
        }
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$duration} months"));
        
        $query = "INSERT INTO school_subscriptions 
                  (school_uid, plan_id, status, start_date, end_date, created_at) 
                  VALUES (?, ?, 'active', ?, ?, NOW())";
        
        return $this->db->execute($query, [$schoolUid, $planId, $startDate, $endDate], 'siss');
    }
    
    /**
     * Get current count of resource for school
     * @param string $schoolUid School unique ID
     * @param string $resource Resource type
     * @return int Current count
     */
    private function getCurrentCount($schoolUid, $resource) {
        $table = '';
        $column = '';
        
        switch ($resource) {
            case 'students':
                $table = 'students';
                $column = 'school_uid';
                break;
            case 'teachers':
                $table = 'teachers';
                $column = 'School_unique_id';
                break;
            case 'classes':
                $table = 'classes';
                $column = 'school_unique_id';
                break;
            default:
                return 0;
        }
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $this->db->selectOne($query, [$schoolUid], 's');
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get plan ID by name
     * @param string $planName Plan name
     * @return int|null Plan ID
     */
    private function getPlanId($planName) {
        $query = "SELECT id FROM subscription_plans WHERE name = ?";
        $result = $this->db->selectOne($query, [$planName], 's');
        
        return $result ? (int)$result['id'] : null;
    }
    
    /**
     * Get all available plans
     * @return array Array of plans
     */
    public function getAllPlans() {
        return self::PLAN_LIMITS;
    }
}
