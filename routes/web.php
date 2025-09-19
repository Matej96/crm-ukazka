<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/storage/{path}', [App\Http\Controllers\Api\HomeApiController::class, 'file'])->where('path', '.*')->middleware('auth');

//Route::prefix('admin')->group(function () {
\Illuminate\Support\Facades\Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => true, // Password Reset Routes...
    'verify' => true, // Email Verification Routes...
]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'lang', 'client'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    Route::get('/profile/{user}', [\App\Plugins\System\app\Http\Controllers\UserController::class, 'show'])->name('profile.show');
    Route::get('/profile/{user}/edit', [\App\Plugins\System\app\Http\Controllers\UserController::class, 'edit'])->name('profile.edit');
});

Route::prefix('api/home')->group(function () {
    Route::put('theme', [\App\Http\Controllers\Api\HomeApiController::class, 'setTheme'])->name('api.home.theme');
    Route::put('locale', [\App\Http\Controllers\Api\HomeApiController::class, 'setLocale'])->name('api.home.locale');
    Route::put('sidebar', [\App\Http\Controllers\Api\HomeApiController::class, 'setSidebar'])->name('api.home.sidebar');

    if(!\Illuminate\Support\Facades\App::isProduction()) {
        Route::post('database/reset', [\App\Http\Controllers\Api\AdminApiController::class, 'databaseReset'])->name('api.admin.database.reset');
    }
});

Route::middleware(['auth', 'lang', 'client'])->prefix('api/home')->name('api.')->group(function () {
    Route::get('{plugin}/{table}/get/{asDatatable?}', [App\Http\Controllers\Api\HomeApiController::class, 'get'])->name('get');
    Route::get('{plugin}/{table}/{model}/single/{asDatatable?}', [App\Http\Controllers\Api\HomeApiController::class, 'single'])->name('single');
    Route::get('{plugin}/{table}/export', [App\Http\Controllers\Api\HomeApiController::class, 'export'])->name('export');

    Route::get('autocomplete/company', [App\Http\Controllers\Api\HomeApiController::class, 'companyAutocomplete'])->name('autocomplete.company');
    Route::post('autocomplete-finstat/company', [App\Http\Controllers\Api\HomeApiController::class, 'companyFinstatAutocomplete'])->name('autocomplete.finstat.company');
    Route::post('autocomplete/user/birth', [App\Http\Controllers\Api\HomeApiController::class, 'userBirthAutocomplete'])->name('autocomplete.user.birth');
});
Route::middleware(['signed', 'lang', 'client'])->prefix('signed/home')->name('signed.')->group(function () {
    Route::post('autocomplete-finstat/company', [App\Http\Controllers\Api\HomeApiController::class, 'companyFinstatAutocomplete'])->name('autocomplete.finstat.company');
    Route::post('autocomplete/user/birth', [App\Http\Controllers\Api\HomeApiController::class, 'userBirthAutocomplete'])->name('autocomplete.user.birth');
});
//});
