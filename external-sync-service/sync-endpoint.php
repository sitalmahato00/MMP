<?php
/**
 * CTEVT External Sync Service Endpoint
 * 
 * This endpoint runs on an external server (VPS, local machine, or GitHub Actions)
 * that CAN access the CTEVT API on port 5580.
 * 
 * It fetches notices from CTEVT and updates the production database.
 * 
 * DEPLOYMENT:
 * 1. Copy this file to your external server
 * 2. Set environment variables:
 *    - CTEVT_SYNC_API_TOKEN: Secret token for authentication
 *    - PRODUCTION_DB_HOST: Production database host
 *    - PRODUCTION_DB_USER: Production database user
 *    - PRODUCTION_DB_PASS: Production database password
 *    - PRODUCTION_DB_NAME: Production database name
 * 3. Make it accessible via HTTPS
 * 4. Configure the URL in cPanel .env: CTEVT_SYNC_EXTERNAL_URL
 */

// ═══════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════

define('API_TOKEN', getenv('CTEVT_SYNC_API_TOKEN') ?: 'your-secret-token-here');
define('CTEVT_API_URL', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices');
define('CTEVT_TIMEOUT', 30);

// Database configuration
define('DB_HOST', getenv('PRODUCTION_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('PRODUCTION_DB_USER') ?: 'root');
define('DB_PASS', getenv('PRODUCTION_DB_PASS') ?: '');
define('DB_NAME', getenv('PRODUCTION_DB_NAME') ?: 'college_portal');

// ═══════════════════════════════════════════════════════════
// RESPONSE HELPERS
// ═══════════════════════════════════════════════════════════

function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error_response($message, $statusCode = 400) {
    json_response([
        'success' => false,
        'message' => $message,
        'error' => $message,
    ], $statusCode);
}

// ═══════════════════════════════════════════════════════════
// AUTHENTICATION
// ═══════════════════════════════════════════════════════════

function authenticate() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader)) {
        error_response('Missing Authorization header', 401);
    }
    
    if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
        error_response('Invalid Authorization header format', 401);
    }
    
    $token = $matches[1];
    
    if ($token !== API_TOKEN) {
        error_response('Invalid API token', 403);
    }
}

// ═══════════════════════════════════════════════════════════
// DATABASE FUNCTIONS
// ═══════════════════════════════════════════════════════════

function get_db_connection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Exception $e) {
        error_response('Database connection error: ' . $e->getMessage(), 500);
    }
}

function upsert_notice($conn, $type, $data) {
    $external_id = $conn->real_escape_string($data['external_id']);
    $title = $conn->real_escape_string($data['title']);
    $url = $conn->real_escape_string($data['url'] ?? '');
    $updated_date = $conn->real_escape_string($data['updated_date']);
    $publisher = $conn->real_escape_string($data['publisher']);
    $files_count = (int) ($data['files_count'] ?? 0);
    $raw_data = $conn->real_escape_string(json_encode($data['raw_data'] ?? []));
    $fetched_at = date('Y-m-d H:i:s');
    
    $query = "
        INSERT INTO ctevt_notices (type, external_id, title, url, updated_date, publisher, files_count, raw_data, fetched_at, created_at, updated_at)
        VALUES ('$type', '$external_id', '$title', '$url', '$updated_date', '$publisher', $files_count, '$raw_data', '$fetched_at', '$fetched_at', '$fetched_at')
        ON DUPLICATE KEY UPDATE
            title = '$title',
            url = '$url',
            updated_date = '$updated_date',
            publisher = '$publisher',
            files_count = $files_count,
            raw_data = '$raw_data',
            fetched_at = '$fetched_at',
            updated_at = '$fetched_at'
    ";
    
    if (!$conn->query($query)) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    return $conn->affected_rows > 0;
}

// ═══════════════════════════════════════════════════════════
// CTEVT API FUNCTIONS
// ═══════════════════════════════════════════════════════════

