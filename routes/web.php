<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCRUDController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeTestController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\ProductContentResourceController;
use App\Http\Controllers\ProductContentResourceListController;
use App\Http\Controllers\ProfileQuestionController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscribeCampDeltaController;
use App\Http\Controllers\SubscribeCampPrecallController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoEventController;
use App\Http\Controllers\VideoLiveStreamController;
use App\Http\Controllers\VideoPlaylistController;
use App\Http\Controllers\AvailabilityController;
use App\Livewire\AdminUserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// use Illuminate\Foundation\Auth\EmailVerificationRequest;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//// Public HTTP GET routes
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('home-test', HomeTestController::class);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    // Find the user by the ID from the URL
    $user = \App\Models\User::find($request->route('id'));

    if (!$user) {
        return redirect('/login')->with('error', 'Invalid verification link or user not found.');
    }

    // Ensure the email hash matches
    if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        return redirect('/login')->with('error', 'Invalid verification link.');
    }

    // Automatically log in the user
    Auth::login($user);

    // Mark the email as verified
    if ($user->markEmailAsVerified()) {
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return redirect('/dashboard')->with('success', 'Your email has been verified!');
})->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::match(['post', 'patch'], '/admin/video/cloudflare_get_tusurl', function (Request $request) {
    return VideoController::cloudflare_get_tusurl($request);
});

//// Public HTTP POST routes
Route::post('/poli/webhook/notice', [PoliController::class, 'receive_nudge']);

//// Routes for authorised users ONLY
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    //// HTTP GET routes
    // Route::get('/dashboard', [DashboardController::class, 'show'])->middleware('check.account.user_id')->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'show'])->middleware(['check.account.user_id', 'check.privileges'])->name('dashboard');
    Route::view('/ecommerce', 'ecommerce/basic')->name('ecommerce');
    Route::get('/billing', [AccountController::class, 'showBillingView'])->name('billing');
    Route::get('/referral', [AccountController::class, 'showReferralView'])->name('referral');
    Route::view('/help', 'help')->name('help');

    Route::get('/workouts', function (Request $request) {
        if (\App\Models\Account::getSessionAccount()['account']->hasActiveSubTo('camp_precall')) {
            return redirect()->route('playlist.list');
        }

        return VideoPlaylistController::watchVirtualPlaylist($request, 'workouts');
    })->name('workouts');
    Route::get('/resources', function (Request $request) {
        if (\App\Models\Account::getSessionAccount()['account']->hasActiveSubTo('camp_precall')) {
            return redirect()->route('resource_list.list');
        }

        return ProductContentResourceListController::seeVirtualResourceList($request, 'resources');
    })->name('resources');

    // Video related routes
    Route::get('/watch/{url_id}', function (Request $request, $url_id) {
        return VideoController::watch($request, $url_id);
    });
    Route::get('/watch/live/{url_id}', function (Request $request, $url_id) {
        return VideoLiveStreamController::watch($request, $url_id);
    });
    Route::get('/watch/playlist/{id}', function (Request $request, $id) {
        return VideoPlaylistController::watch($request, $id);
    })->name('playlist.watch');
    Route::get('/videos', [VideoPlaylistController::class, 'viewPlaylistsForAudience'])->name('playlist.list');

    // Resource related routes
    Route::get('/pc/resource_list/{id}', function (Request $request, $id) {
        return ProductContentResourceListController::see($request, $id);
    })->name('pc_resource_list.see');
    Route::get('/pc/resource_lists', [ProductContentResourceListController::class, 'viewResourceListsForAudience'])->name('resource_list.list');

    // Payment related routes
    Route::view('/stripe/confirmation', 'stripe_payment_confirmation');
    Route::view('/poli/ack_success', 'poli_ack', ['mode' => 'success']);
    Route::view('/poli/ack_fail', 'poli_ack', ['mode' => 'fail']);
    Route::view('/poli/ack_cancel', 'poli_ack', ['mode' => 'cancel']);

    Route::view('/purchase/confirmation', 'purchase_confirmation');

    Route::get('/subscribe/camp_delta', [SubscribeCampDeltaController::class, 'show']);
    Route::get('/subscribe/camp_precall', [SubscribeCampPrecallController::class, 'show']);
    Route::get('/subscribe/camp_delta_migrate', [SubscribeCampPrecallController::class, 'show_delta_migrate']);

    //// HTTP POST routes
    Route::post('/subscribe/camp_precall', [SubscribeCampPrecallController::class, 'subscribe']);
    Route::post('/subscribe/camp_delta_migrate', [SubscribeCampDeltaController::class, 'subscribe_migrate']);

    // Payment related routes
    Route::post('/stripe/create_pay_intent', [StripeController::class, 'create_pay_intent']);
    Route::post('/poli/initiate_transaction', [PoliController::class, 'initiate_transaction']);

    // Display Payment History
    Route::get('user/profile', [TransactionController::class, 'show'])->name('profile.show');

    // display regions data
    Route::get('/regions-by-country', [RegionController::class, 'getRegionsByCountryId'])->name('regions.byCountry');
});

