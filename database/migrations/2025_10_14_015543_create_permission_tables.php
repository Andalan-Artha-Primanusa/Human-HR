<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        $columnNames = config('permission.column_names', [
            'team_foreign_key' => 'team_id',
        ]);

        $teams = (bool) config('permission.teams', false);

        // permissions
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name','guard_name']);
        });

        // roles
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');

            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'],'name','guard_name']);
            } else {
                $table->unique(['name','guard_name']);
            }
        });

        // model_has_permissions (UUID morphs => model_type + model_id CHAR(36) + index)
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger('permission_id');
            $table->uuidMorphs('model'); // <-- JANGAN tambah model_type manual

            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
            }

            $table->foreign('permission_id')
                ->references('id')->on($tableNames['permissions'])
                ->onDelete('cascade');

            $primary = ['permission_id','model_id','model_type'];
            if ($teams || config('permission.testing')) {
                $primary[] = $columnNames['team_foreign_key'];
            }
            $table->primary($primary, 'model_has_permissions_permission_model_type_primary');
        });

        // model_has_roles (UUID morphs)
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger('role_id');
            $table->uuidMorphs('model'); // <-- JANGAN tambah model_type manual

            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
            }

            $table->foreign('role_id')
                ->references('id')->on($tableNames['roles'])
                ->onDelete('cascade');

            $primary = ['role_id','model_id','model_type'];
            if ($teams || config('permission.testing')) {
                $primary[] = $columnNames['team_foreign_key'];
            }
            $table->primary($primary, 'model_has_roles_role_model_type_primary');
        });

        // role_has_permissions
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['permission_id','role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // clear cache (optional)
        try {
            app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
                ->forget(config('permission.cache.key'));
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
