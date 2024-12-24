<?php

use App\Http\Controllers\API\TerraAPIWebHookController;
use App\Http\Controllers\API\TerraController;
use App\Http\Controllers\API\UserTargetCalorieController;
use App\Http\Controllers\ApiCreateUserController;
use App\Http\Controllers\ApiDashboardController;
use App\Http\Controllers\ApiPaymentController;
use App\Http\Controllers\ApiStripeController;
use App\Http\Controllers\ApiVideoController;
use App\Http\Controllers\ApiVideoLiveStreamController;
use App\Http\Controllers\ApiVideoPlaylistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiTransationController;
use App\Http\Controllers\ApiUserProfileController;
use App\Http\Controllers\ApiResourceController;
use App\Http\Controllers\ApiProfileQuestionController;
use App\Http\Controllers\ApiVideoEventController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserMoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// API ROUTES FOR DASHBOARD
Route::get('/dashboard', [ApiDashboardController::class, 'get_dashboard_data']);

// Auth Api
Route::post('/login-user', [AuthController::class, 'login_user']);
// Route::post('/register-user', [AuthController::class, 'register_user']);

// 09-08-2024       Coded by Vartik Anand Start
Route::post('/register-user', [ApiCreateUserController::class, 'create_user']);
// 09-08-2024       Coded by Vartik Anand End




// 03-08-2024       Coded by Vartik Anand Start
// Video related routes
//single video route
Route::get('/video/{url_id}/{user_id}', [ApiVideoController::class, 'get_single_video']);
// All Video
Route::post('/all_video', [ApiVideoController::class, 'get_all_video']);
//Video playlist route
Route::post('/playlist/{id}', [ApiVideoPlaylistController::class, 'get_playlist_Videos']);
Route::post('/how_to_basic/{id}', [ApiVideoPlaylistController::class, 'get_how_to_basic']);
// All Playlist
Route::post('/all_playlist', [ApiVideoPlaylistController::class, 'get_all_playlist']);

// All Resource and List
Route::get('/all_resource_list', [ApiResourceController::class, 'get_all_resource_list']);
Route::post('/all_resource', [ApiResourceController::class, 'get_all_resource']);
// get precall resources
Route::get('/all_precall_resource_list', [ApiResourceController::class, 'get_all_precall_resource_list']);
Route::post('/all_precall_resource', [ApiResourceController::class, 'get_all_precall_resource']);

// Live stream video route
Route::get('/watch/video/live/{id}', [ApiVideoLiveStreamController::class, 'getLiveVideo']);

// 03-08-2024       Coded by Vartik Anand End


// Api to get logged in user audience type
// 11-09-2024   Coded by Vartik Anand Start
Route::post('/get-audience', [AuthController::class, 'get_audience']);
// 11-09-2024   Coded by Vartik Anand End


// 12-09-2024  Code by vartik anand start
// Payment related routes
// Api route to generate csrf token
Route::middleware('web')->get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
// Api for poli payment
Route::post('/payment/poli', [ApiPaymentController::class, 'initiate_transaction']);
//Api Route For stripe Payment
Route::post('/payment/stripe', [ApiStripeController::class, 'create_pay_intent']);
// 12-09-2024  Code by vartik anand end


