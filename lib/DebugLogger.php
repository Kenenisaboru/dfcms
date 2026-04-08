<?php
class DebugLogger {
    public static function log($runId, $hypothesisId, $location, $message, $data = array()) {
        $entry = array(
            'sessionId' => 'b45e3d',
            'runId' => $runId,
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => round(microtime(true) * 1000)
        );

        $path = dirname(__DIR__) . '/debug-b45e3d.log';
        @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }
}
?>
