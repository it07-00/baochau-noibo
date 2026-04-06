<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogAuthActivity
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($event): void
    {
        $eventName = '';
        $description = '';
        $user = null;

        if ($event instanceof Login) {
            $eventName = 'login';
            $description = 'Đăng nhập hệ thống';
            $user = $event->user;
        } elseif ($event instanceof Logout) {
            $eventName = 'logout';
            $description = 'Đăng xuất hệ thống';
            $user = $event->user;
        } elseif ($event instanceof Failed) {
            $eventName = 'failed_login';
            $attempted = $event->credentials['username'] ?? $event->credentials['email'] ?? $event->credentials['login'] ?? 'Không rõ';
            $description = 'Đăng nhập thất bại cho tài khoản: ' . $attempted;
            $user = $event->user; // Might be null
        }

        if ($eventName) {
            activity('auth')
                ->event($eventName)
                ->causedBy($user)
                ->withProperties([
                    'ip' => $this->request->ip(),
                    'user_agent' => $this->request->userAgent()
                ])
                ->log($description);
        }
    }
}
