<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DynamicMailer
{
    /**
     * Send a mailable through the given admin's own configured mail account.
     * Silently skips (returns false) if they haven't configured one, or if
     * sending fails - a bad SMTP config shouldn't break the request that
     * triggered it (e.g. creating an employee or assigning a task).
     */
    public function send(User $admin, string $toEmail, Mailable $mailable): bool
    {
        if (! $admin->hasMailConfigured()) {
            return false;
        }

        config([
            'mail.mailers.dynamic' => [
                'transport' => 'smtp',
                'host' => $admin->mail_host,
                'port' => $admin->mail_port ?: 587,
                'encryption' => $admin->mail_encryption ?: null,
                'username' => $admin->mail_username,
                'password' => $admin->mail_password,
            ],
            'mail.from' => [
                'address' => $admin->mail_from_address,
                'name' => $admin->mail_from_name ?: $admin->name,
            ],
        ]);

        try {
            Mail::mailer('dynamic')->to($toEmail)->send($mailable);

            return true;
        } catch (Throwable $e) {
            Log::warning('DynamicMailer: failed to send via admin-configured mail account', [
                'admin_id' => $admin->id,
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
