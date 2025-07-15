<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\LoginController;

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\CalendarApiController;
use App\Http\Controllers\TrashPlaceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\ProductionController;

Route::get('/login', function () {
    return view('dashboard.login');
})->name('login');

Route::post('/login', [LoginController::class, 'authenticate'])
    ->middleware('throttle:5,1')
    ->name('admin.login');

Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth:employees')->name('logout');


Route::prefix('employee')->middleware('auth:employees')->group(function () {
    // Календарь API
    Route::get('/calendar/events', [CalendarApiController::class, 'index']);
    Route::get('/calendar/employees', [CalendarApiController::class, 'getEmployees']);
    
    Route::prefix('orders')->name('employee.orders.')->group(function () {
        
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');

        Route::get('/by-number', [OrderController::class, 'orderByNumber'])->name('orderByNumber');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::patch('/{order}', [OrderController::class, 'update'])->name('update');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');

        Route::post('/{order}/attachMedia', [AttachmentController::class, 'attachMediaByOrder'])->name('attachMedia');

    });
    Route::prefix('measurements')->name('employee.measurements.')->group(function () {
        Route::get('/', [MeasurementController::class, 'index'])->name('index');
        Route::get('/create', [MeasurementController::class, 'create'])->name('create');
        Route::post('/', [MeasurementController::class, 'store'])->name('store');
        Route::get('/{measurement}', [MeasurementController::class, 'show'])->name('show');
        Route::get('/{measurement}/edit', [MeasurementController::class, 'edit'])->name('edit');
        Route::patch('/{measurement}/update', [MeasurementController::class, 'update'])->name('update');
        Route::post('/{measurement}/addNote', [MeasurementController::class, 'addNote'])->middleware('notInProgress')->name('addNote');
        Route::post('/{measurement}/timeChange', [MeasurementController::class, 'timeChange'])->middleware('notInProgress')->name('timeChange');
        Route::post('/{measurement}/addAttachment', [AttachmentController::class, 'attachMediaByMeasurement'])->name('addAttachment');
        Route::post('/{measurement}/complete', [MeasurementController::class, 'complete'])->middleware('notInProgress')->name('complete');
        Route::delete('/{measurement}', [MeasurementController::class, 'destroy'])->name('destroy');
        Route::patch('/{measurement}', [MeasurementController::class, 'restore'])->name('restore');
    });

    Route::prefix('archived')->name('employee.archived.')->group(function () {
        Route::get('/', [TrashPlaceController::class, 'index'])->name('index');
        Route::patch('/{order}', [TrashPlaceController::class, 'restore'])->name('restore');
    });

    Route::prefix('contracts')->name('employee.contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::patch('/{contract}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
        Route::post('/{contract}/sign', [ContractController::class, 'sign'])->name('sign');
    });

    Route::prefix('users')->name('employee.users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/profile', [UsersController::class, 'profile'])->name('profile');
        Route::post('/', [UsersController::class, 'userAdd'])->name('userAdd');
        Route::patch('/{user}', [UsersController::class, 'changeRole'])->name('changeRole');
        Route::delete('/{user}', [UsersController::class, 'userDelete'])->name('userDelete');
        Route::patch('/profile', [UsersController::class, 'updateProfile'])->name('updateProfile');
    });

    Route::prefix('documentations')->name('employee.documentations.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/create', [DocumentationController::class, 'create'])->name('create');
        Route::post('/', [DocumentationController::class, 'store'])->name('store');
        Route::get('/{documentation}', [DocumentationController::class, 'show'])->name('show');
        Route::get('/{documentation}/edit', [DocumentationController::class, 'edit'])->name('edit');
        Route::patch('/{documentation}', [DocumentationController::class, 'update'])->name('update');
        Route::delete('/{documentation}', [DocumentationController::class, 'destroy'])->name('destroy');
        Route::post('/{documentation}/confirm', [DocumentationController::class, 'confirm'])->name('confirm');
        Route::post('/{documentation}/addAttachment', [AttachmentController::class, 'attachMediaByDocumentation'])->name('addAttachment');
    });

    Route::prefix('installations')->name('employee.installations.')->group(function () {
        Route::get('/', [InstallationController::class, 'index'])->name('index');
        Route::get('/create', [InstallationController::class, 'create'])->name('create');
        Route::post('/', [InstallationController::class, 'store'])->name('store');
        Route::get('/{installation}', [InstallationController::class, 'show'])->name('show');
        Route::get('/{installation}/edit', [InstallationController::class, 'edit'])->name('edit');
        Route::patch('/{installation}', [InstallationController::class, 'update'])->name('update');
        Route::post('/{installation}/confirm', [InstallationController::class, 'confirm'])->name('confirm');
        Route::delete('/{installation}', [InstallationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('productions')->name('employee.productions.')->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::post('/{production}/complete', [ProductionController::class, 'complete'])->name('complete');
    });
});


