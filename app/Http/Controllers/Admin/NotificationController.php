<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('status', 'সব নোটিফিকেশন পড়া হিসেবে চিহ্নিত হয়েছে।');
    }

    public function destroy(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        return back()->with('status', 'নোটিফিকেশন মুছে ফেলা হয়েছে।');
    }
}
