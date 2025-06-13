<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Индексы для таблицы orders
        Schema::table('orders', function (Blueprint $table) {
            $this->addIndexIfNotExists('orders', 'status');
            $this->addIndexIfNotExists('orders', 'manager_id');
            $this->addIndexIfNotExists('orders', 'surveyor_id');
            $this->addIndexIfNotExists('orders', 'constructor_id');
            $this->addIndexIfNotExists('orders', 'installer_id');
            $this->addIndexIfNotExists('orders', 'order_number');
            $this->addIndexIfNotExists('orders', 'meeting_at');
        });

        // Составные индексы для orders
        $this->addCompositeIndexIfNotExists('orders', ['status', 'manager_id'], 'orders_status_manager_id_index');
        $this->addCompositeIndexIfNotExists('orders', ['status', 'created_at'], 'orders_status_created_at_index');

        // Индексы для таблицы measurements
        Schema::table('measurements', function (Blueprint $table) {
            $this->addIndexIfNotExists('measurements', 'order_id');
            $this->addIndexIfNotExists('measurements', 'surveyor_id');
            $this->addIndexIfNotExists('measurements', 'status');
            $this->addIndexIfNotExists('measurements', 'measured_at');
        });

        $this->addCompositeIndexIfNotExists('measurements', ['order_id', 'status'], 'measurements_order_id_status_index');

        // Индексы для таблицы productions
        Schema::table('productions', function (Blueprint $table) {
            $this->addIndexIfNotExists('productions', 'order_id');
            $this->addIndexIfNotExists('productions', 'completed_at');
        });

        $this->addCompositeIndexIfNotExists('productions', ['order_id', 'completed_at'], 'productions_order_id_completed_at_index');

        // Индексы для таблицы installations
        Schema::table('installations', function (Blueprint $table) {
            $this->addIndexIfNotExists('installations', 'order_id');
            $this->addIndexIfNotExists('installations', 'installer_id');
            $this->addIndexIfNotExists('installations', 'documentation_id');
            $this->addIndexIfNotExists('installations', 'installed_at');
        });

        $this->addCompositeIndexIfNotExists('installations', ['order_id', 'installed_at'], 'installations_order_id_installed_at_index');

        // Индексы для таблицы contracts
        Schema::table('contracts', function (Blueprint $table) {
            $this->addIndexIfNotExists('contracts', 'order_id');
            $this->addIndexIfNotExists('contracts', 'constructor_id');
            $this->addIndexIfNotExists('contracts', 'signed_at');
            $this->addIndexIfNotExists('contracts', 'contract_number');
        });

        // Индексы для таблицы documentations
        Schema::table('documentations', function (Blueprint $table) {
            $this->addIndexIfNotExists('documentations', 'order_id');
            $this->addIndexIfNotExists('documentations', 'constructor_id');
            $this->addIndexIfNotExists('documentations', 'completed_at');
        });

        $this->addCompositeIndexIfNotExists('documentations', ['order_id', 'completed_at'], 'documentations_order_id_completed_at_index');

        // Индексы для таблицы attachments (проверяем существующие)
        Schema::table('attachments', function (Blueprint $table) {
            $this->addIndexIfNotExists('attachments', 'mime_type');
            $this->addIndexIfNotExists('attachments', 'created_at');
        });

        // Проверяем составной индекс для attachments отдельно
        if (!$this->indexExists('attachments', 'attachments_attachable_type_attachable_id_index')) {
            $this->addCompositeIndexIfNotExists('attachments', ['attachable_type', 'attachable_id'], 'attachments_attachable_type_attachable_id_index');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexesToDrop = [
            'orders' => ['status', 'manager_id', 'surveyor_id', 'constructor_id', 'installer_id', 'order_number', 'meeting_at'],
            'measurements' => ['order_id', 'surveyor_id', 'status', 'measured_at'],
            'productions' => ['order_id', 'completed_at'],
            'installations' => ['order_id', 'installer_id', 'documentation_id', 'installed_at'],
            'contracts' => ['order_id', 'constructor_id', 'signed_at', 'contract_number'],
            'documentations' => ['order_id', 'constructor_id', 'completed_at'],
            'attachments' => ['mime_type', 'created_at']
        ];

        foreach ($indexesToDrop as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexes) {
                    foreach ($indexes as $index) {
                        $table->dropIndex([$index]);
                    }
                });
            }
        }

        // Удаляем составные индексы
        $compositeIndexes = [
            'orders_status_manager_id_index',
            'orders_status_created_at_index',
            'measurements_order_id_status_index',
            'productions_order_id_completed_at_index',
            'installations_order_id_installed_at_index',
            'documentations_order_id_completed_at_index',
            'attachments_attachable_type_attachable_id_index'
        ];

        foreach ($compositeIndexes as $indexName) {
            DB::statement("DROP INDEX IF EXISTS {$indexName}");
        }
    }

    private function addIndexIfNotExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        if (!$this->indexExists($table, $indexName)) {
            DB::statement("CREATE INDEX {$indexName} ON {$table} ({$column})");
        }
    }

    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            $columnList = implode(', ', $columns);
            DB::statement("CREATE INDEX {$indexName} ON {$table} ({$columnList})");
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::connection();
        
        if ($connection->getDriverName() === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return !empty($result);
        }
        
        // Для MySQL
        if ($connection->getDriverName() === 'mysql') {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return !empty($result);
        }
        
        return false;
    }
};
