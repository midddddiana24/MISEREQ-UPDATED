<?php

namespace App\Support;

final class PreventiveMaintenanceOptions
{
    public const HARDWARE = [
        'complete_components' => 'Complete Components & Peripherals',
        'remove_dust' => 'Remove Dust Build-up',
        'check_power' => 'Check Power/Overheating Issues',
        'check_network_connectivity' => 'Check/Optimize Network Connectivity',
    ];

    public const SOFTWARE = [
        'update_drivers' => 'Up-to-date Drivers',
        'remove_unwanted_registry' => 'Remove Unwanted Registry Files',
        'defragment_disk' => 'Defragment Disk Drive',
        'complete_productivity_tools' => 'Complete Productivity Tools',
        'scan_for_viruses' => 'Scan for Viruses',
        'optimize_startup' => 'Optimize Start-up',
        'check_os_licenses' => 'Check OS and Other Licenses',
        'delete_trash_junk' => 'Delete Trash/Junk Files',
        'update_security_tools' => 'Up-to-date Security Tools',
    ];

    public const OTHER_ICT = [
        'software' => 'Software',
        'hardware' => 'Hardware',
        'network' => 'Network',
        'communications' => 'Communications',
    ];

    public const INFORMATION_SYSTEMS = [
        'connectivity' => 'Connectivity',
        'software_maintenance' => 'Software Maintenance',
        'database_backup' => 'Database Backup',
        'application_backup' => 'Application Backup',
    ];
}
