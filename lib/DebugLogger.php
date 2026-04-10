<?php
class DebugLogger {
    public static function log($runId, $hypothesisId, $location, $message, $data = array()) {
        $debugEnabled = strtolower((string) getenv('APP_DEBUG')) === 'true';
        if (!$debugEnabled) {
            return;
        }

        $entry = array(
            'sessionId' => 'b45e3d',
            'runId' => $runId,
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => round(microtime(true) * 1000)
        );

        $logDir = getenv('APP_LOG_DIR');
        if (!$logDir) {
            $logDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dfcms-logs';
        }
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }
        $path = rtrim($logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'debug.log';
        @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }
}
?>
