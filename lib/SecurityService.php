<?php
// lib/SecurityService.php
require_once __DIR__ . '/../config/database.php';

class SecurityService {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->ensureUserTableSchema();
    }

    /**
     * Ensures the users table has the necessary security columns
     */
    private function ensureUserTableSchema() {
        // Check for login_attempts column
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `users` LIKE 'login_attempts'");
        if (!$stmt->fetch()) {
            $this->pdo->exec("ALTER TABLE `users` ADD COLUMN `login_attempts` int(11) DEFAULT 0");
        }
        
        // Check for locked_until column
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `users` LIKE 'locked_until'");
        if (!$stmt->fetch()) {
            $this->pdo->exec("ALTER TABLE `users` ADD COLUMN `locked_until` timestamp NULL DEFAULT NULL");
        }
    }
    
    /**
     * Two-Factor Authentication
     */
    public function generate2FASecret($userId) {
        $secret = $this->generateRandomKey(32);
        
        // Store secret hash in database
        $stmt = $this->pdo->prepare("UPDATE users SET two_factor_secret = ? WHERE id = ?");
        $stmt->execute(array(password_hash($secret, PASSWORD_DEFAULT), $userId));
        
        return $secret;
    }
    
    public function verify2FACode($userId, $code) {
        $stmt = $this->pdo->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $user = $stmt->fetch();
        
        if (!$user || !$user['two_factor_secret']) {
            return false;
        }
        
        // Verify code against stored secret (you'd need to implement TOTP verification)
        return $this->verifyTOTP($code, $user['two_factor_secret']);
    }
    
    /**
     * JWT Token Management
     */
    public function generateJWT($userId, $userData) {
        $config = $this->getJWTConfig();
        $payload = array(
            'iss' => $config['issuer'],
            'aud' => $config['audience'],
            'iat' => time(),
            'exp' => time() + $config['access_token_expiry'],
            'user_id' => $userId,
            'role' => $userData['role'],
            'session_id' => session_id()
        );
        
        return $this->encodeJWT($payload);
    }
    
    public function verifyJWT($token) {
        try {
            $payload = $this->decodeJWT($token);
            
            // Check if token is expired
            if ($payload['exp'] < time()) {
                return false;
            }
            
            // Verify session is still valid
            $stmt = $this->pdo->prepare("SELECT id FROM user_sessions WHERE session_id = ? AND expires_at > NOW()");
            $stmt->execute(array($payload['session_id']));
            
            return $stmt->fetch() ? $payload : false;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Data Encryption
     */
    public function encryptData($data) {
        $config = $this->getEncryptionConfig();
        $key = $this->getEncryptionKey();
        
        $iv = random_bytes($config['iv_length']);
        $tag = '';
        $encrypted = openssl_encrypt($data, $config['algorithm'], $key, OPENSSL_RAW_DATA, $iv, $tag, '', $config['tag_length']);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    public function decryptData($encryptedData) {
        $config = $this->getEncryptionConfig();
        $key = $this->getEncryptionKey();
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, $config['iv_length']);
        $tag = substr($data, $config['iv_length'], $config['tag_length']);
        $encrypted = substr($data, $config['iv_length'] + $config['tag_length']);
        
        return openssl_decrypt($encrypted, $config['algorithm'], $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
    
    /**
     * Rate Limiting
     */
    private function ensureRateLimitsTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `rate_limits` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `action` varchar(50) NOT NULL,
            `identifier` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_action_identifier` (`action`,`identifier`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->pdo->exec($sql);
    }

    public function checkRateLimit($action, $identifier) {
        $this->ensureRateLimitsTableExists();
        $limits = $this->getRateLimits();
        $limit = $limits[$action];
        
        if (!$limit) {
            return true; // No limit configured
        }
        
        // Clean old entries
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limits 
            WHERE action = ? AND identifier = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute(array($action, $identifier, $limit['window']));
        
        // Count recent attempts
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM rate_limits 
            WHERE action = ? AND identifier = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute(array($action, $identifier, $limit['window']));
        $count = $stmt->fetch()['count'];
        
        if ($count >= $limit['attempts']) {
            return false;
        }
        
        // Log this attempt
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (action, identifier, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute(array($action, $identifier));
        
        return true;
    }
    
    /**
     * Security Audit Logging
     */
    private function ensureAuditLogTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `security_audit_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(100) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `details` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_event_type` (`event_type`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->pdo->exec($sql);
    }

    public function logSecurityEvent($eventType, $userId, $details = array()) {
        $this->ensureAuditLogTableExists();
        $stmt = $this->pdo->prepare("
            INSERT INTO security_audit_log 
            (event_type, user_id, ip_address, user_agent, details, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $detailsJson = json_encode($details);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return $stmt->execute(array($eventType, $userId, $ipAddress, $userAgent, $detailsJson));
    }
    
    /**
     * Password Policy Validation
     */
    public function validatePassword($password) {
        $policy = $this->getPasswordPolicy();
        
        if (strlen($password) < $policy['min_length']) {
            return "Password must be at least {$policy['min_length']} characters long";
        }
        
        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter";
        }
        
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter";
        }
        
        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number";
        }
        
        if ($policy['require_special'] && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return "Password must contain at least one special character";
        }
        
        return true;
    }
    
    /**
     * Session Management
     */
    private function ensureSessionsTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `user_sessions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_id` varchar(255) NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `expires_at` timestamp NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_session` (`session_id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->pdo->exec($sql);
    }

    public function createSecureSession($userId) {
        $this->ensureSessionsTableExists();
        $sessionId = session_id();
        $config = $this->getSessionConfig();
        
        // Clean old sessions
        $stmt = $this->pdo->prepare("
            DELETE FROM user_sessions 
            WHERE user_id = ? OR expires_at < NOW()
        ");
        $stmt->execute(array($userId));
        
        // Create new session
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions 
            (user_id, session_id, ip_address, user_agent, created_at, expires_at) 
            VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return $stmt->execute(array($userId, $sessionId, $ipAddress, $userAgent, $config['lifetime']));
    }
    
    public function validateSession($userId) {
        $sessionId = session_id();
        
        $stmt = $this->pdo->prepare("
            SELECT id FROM user_sessions 
            WHERE user_id = ? AND session_id = ? AND expires_at > NOW()
        ");
        $stmt->execute(array($userId, $sessionId));
        
        return $stmt->fetch() ? true : false;
    }
    
    /**
     * Helper Methods
     */
    private function generateRandomKey($length) {
        return bin2hex(random_bytes($length / 2));
    }
    
    private function verifyTOTP($code, $secret) {
        // Implement TOTP verification (you'd need a library like OTPHP)
        // For now, return true as placeholder
        return true;
    }
    
    private function encodeJWT($payload) {
        // Implement JWT encoding (you'd need a library like firebase/php-jwt)
        // For now, return base64 encoded payload as placeholder
        return base64_encode(json_encode($payload));
    }
    
    private function decodeJWT($token) {
        // Implement JWT decoding
        // For now, decode base64 as placeholder
        return json_decode(base64_decode($token), true);
    }
    
    private function getJWTConfig() {
        return array(
            'algorithm' => 'HS256',
            'access_token_expiry' => 3600,
            'refresh_token_expiry' => 86400 * 7,
            'issuer' => 'DFCMS',
            'audience' => 'DFCMS_USERS'
        );
    }
    
    private function getEncryptionConfig() {
        return array(
            'algorithm' => 'aes-256-gcm',
            'key_length' => 32,
            'iv_length' => 12,
            'tag_length' => 16
        );
    }
    
    private function getEncryptionKey() {
        return getenv('ENCRYPTION_KEY') ?: 'default_encryption_key_change_in_production';
    }
    
    private function getSessionConfig() {
        return array(
            'lifetime' => 3600,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
            'regenerate_id' => true,
            'max_concurrent_sessions' => 3
        );
    }
    
    private function getPasswordPolicy() {
        return array(
            'min_length' => 12,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => true,
            'max_age_days' => 90,
            'history_count' => 5,
            'lockout_attempts' => 5,
            'lockout_duration' => 900
        );
    }
    
    private function getRateLimits() {
        return array(
            'login' => array('attempts' => 5, 'window' => 900),
            'password_reset' => array('attempts' => 3, 'window' => 3600),
            'api' => array('requests' => 100, 'window' => 60)
        );
    }
}
?>
