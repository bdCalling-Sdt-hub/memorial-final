<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoryController;


use App\Http\Controllers\Api\Addmin\AboutController;
use App\Http\Controllers\Api\Addmin\AdminStoryController;
use App\Http\Controllers\Api\Addmin\DashboardController;
use App\Http\Controllers\Api\Addmin\SubscribController;
use App\Http\Controllers\Api\Addmin\UserController;
use App\Http\Controllers\Api\Webapi\ContactController;
use App\Http\Controllers\Api\Webapi\ServiceController;
use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeleteUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RulesRegulationController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\SubscriptionController;

Route::group([
    ['middleware' => 'auth:api']
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/email-verified', [AuthController::class, 'emailVerified']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profile', [AuthController::class, 'loggedUserData']);
    Route::post('forget-pass', [AuthController::class, 'forgetPassword']);
    Route::post('/verified-checker', [AuthController::class, 'emailVerifiedForResetPass']);
    Route::post('/reset-pass', [AuthController::class, 'resetPassword']);
    Route::post('/update-pass', [AuthController::class, 'updatePassword']);
    Route::put('/profile/edit/{id}', [AuthController::class, 'editProfile']);
    Route::post('/resend-otp',[AuthController::class,'resendOtp']);

    Route::post('/social-login',[SocialLoginCOntroller::class,'socialLogin']);
    //notification
    Route::get('/notification', [NotificationController::class, 'notification']);

    Route::get('/user-read-notification', [NotificationController::class, 'userReadNotification']);

});


// ================ WEB API ================== //

Route::post('/contact', [ContactController::class, 'contact']);

Route::get('/recent/story', [ContactController::class, 'recentStory']);
Route::get('/all/story', [ContactController::class, 'allStory']);
Route::get('/story/details/{id}', [ContactController::class, 'storyDetails']);
Route::get('/about', [ContactController::class, 'about']);
Route::get('/pricing', [ContactController::class, 'priceing']);
Route::get('/terms/condition', [ContactController::class, 'terms_condition']);
Route::get('/privacy/policy', [ContactController::class, 'privacy']);

// Service //

Route::resource('/service', ServiceController::class);

Route::get('/show-package',[PackageController::class,'showPackage']);
// category
Route::post('/add-category', [CategoryController::class, 'addCategory']);
Route::get('/show/category', [CategoryController::class, 'show_category']);
// package
Route::post('/add-package', [PackageController::class, 'addPackage']);

Route::middleware(['user','auth:api'])->group(function () {
    //Filter and search
    Route::get('/filter-story-by-category',[StoryController::class,'filterStoryByCategory']);
    //story details in app
    Route::get('/story-details',[StoryController::class,'storyDetails']);
    //my subscription
    Route::get('/my-subscription',[SubscriptionController::class,'mySubscription']);
    Route::get('/upgrade-subscription',[SubscriptionController::class,'upgradeSubscription']);
    // Subscription
    Route::post('/user-subscription', [SubscriptionController::class, 'userSubscription']);
    Route::post('/purchase-subscription', [SubscriptionController::class, 'purchaseNewSubscription']);
    // my story
    Route::get('/my-story', [StoryController::class, 'myStory']);
    // delete story
    Route::get('/delete-story', [StoryController::class, 'deleteStory']);
    // archive story
    Route::get('/archive-story', [StoryController::class, 'archiveStory']);

    // Subscription
//    Route::post('/user-subscription', [SubscriptionController::class, 'userSubscription']);

    Route::get('/terms-condition', [RulesRegulationController::class, 'termsCondition']);
    Route::get('/privacy-policy', [RulesRegulationController::class, 'privacyPolicy']);
    Route::get('/about-us', [RulesRegulationController::class, 'aboutUs']);

    //delete user
    Route::post('delete-user',[DeleteUserController::class,'deleteUser']);

    //notification

    Route::get('/read-unread',[NotificationController::class,'markRead']);

    //
});

//payment
Route::post('/paypal-payment',[PaymentController::class,'paypalPayment']);
Route::post('/success',[PaymentController::class,'paypalSuccess']);

Route::middleware(['payment.user','auth:api'])->group(function () {
    //add Story
    Route::post('/add-story',[StoryController::class,'addStory']);
// repost api
    Route::post('/edit-story',[StoryController::class,'editStory']);
    //pending story
    Route::get('/pending-story',[StoryController::class,'pendingStory']);
    // re post story
    Route::post('/re-post-story',[StoryController::class,'rePostStory']);
    Route::get('/re-post-story-test',[StoryController::class,'rePostStory1']);
});

Route::middleware(['admin','auth:api'])->group(function () {

    //update category
    Route::post('/update-category/{id}',[CategoryController::class,'updateCategory']);
    //notification

    Route::get('/admin-notification',[NotificationController::class,'adminNotification']);
    Route::get('/read-notification',[NotificationController::class,'readNotificationById']);


  // ================== Admin Api ====================//

    Route::get('/user/list', [UserController::class, 'userList']);

    Route::get('/package/show', [UserController::class, 'package']);
    Route::get('/package/details/{id}', [UserController::class, 'userDetails']);
    Route::get('/search/subscrib/user', [UserController::class, 'search_subscriber']);

    // =================== SUBSCRIBE  ===================//

    Route::get('/edit/subscription/{id}', [SubscribController::class, 'edit_subscribe_package']);
    Route::post('/update/subscription', [SubscribController::class, 'update_package']);
    Route::delete('/package/delete/{id}', [SubscribController::class, 'deletePackage']);

    // ===================== SETTING ================//

    Route::get('/setting', [AboutController::class, 'settings']);

    Route::get('/edit/privacy/{id}', [AboutController::class, 'edit_privacy']);
    Route::post('/update/privacy', [AboutController::class, 'update_privacy']);

    Route::get('/edit/terms/{id}', [AboutController::class, 'edit_terms']);
    Route::post('/update/terms', [AboutController::class, 'update_terms']);

    Route::get('/edit/about/{id}', [AboutController::class, 'edit_about']);
    Route::post('/update/about', [AboutController::class, 'update_about']);

    // =================== STORY ==========================//

    Route::get('/user/story', [AdminStoryController::class, 'user_story']);
    Route::get('/story/request', [AdminStoryController::class, 'userRequest']);
    Route::post('/story/status', [AdminStoryController::class, 'story_status']);
    Route::get('/details/story/{id}', [AdminStoryController::class, 'story_details']);

    // ============ DASH BOARD ====================//

    Route::get('/dashboard', [DashboardController::class, 'count_category_story']);
    Route::get('/recent/transection', [DashboardController::class, 'recent_transection']);
    Route::get('/transection/details/{id}', [DashboardController::class, 'transetion_details']);
    Route::get('/month/income/ratio', [DashboardController::class, 'monthIncome_ratio']);
    // =================== INCOME ============================//

    Route::get('/income', [DashboardController::class, 'income']);
    Route::get('/daily/income', [DashboardController::class, 'daily_income']);
    Route::get('/daily/income/details/{id}', [DashboardController::class, 'daily_income_details']);
    Route::get('/weekly/income', [DashboardController::class, 'weekly_income']);
    Route::get('/month/income', [DashboardController::class, 'monthIncome']);
});

Route::middleware(['super.admin','auth:api'])->group(function () {
    //super admin
    Route::post('/add-admin', [AuthAdminController::class, 'addAdmin']);
    Route::get('/show-admin', [AuthAdminController::class, 'showAdmin']);
    Route::get('/delete-admin/{id}', [AuthAdminController::class, 'deleteAdmin']);
});

Route::get('/notification-event',[NotificationController::class,'notificationEvent']);



