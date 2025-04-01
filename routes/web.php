

<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

use App\Http\Controllers\ApiUserController;


// Route::get('/', function () {
//     //  return Inertia::render('Welcome', [
//     return Inertia::render('Register', [


//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });




Route::get('/' ,function () {
    return Inertia::render('Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('login');


Route::get('/register', function () {
    return Inertia::render('Auth/Register', [

    ]);
})->name('register');


Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // acessible by everyone dashboard , applications ,

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


      // acessible by everyone 
      Route::get('/orders', function () {
        return Inertia::render('Orders');
    })->name('orders');

    Route::get('/reports', function () {
        return Inertia::render('Reports');
    })->name('reports');


    Route::get('/password-reset', function () {
        return Inertia::render('ForgotPassword');
    })->name('password-reset');


    Route::get('v1/users', [ApiUserController::class, 'index']);



    // Route::group(['middleware' => ['role:SuperAdmin']], function () {

        
        // Route::get('v1/users', [ApiUserController::class, 'index']);
        Route::post('v1/user', [ApiUserController::class, 'store']);
        Route::put('v1/user/{id}', [ApiUserController::class, 'update'])->name('user.update');
        Route::delete('v1/user/{id}', [ApiUserController::class, 'destroy'])->name('user.destroy');
        Route::get('/permissions', function () {
            return Inertia::render('UserPermissions');
        })->name('permissions');
        Route::get('/user-roles', function () {
            return Inertia::render('UserRoles');
        })->name('user-roles');

        Route::get('/branches', function () {
            return Inertia::render('Branches');
        })->name('branches');

        Route::get('/user', function () {
            return Inertia::render('Users');
        })->name('user');

        Route::get('/settings', function () {
            return Inertia::render('Settings');
        })->name('settings');

        Route::get('/ivr-options', function () {
            return Inertia::render('IvrOptions');
        })->name('ivr-options');

        Route::get('/sms', function () {
            return Inertia::render('Sm');
        })->name('sms');

        Route::get('/whatsapp', function () {
            return Inertia::render('Whatsap');
        })->name('whatsapp');

        Route::get('/telegram', function () {
            return Inertia::render('Telegra');
        })->name('telegram');

        Route::get('/facebook', function () {
            return Inertia::render('Faceboo');
        })->name('facebook');

        Route::get('/twitter', function () {
            return Inertia::render('Twitte');
        })->name('twitter');

        Route::get('/email', function () {
            return Inertia::render('Emai');
        })->name('email');  
    });

 
// });



