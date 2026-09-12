<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconcile schema drift: the live database gained these
     * service_requests columns outside the migration history (manual
     * ALTERs). Fresh installs (CI, staging, new environments) were missing
     * them, breaking request creation and every control-number lookup.
     *
     * Definitions mirror the live production schema exactly:
     * - control_number: nullable, UNIQUE (SR-YYYYMMDDHHMMSS-N format)
     * - requester_name/office/phone/property_no: nullable varchar(255)
     * - services: nullable longtext (JSON array of service keys)
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'control_number')) {
                $table->string('control_number')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('service_requests', 'requester_name')) {
                $table->string('requester_name')->nullable()->after('control_number');
            }
            if (! Schema::hasColumn('service_requests', 'office')) {
                $table->string('office')->nullable()->after('requester_office');
            }
            if (! Schema::hasColumn('service_requests', 'phone')) {
                $table->string('phone')->nullable()->after('office');
            }
            if (! Schema::hasColumn('service_requests', 'services')) {
                $table->longText('services')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('service_requests', 'property_no')) {
                $table->string('property_no')->nullable()->after('services');
            }
        });

        // requestor_name (legacy column) is nullable on live; fresh installs
        // inherited NOT NULL from the original create migration, which breaks
        // inserts that only populate the newer requester_name column.
        if (Schema::hasColumn('service_requests', 'requestor_name')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->string('requestor_name')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            foreach (['control_number', 'requester_name', 'office', 'phone', 'services', 'property_no'] as $column) {
                if (Schema::hasColumn('service_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
