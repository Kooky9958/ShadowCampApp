<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\ProductContentResource;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {

        $user = Auth::user();

        // Check if user privileges are >= 10000
        if ($user->privileges >= 10000) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', array_merge(Account::getSessionAccount(), ['video_most_recent' => Video::getMostRecent(1)->first(), 'resource_most_recent' => ProductContentResource::getMostRecent(1)->first()]));
    }
}
