<?php
// lib/EngagementService.php

class EngagementService {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Badge System
     */
    public function getUserBadges($userId) {
        $stmt = $this->pdo->prepare("SELECT badge_type, awarded_at FROM user_badges WHERE user_id = ?");
        $stmt->execute(array($userId));
        $earned = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $allBadges = EngagementConfig::$badges;
        foreach ($allBadges as $key => &$badge) {
            $badge['earned'] = isset($earned[$key]);
            $badge['awarded_at'] = $badge['earned'] ? $earned[$key] : null;
        }
        return $allBadges;
    }

    public function awardBadge($userId, $badgeType) {
        if (!isset(EngagementConfig::$badges[$badgeType])) return false;

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_type, awarded_at) VALUES (?, ?, NOW())");
        return $stmt->execute(array($userId, $badgeType));
    }

    /**
     * Knowledge Base
     */
    public function getKBArticles($category = null, $search = null) {
        $sql = "SELECT * FROM knowledge_base WHERE is_published = 1";
        $params = array();

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $sql .= " AND (title LIKE ? OR content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY views DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Interactive Help Tracking
     */
    public function logHelpInteraction($userId, $helpKey) {
        $stmt = $this->pdo->prepare("INSERT INTO help_interactions (user_id, help_key, interacted_at) VALUES (?, ?, NOW())");
        return $stmt->execute(array($userId, $helpKey));
    }
}
?>
