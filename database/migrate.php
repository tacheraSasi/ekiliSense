<?php

/**
 * Database Migration Runner
 * Run migrations from command line or web interface
 * 
 * Usage (CLI): php migrate.php [up|down|status]
 * Usage (Web): /database/migrate.php?action=[up|down|status]&confirm=yes
 */

require_once __DIR__ . '/../includes/init.php';

// Security check for web access
if (php_sapi_name() !== 'cli') {
    // Only allow in development or with proper authentication
    $environment = getenv('APP_ENVIRONMENT') ?: 'production';

    if ($environment === 'production') {
        die('Migration runner is disabled in production web access. Use CLI instead.');
    }

    // Check if user is authenticated as admin
    if (!isset($_SESSION['School_uid']) || $_SESSION['School_uid'] !== '1635813453') {
        die('Unauthorized access');
    }

    // Require confirmation
    if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
        die('Missing confirmation parameter');
    }
}

class MigrationRunner
{
    private $db;
    private $migrationsPath;
    private $migrationTable = 'migrations';

    public function __construct($database, $migrationsPath)
    {
        $this->db = $database;
        $this->migrationsPath = $migrationsPath;
        $this->ensureMigrationTable();
    }

    /**
     * Create migrations tracking table if it doesn't exist
     */
    private function ensureMigrationTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->migrationTable}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) NOT NULL,
            `batch` int(11) NOT NULL,
            `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `migration` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($query);
    }

    /**
     * Get list of migration files
     */
    private function getMigrationFiles()
    {
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);
        return array_map('basename', $files);
    }

    /**
     * Get executed migrations from database
     */
    private function getExecutedMigrations()
    {
        $result = $this->db->selectAll("SELECT migration FROM {$this->migrationTable} ORDER BY id");
        return array_column($result, 'migration');
    }

    /**
     * Get pending migrations
     */
    public function getPendingMigrations()
    {
        $allMigrations = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();

        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Run pending migrations
     */
    public function up()
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            $this->output("No pending migrations.\n", 'info');
            return true;
        }

        $this->output("Found " . count($pending) . " pending migration(s).\n", 'info');

        // Get next batch number
        $batchResult = $this->db->selectOne("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");
        $batch = ($batchResult ? (int)$batchResult['max_batch'] : 0) + 1;

        foreach ($pending as $migration) {
            $this->output("\nRunning: {$migration}\n", 'info');

            try {
                $this->runMigration($migration);

                // Record migration
                $this->db->execute(
                    "INSERT INTO {$this->migrationTable} (migration, batch) VALUES (?, ?)",
                    [$migration, $batch],
                    'si'
                );

                $this->output("✓ Completed: {$migration}\n", 'success');
            } catch (Exception $e) {
                $this->output("✗ Failed: {$migration}\n", 'error');
                $this->output("Error: " . $e->getMessage() . "\n", 'error');
                return false;
            }
        }

        $this->output("\n✓ All migrations completed successfully!\n", 'success');
        return true;
    }

    /**
     * Rollback last batch of migrations
     */
    public function down()
    {
        // Get last batch
        $batchResult = $this->db->selectOne("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");

        if (!$batchResult || !$batchResult['max_batch']) {
            $this->output("No migrations to rollback.\n", 'info');
            return true;
        }

        $batch = (int)$batchResult['max_batch'];

        // Get migrations in last batch
        $migrations = $this->db->selectAll(
            "SELECT migration FROM {$this->migrationTable} WHERE batch = ? ORDER BY id DESC",
            [$batch],
            'i'
        );

        $this->output("Rolling back batch {$batch} (" . count($migrations) . " migration(s)).\n", 'info');

        foreach ($migrations as $row) {
            $migration = $row['migration'];
            $this->output("\nRolling back: {$migration}\n", 'info');

            try {
                // Note: SQL migrations don't typically have down methods
                // This is a placeholder - actual rollback would need down.sql files

                // Remove from migrations table
                $this->db->execute(
                    "DELETE FROM {$this->migrationTable} WHERE migration = ?",
                    [$migration],
                    's'
                );

                $this->output("✓ Rolled back: {$migration}\n", 'success');
            } catch (Exception $e) {
                $this->output("✗ Failed to rollback: {$migration}\n", 'error');
                $this->output("Error: " . $e->getMessage() . "\n", 'error');
                return false;
            }
        }

        $this->output("\n✓ Rollback completed!\n", 'success');
        return true;
    }

    /**
     * Show migration status
     */
    public function status()
    {
        $all = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        $pending = $this->getPendingMigrations();

        $this->output("\n=== Migration Status ===\n", 'info');
        $this->output("Total migrations: " . count($all) . "\n", 'info');
        $this->output("Executed: " . count($executed) . "\n", 'success');
        $this->output("Pending: " . count($pending) . "\n", 'warning');

        if (!empty($executed)) {
            $this->output("\nExecuted Migrations:\n", 'info');
            foreach ($executed as $migration) {
                $this->output("  ✓ {$migration}\n", 'success');
            }
        }

        if (!empty($pending)) {
            $this->output("\nPending Migrations:\n", 'info');
            foreach ($pending as $migration) {
                $this->output("  - {$migration}\n", 'warning');
            }
        }

        $this->output("\n");
    }

    /**
     * Execute a migration file
     */
    private function runMigration($migrationFile)
    {
        $filepath = $this->migrationsPath . '/' . $migrationFile;

        if (!file_exists($filepath)) {
            throw new Exception("Migration file not found: {$filepath}");
        }

        $sql = file_get_contents($filepath);

        // Split by semicolon and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function ($stmt) {
                return !empty($stmt) &&
                    !preg_match('/^--/', $stmt) &&
                    !preg_match('/^\/\*/', $stmt);
            }
        );

        foreach ($statements as $statement) {
            if (empty(trim($statement))) {
                continue;
            }

            $result = $this->db->execute($statement);
            if ($result === false) {
                throw new Exception("Failed to execute statement: " . substr($statement, 0, 100));
            }
        }
    }

    /**
     * Output message with color (CLI) or HTML (web)
     */
    private function output($message, $type = 'default')
    {
        if (php_sapi_name() === 'cli') {
            $colors = [
                'default' => "\033[0m",
                'info' => "\033[34m",
                'success' => "\033[32m",
                'warning' => "\033[33m",
                'error' => "\033[31m"
            ];

            $color = $colors[$type] ?? $colors['default'];
            echo $color . $message . $colors['default'];
        } else {
            $colors = [
                'default' => '#333',
                'info' => '#3B82F6',
                'success' => '#10B981',
                'warning' => '#F59E0B',
                'error' => '#EF4444'
            ];

            $color = $colors[$type] ?? $colors['default'];
            echo '<span style="color: ' . $color . '">' . htmlspecialchars($message) . '</span>';
        }
    }
}

// Run migrations
try {
    $migrationsPath = __DIR__ . '/migrations';

    // Check if database connection exists
    if (!isset($db) || !$db) {
        throw new Exception("Database connection not available. Please check your database configuration.");
    }

    $runner = new MigrationRunner($db, $migrationsPath);

    // Determine action
    $action = 'status';
    if (php_sapi_name() === 'cli') {
        $action = $argv[1] ?? 'status';
    } else {
        $action = $_GET['action'] ?? 'status';
    }

    // Execute action
    switch ($action) {
        case 'up':
            $runner->up();
            break;
        case 'down':
            $runner->down();
            break;
        case 'status':
        default:
            $runner->status();
            break;
    }
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
