<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'activities',
        'actors',
        'admin_users',
        'annuaires',
        'application_blocks',
        'application_events',
        'application_flows',
        'application_modules',
        'applications',
        'application_services',
        'backups',
        'bays',
        'buildings',
        'certificates',
        'clusters',
        'containers',
        'databases',
        'data_processing',
        'dhcp_servers',
        'dnsservers',
        'documents',
        'domains',
        'entities',
        'external_connected_entities',
        'forest_ads',
        'gateways',
        'information',
        'lans',
        'logical_flows',
        'logical_servers',
        'macro_processuses',
        'mans',
        'networks',
        'network_switches',
        'operations',
        'peripherals',
        'permissions',
        'phones',
        'physical_links',
        'physical_routers',
        'physical_security_devices',
        'physical_servers',
        'physical_switches',
        'processes',
        'relations',
        'routers',
        'security_controls',
        'security_devices',
        'sites',
        'storage_devices',
        'subnetworks',
        'tasks',
        'vlans',
        'wans',
        'wifi_terminals',
        'workstations',
        'zone_admins',
        'zones',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('ext_refs', 255)->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('ext_refs');
            });
        }
    }
};
