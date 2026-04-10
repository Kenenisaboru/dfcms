<?php
// lib/EngagementService.php
require_once __DIR__ . '/../config/engagement_config.php';

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
        $allBadges = EngagementConfig::$badges;
        $earned = array();

        if (!isset($this->pdo) || !$this->pdo) {
            foreach ($allBadges as $key => &$badge) {
                $badge['earned'] = false;
                $badge['awarded_at'] = null;
            }
            return $allBadges;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT badge_type, awarded_at FROM user_badges WHERE user_id = ?");
            $stmt->execute(array($userId));
            $earned = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            // If engagement tables are not imported yet, return badge definitions as locked.
            $earned = array();
        }

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
        try {
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
        } catch (Throwable $e) {
            // Return empty if table doesn't exist yet
            return array();
        }
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
