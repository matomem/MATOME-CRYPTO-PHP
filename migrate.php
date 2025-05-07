<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Commands\MigrateCommand;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$command = new MigrateCommand();

// Parse command line arguments
$action = $argv[1] ?? 'migrate';

switch ($action) {
    case 'migrate':
        $command->migrate();
        break;
    case 'rollback':
        $command->rollback();
        break;
    case 'refresh':
        $command->refresh();
        break;
    default:
        echo "Unknown command: $action\n";
        echo "Available commands: migrate, rollback, refresh\n";
        exit(1);
} 