//// Routes for admin users ONLY
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'admin',
])->group(function () {
    // Administration routes
    Route::view('/admin', '/admin/dashboard')->name('admin.dashboard');

    Route::view('/admin/video/upload', '/admin/video_upload')->name('admin.video_upload');
    // video event
    Route::view('/admin/video/event/upload', '/admin/live_video_upload')->name('admin.upload_video_event');
    // availability
    Route::view('/admin/availability/upload', '/admin/availability_upload')->name('admin.upload_availability');
    Route::view('/admin/video/create_playlist', '/admin/create_playlist')->name('admin.create_playlist');
    Route::view('/admin/resource/upload', '/admin/resource_upload')->name('admin.resource_upload');
    Route::view('/admin/resource/create_resource_list', '/admin/create_resource_list')->name('admin.create_resource_list');

    Route::get('/admin/crud/list/{model}', function (Request $request, $model) {
        return AdminCRUDController::list($request, $model);
    });
    Route::delete('/admin/crud/{model_class_name}/{id}', [AdminCRUDController::class, 'destroy'])->name('crud.destroy');
    Route::get('/admin/crud/report/{report}', function (Request $request, $report) {
        return AdminCRUDController::report($request, $report);
    });

    Route::get('/admin/download/{file}', function (Request $request, $file) {
        return AdminController::download($request, $file);
    });

    // HTTP POST routes for admin
    Route::get('/admin/video/upload', [VideoController::class, 'upload'])->name('admin.video_upload');
    Route::post('/admin/video/upload', [VideoController::class, 'upload'])->name('submit_admin_video_upload');
    // video event
    Route::get('/admin/video/event/upload', [VideoEventController::class, 'upload'])->name('admin.upload_video_event');
    Route::post('/admin/video/event/upload', [VideoEventController::class, 'upload'])->name('submit_event');
    // availability
    Route::get('/admin/availability/upload', [AvailabilityController::class, 'upload'])->name('admin.upload_availability');
    Route::post('/admin/availability/upload', [AvailabilityController::class, 'submitAvailability'])->name('submit_availability');
    // video event Route to show the edit form
    Route::get('/admin/videoevents/{id}/edit', [VideoEventController::class, 'edit'])->name('videoevents.edit');
    Route::post('/admin/videoevents/{id}/update', [VideoEventController::class, 'update'])->name('videoevents.update');
    Route::delete('/admin/videoevents/{model_class_name}/{id}', [VideoEventController::class, 'destroy'])->name('videoevents.destroy');
    // Route to show the edit form
    Route::get('/admin/videos/{id}/edit', [VideoController::class, 'edit'])->name('videos.edit');
    Route::post('/admin/videos/{id}/update', [VideoController::class, 'update'])->name('videos.update');
    Route::delete('/admin/videos/{model_class_name}/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');

    Route::get('/admin/video/create_playlist', [VideoPlaylistController::class, 'create'])->name('admin.create_playlist');
    Route::post('/admin/video/create_playlist', [VideoPlaylistController::class, 'create'])->name('submit_admin_create_playlist');
    // Existing Video display
    Route::post('/playlists/{id}/video-update', [VideoPlaylistController::class, 'video_update'])->name('playlists.video_update');
    // Route to show the edit form
    Route::get('/admin/video_playlist/{id}/edit', [VideoPlaylistController::class, 'edit'])->name('video_playlist.edit');
    Route::post('/admin/video_playlist/{id}/update', [VideoPlaylistController::class, 'update'])->name('video_playlist.update');
    Route::delete('/admin/video_playlist/{model_class_name}/{id}', [VideoPlaylistController::class, 'destroy'])->name('video_playlist.destroy');

    Route::post('/admin/resource/upload', [ProductContentResourceController::class, 'upload'])->name('submit_admin_resource_upload');
    // Route to show the edit form
    Route::get('/admin/resource/{id}/edit', [ProductContentResourceController::class, 'edit'])->name('resource.edit');
    Route::post('/admin/resource/{id}/update', [ProductContentResourceController::class, 'update'])->name('resource.update');
    Route::delete('/admin/resource/{model_class_name}/{id}', [ProductContentResourceController::class, 'destroy'])->name('resource.destroy');

    Route::post('/admin/video/create_resource_list', [ProductContentResourceListController::class, 'create'])->name('submit_admin_create_resource_list');

    Route::prefix('admin')->group(function () {
        Route::get('{userId}/profile', AdminUserProfile::class)->name('admin.user.profile');
    });

    // edit playlist module delete video
    Route::delete('/remove-video/{videoId}/{playlistId}', [VideoPlaylistController::class, 'delete_video'])->name('remove.video');
    // Drag and Drop Column
    Route::post('/save-video-order', [VideoPlaylistController::class, 'saveOrder'])->name('save_video_order');

    // Profile Questions
    Route::post('/profile/update', [ProfileQuestionController::class, 'update'])->name('profile_question.update');
});