function fetch_ctevt_notices($type) {
    $isResult = $type === 'result';
    
    $params = [
        'draw' => 1,
        'columns' => [
            ['data' => 'serial_no', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => 'updated_date', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => 'notice_title', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => 'notice_files', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => 'publisher', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
        ],
        'order' => [['column' => 0, 'dir' => 'asc']],
        'start' => 0,
        'length' => 20,
        'search' => ['value' => '', 'regex' => 'false'],
        'tab_id' => 'tab-0',
        'is_result' => $isResult ? '1' : '0',
    ];
    
    $url = CTEVT_API_URL . '?' . http_build_query($params);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => CTEVT_TIMEOUT,
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json',
            ],
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    
    try {
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to fetch from CTEVT API');
        }
        
        $data = json_decode($response, true);
        
        if (!is_array($data) || !isset($data['data'])) {
            throw new Exception('Invalid response from CTEVT API');
        }
        
        return $data['data'];
    } catch (Exception $e) {
        throw new Exception('CTEVT API error: ' . $e->getMessage());
    }
}

function map_notice_data($item, $type) {
    $titleHtml = trim($item['notice_title'] ?? '');
    
    if (empty($titleHtml)) {
        return null;
    }
    
    // Extract URL and text from HTML link
    $titleUrl = null;
    $titleText = strip_tags($titleHtml);
    
    if (preg_match('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $titleHtml, $matches)) {
        $titleUrl = html_entity_decode($matches[1]);
        $titleText = html_entity_decode(strip_tags($matches[2]));
    }
    
    $externalId = $item['notice_cd'] ?? md5($titleText . ($item['updated_date'] ?? ''));
    
    return [
        'type' => $type,
        'external_id' => (string) $externalId,
        'title' => $titleText ?: strip_tags($titleHtml),
        'url' => $titleUrl,
        'updated_date' => trim($item['updated_date'] ?? ''),
        'publisher' => trim($item['publisher'] ?? ''),
        'files_count' => substr_count($item['notice_files'] ?? '', '<a '),
        'raw_data' => $item,
    ];
}

// ═══════════════════════════════════════════════════════════
// MAIN SYNC LOGIC
// ═══════════════════════════════════════════════════════════

function sync_notices() {
    $startTime = microtime(true);
    $conn = get_db_connection();
    
    $stats = [
        'general' => ['added' => 0, 'updated' => 0],
        'result' => ['added' => 0, 'updated' => 0],
    ];
    
    try {
        foreach (['general', 'result'] as $type) {
            try {
                $items = fetch_ctevt_notices($type);
                
                foreach ($items as $item) {
                    $noticeData = map_notice_data($item, $type);
                    
                    if (!$noticeData) {
                        continue;
                    }
                    
                    $affectedRows = upsert_notice($conn, $type, $noticeData);
                    
                    if ($affectedRows > 0) {
                        // Check if it's an insert or update
                        $checkQuery = "SELECT id FROM ctevt_notices WHERE type = '$type' AND external_id = '{$noticeData['external_id']}'";
                        $result = $conn->query($checkQuery);
                        
                        if ($result && $result->num_rows > 0) {
                            $stats[$type]['updated']++;
                        } else {
                            $stats[$type]['added']++;
                        }
                    }
                }
            } catch (Exception $e) {
                // Log error but continue with other types
                error_log("Error syncing $type notices: " . $e->getMessage());
            }
        }
        
        $conn->close();
        
        $duration = microtime(true) - $startTime;
        
        return [
            'success' => true,
            'message' => 'Sync completed successfully',
            'notices_added' => $stats['general']['added'] + $stats['result']['added'],
            'notices_updated' => $stats['general']['updated'] + $stats['result']['updated'],
            'notices_total' => $stats['general']['added'] + $stats['general']['updated'] + $stats['result']['added'] + $stats['result']['updated'],
            'duration_seconds' => round($duration, 2),
            'breakdown' => $stats,
        ];
    } catch (Exception $e) {
        $conn->close();
        
        return [
            'success' => false,
            'message' => 'Sync failed',
            'error' => $e->getMessage(),
        ];
    }
}

// ═══════════════════════════════════════════════════════════
// REQUEST HANDLER
// ═══════════════════════════════════════════════════════════

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('Method not allowed', 405);
}

// Authenticate request
authenticate();

// Get action
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

if ($action !== 'sync_notices') {
    error_response('Invalid action', 400);
}

// Execute sync
$result = sync_notices();

json_response($result, $result['success'] ? 200 : 500);
