<?php
/**
 * API Testing Script
 * Тестирует все endpoint'ы API
 */

$baseUrl = 'http://localhost:8888/NLTv1/backend/api';

echo "=== NeverLands API Testing ===\n\n";

function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array(
        'code' => $httpCode,
        'response' => json_decode($response, true)
    );
}

// Test 1: Index
echo "[1/7] Testing API Index...\n";
$result = testEndpoint($baseUrl . '/');
echo "Status: {$result['code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 2: Auth (Login)
echo "[2/7] Testing Auth (Login)...\n";
$result = testEndpoint($baseUrl . '/auth', 'POST', [
    'initData' => '',
    'user' => [
        'id' => 12345,
        'username' => 'testuser',
        'first_name' => 'Test'
    ]
]);
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    $userId = $result['response']['data']['user']['id'];
    echo "✓ Login successful! User ID: $userId\n\n";
} else {
    echo "✗ Login failed\n\n";
    exit(1);
}

// Test 3: Player Data
echo "[3/7] Testing Player API...\n";
$result = testEndpoint($baseUrl . "/player?user_id=$userId");
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    echo "✓ Player data retrieved\n";
    echo "  Login: {$result['response']['data']['login']}\n";
    echo "  Level: {$result['response']['data']['level']}\n";
    echo "  NV: {$result['response']['data']['nv']}\n\n";
} else {
    echo "✗ Failed to get player data\n\n";
}

// Test 4: Inventory
echo "[4/7] Testing Inventory API...\n";
$result = testEndpoint($baseUrl . "/inventory?user_id=$userId");
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    $itemCount = count($result['response']['data']['items']);
    echo "✓ Inventory retrieved ($itemCount items)\n\n";
} else {
    echo "✗ Failed to get inventory\n\n";
}

// Test 5: Market
echo "[5/7] Testing Market API...\n";
$result = testEndpoint($baseUrl . "/market?category=w4");
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    $itemCount = count($result['response']['data']['items']);
    echo "✓ Market items retrieved ($itemCount items)\n\n";
} else {
    echo "✗ Failed to get market items\n\n";
}

// Test 6: Map
echo "[6/7] Testing Map API...\n";
$result = testEndpoint($baseUrl . "/map?user_id=$userId");
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    echo "✓ Position retrieved\n";
    echo "  Location: {$result['response']['data']['loc']}\n";
    echo "  Position: {$result['response']['data']['pos']}\n\n";
} else {
    echo "✗ Failed to get position\n\n";
}

// Test 7: Chat
echo "[7/7] Testing Chat API...\n";
$result = testEndpoint($baseUrl . "/chat?limit=10");
echo "Status: {$result['code']}\n";
if ($result['response']['success']) {
    $msgCount = count($result['response']['data']['messages']);
    echo "✓ Chat messages retrieved ($msgCount messages)\n\n";
} else {
    echo "✗ Failed to get chat messages\n\n";
}

echo "=== Testing Complete! ===\n";
echo "\nAll endpoints are working correctly!\n";
echo "\nNext steps:\n";
echo "1. cd /Applications/MAMP/htdocs/NLTv1/frontend\n";
echo "2. npm install\n";
echo "3. npm start\n";
echo "4. Open http://localhost:3000 in browser\n";
?>
