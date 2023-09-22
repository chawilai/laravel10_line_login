<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/auth/redirect', function () {
    return Socialite::driver('line')->redirect();
});

Route::get('/auth/callback', function () {

    $socialite_data = Socialite::driver('line')->user();

    $user = User::firstOrCreate(
        [
            'provider' => 'line',
            'provider_id' => $socialite_data->id,
        ],
        [
            'name' => $socialite_data->name,
            'avatar' => $socialite_data->avatar,
            'access_token' => $socialite_data->access_token,
            'refresh_token' => $socialite_data->refresh_token,
        ]
    );

    auth()->login($user);

    return redirect('/dashboard');
});

require __DIR__ . '/auth.php';
