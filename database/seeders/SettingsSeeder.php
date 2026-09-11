<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // System Settings
        Setting::set('system_email', 'system@mis.edu', 'string', 'system', 'System email address for notifications');
        Setting::set('support_email', 'support@mis.edu', 'string', 'system', 'Support email address');
        Setting::set('max_upload_size', '50', 'integer', 'system', 'Maximum upload file size in MB');

        // Email Settings
        Setting::set('mail_driver', 'SMTP', 'string', 'email', 'Mail driver to use');
        Setting::set('mail_host', 'smtp.mailtrap.io', 'string', 'email', 'Mail server host');
        Setting::set('mail_port', '2525', 'integer', 'email', 'Mail server port');
        Setting::set('mail_username', '', 'string', 'email', 'Mail server username');
        Setting::set('mail_password', '', 'string', 'email', 'Mail server password');

        // Notification Settings
        Setting::set('notify_new_requests', true, 'boolean', 'notifications', 'Send email for new requests');
        Setting::set('notify_request_updates', true, 'boolean', 'notifications', 'Send email for request updates');
        Setting::set('notify_completed_requests', false, 'boolean', 'notifications', 'Send email for completed requests');
        Setting::set('notify_admin_alerts', true, 'boolean', 'notifications', 'Send admin alerts for pending requests');
        Setting::set('notification_frequency', 'Immediate', 'string', 'notifications', 'Notification frequency preference');

        // Security Settings
        Setting::set('session_timeout', '30', 'integer', 'security', 'Session timeout in minutes');
        Setting::set('password_policy', 'Standard (8+ characters)', 'string', 'security', 'Password policy requirement');
        Setting::set('require_2fa_admins', true, 'boolean', 'security', 'Require 2FA for admins');
        Setting::set('enable_ip_whitelist', false, 'boolean', 'security', 'Enable IP whitelisting');
        Setting::set('log_admin_actions', true, 'boolean', 'security', 'Log all admin actions');
    }
}
