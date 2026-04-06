<?php
// config/engagement_config.php
class EngagementConfig {
    public static $badges = array(
        'first_complaint' => array(
            'name' => 'First Voice',
            'description' => 'Submitted your first complaint',
            'icon' => 'fa-bullhorn',
            'color' => '#10b981'
        ),
        'resolved_5' => array(
            'name' => 'Problem Solver',
            'description' => '5 complaints resolved',
            'icon' => 'fa-check-circle',
            'color' => '#3b82f6'
        ),
        'helpful_feedback' => array(
            'name' => 'Community Pillar',
            'description' => 'Provided helpful feedback on resolution',
            'icon' => 'fa-hands-helping',
            'color' => '#8b5cf6'
        ),
        'quick_responder' => array(
            'name' => 'Fast Tracker',
            'description' => 'Responded to handler within 1 hour',
            'icon' => 'fa-bolt',
            'color' => '#f59e0b'
        )
    );

    public static $knowledge_base_categories = array(
        'general' => 'General Information',
        'academic' => 'Academic Issues',
        'technical' => 'Technical Support',
        'facilities' => 'Facility Management'
    );
}
?>
