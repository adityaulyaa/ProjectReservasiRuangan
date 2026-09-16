<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class DatabaseCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat database otomatis jika belum ada (MySQL/MariaDB)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver !== 'mysql') {
            $this->info("Skip: db:create hanya untuk driver mysql (aktif: {$driver}).");

            return self::SUCCESS;
        }

        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $database = config("database.connections.{$connection}.database");

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $charset = config("database.connections.{$connection}.charset", 'utf8mb4');
            $collation = config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci');

            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}"
            );

            $this->info("Database `{$database}` siap (sudah ada atau berhasil dibuat).");
        } catch (\PDOException $e) {
            $this->error("Gagal membuat database: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
