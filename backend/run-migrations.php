<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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

try {
    // Create users table if it doesn't exist
    if (!Capsule::schema()->hasTable('users')) {
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        echo "✅ Users table created successfully\n";
    } else {
        echo "✅ Users table already exists\n";
    }

    // Create personal_access_tokens table if it doesn't exist
    if (!Capsule::schema()->hasTable('personal_access_tokens')) {
        Capsule::schema()->create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        echo "✅ Personal access tokens table created successfully\n";
    } else {
        echo "✅ Personal access tokens table already exists\n";
    }

    echo "🎉 Database setup completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
