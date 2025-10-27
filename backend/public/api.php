<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configure database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'] ?? 'db',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['DB_DATABASE'] ?? 'aggregator',
    'username' => $_ENV['DB_USERNAME'] ?? 'aggregator_user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'aggregator_password',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Simple routing
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Route handling
if ($path === '/api/register' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validation
    if (empty($input['name']) || empty($input['email']) || empty($input['password'])) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => [
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]
        ]);
        exit;
    }

    if ($input['password'] !== $input['password_confirmation']) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => [
                'password_confirmation' => ['The password confirmation does not match.']
            ]
        ]);
        exit;
    }

    // Check if user exists
    $existingUser = Capsule::table('users')->where('email', $input['email'])->first();
    if ($existingUser) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => [
                'email' => ['The email has already been taken.']
            ]
        ]);
        exit;
    }

    // Create user
    $userId = Capsule::table('users')->insertGetId([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => password_hash($input['password'], PASSWORD_DEFAULT),
        'created_at' => now(),
        'updated_at' => now()
    ]);

    $user = Capsule::table('users')->where('id', $userId)->first();

    // Generate token (simple implementation)
    $token = bin2hex(random_bytes(32));

    // Store token
    Capsule::table('personal_access_tokens')->insert([
        'tokenable_type' => 'App\\Models\\User',
        'tokenable_id' => $userId,
        'name' => 'auth_token',
        'token' => hash('sha256', $token),
        'abilities' => '["*"]',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully',
        'data' => [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]
    ]);

} elseif ($path === '/api/login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]
        ]);
        exit;
    }

    $user = Capsule::table('users')->where('email', $input['email'])->first();

    if (!$user || !password_verify($input['password'], $user->password)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        exit;
    }

    // Generate token
    $token = bin2hex(random_bytes(32));

    // Store token
    Capsule::table('personal_access_tokens')->insert([
        'tokenable_type' => 'App\\Models\\User',
        'tokenable_id' => $user->id,
        'name' => 'auth_token',
        'token' => hash('sha256', $token),
        'abilities' => '["*"]',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]
    ]);

} elseif ($path === '/api/user' && $method === 'GET') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit;
    }

    $token = $matches[1];
    $tokenHash = hash('sha256', $token);

    $tokenRecord = Capsule::table('personal_access_tokens')
        ->where('token', $tokenHash)
        ->first();

    if (!$tokenRecord) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid token'
        ]);
        exit;
    }

    $user = Capsule::table('users')->where('id', $tokenRecord->tokenable_id)->first();

    echo json_encode([
        'success' => true,
        'data' => [
            'user' => $user
        ]
    ]);

} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Not found'
    ]);
}

function now() {
    return date('Y-m-d H:i:s');
}
?>