// Payment and User Profile API
// Define a route group with middleware and prefix
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/payment_list', [ApiTransationController::class, 'getTransation']);
    Route::get('/getper', [ApiTransationController::class, 'getPer']);
    Route::get('/profile_list', [ApiUserProfileController::class, 'getProfile']);
    Route::post('/update_users/{id}', [ApiUserProfileController::class, 'update'])->name('edit_user_profile.show');
    Route::post('/update_password/{id}', [ApiUserProfileController::class, 'update_password'])->name('edit_user_password.show');
    // Profile Questions
    Route::get('/profile-question', [ApiProfileQuestionController::class, 'get_profile_question'])->name('profile_question.get');
    Route::post('/profile-question/update', [ApiProfileQuestionController::class, 'update'])->name('profile_question.update');
    // Video Event
    Route::get('/video_event', [ApiVideoEventController::class, 'getVideoEvent'])->name('video_event.get');
    Route::get('/latest_video_event', [ApiVideoEventController::class, 'getLatestVideoEvent'])->name('video_event.latest');

    // Coded by Brent Philip
    // Favorites
    Route::post('/favorites', [FavoriteController::class, 'store']); // Create a new favorite and insert recipe data into the database
    Route::delete('/favorites', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/{userId}', [FavoriteController::class, 'checkFavorite']);
    Route::get('/favorites/{userId}', [FavoriteController::class, 'getAllFavorites']);
    // Recipes
    Route::get('/recipes', [RecipeController::class, 'fetchAllRecipes']); // Get all recipes
    Route::get('/recipes/favorites', [RecipeController::class, 'fetchFavoriteRecipes']); // Get all favorite recipes
    //comments
    Route::post('/comment', [CommentController::class, 'store']);
    Route::delete('/comment/{id}', [CommentController::class, 'destroy']);
    Route::put('/comment/update', [CommentController::class, 'update']);
    Route::get('/comments/{contentType}/{contentId}', [CommentController::class, 'fetchCommentsFor']);
    // Reactions
    Route::post('/comments/reaction', [ReactionController::class, 'addOrUpdateReaction']);
    Route::delete('/comments/reaction', [ReactionController::class, 'removeReaction']);
    // view all favorite videos
    Route::get('/favorite/videos/{user_id}', [ApiVideoController::class, 'getFavoriteVideos']);
    // is_complete video
    Route::post('/videos/{url_id}/completion', [ApiVideoController::class, 'updateCompletionStatus']);
    //Video playlist route
    Route::post('/playlist/{id}', [ApiVideoPlaylistController::class, 'get_playlist_Videos']);

    //moods
    Route::post('/moods', [UserMoodController::class, 'store']);
    Route::get('/moods', [UserMoodController::class, 'index']);
    Route::get('/user_moods', [UserMoodController::class, 'getUserMoods']);

    //target calories
    Route::apiResource('target-calories', UserTargetCalorieController::class);
});

// Coded by Brent Philip
Route::get('/{content_type}/{content_id}/comments', [CommentController::class, 'index'])
    ->where('content_type', 'video|recipe') // Ensure only valid content types
    ->name('comments.index');

// Country & Region Data GET
Route::get('/country', [ApiUserProfileController::class, 'country'])->name('country.show');
Route::get('/region/{country_id}', [ApiUserProfileController::class, 'region'])->name('region.show');
// Tag wise Video Display
Route::get('/videos-by-tags/{tags}', [ApiVideoController::class, 'getVideosByTags'])->name('videos.byTags');
//  All video tags
Route::get('/all-videos-by-tags', [ApiVideoController::class, 'getAllVideosByTags'])->name('Allvideos.byTags');
// All video Playlist tags
Route::get('/all-videosplaylist-by-tags', [ApiVideoPlaylistController::class, 'getAllVideoPlaylistByTags'])->name('AllvideoPlaylist.byTags');
// All video playlist url_id wise tags
Route::get('/all-videosplaylist-by-tags/{url_id}', [ApiVideoPlaylistController::class, 'getAllVideoPlaylistByTagsid'])->name('AllvideoPlaylist.byTags.url_id');

// watch video wise data GET
Route::post('/watch/videos/store', [ApiVideoController::class, 'watch_video']);
Route::get('/get/watch/videos/{id}', [ApiVideoController::class, 'get_watch_video']);


//TERRA API INTEGRATION
Route::post('terra-web-hook', TerraAPIWebHookController::class);

Route::group(['prefix' => 'terra', 'middleware' => ['auth:sanctum']], function () {
    Route::get('active-connections', [TerraController::class, 'activeConnections']);
    Route::post('connection-widget', [TerraController::class, 'connectWidget']);
    Route::post('connect', [TerraController::class, 'connect']);
    Route::post('disconnect', [TerraController::class, 'disconnect']);

    Route::get('activity', [TerraController::class, 'activity']);
    Route::get('body', [TerraController::class, 'bodyData']);
    Route::get('daily', [TerraController::class, 'dailyData']);
    Route::get('menstruation', [TerraController::class, 'menstruation']);
    Route::get('nutrition', [TerraController::class, 'nutrition']);
    Route::get('sleep', [TerraController::class, 'sleepData']);
    // Route::get('athlete', [TerraController::class, 'athlete']);

    Route::get('nutritions', [TerraController::class, 'nutritions']);
});
