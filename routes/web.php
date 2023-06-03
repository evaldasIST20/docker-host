<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

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

Route::get('/', [ProjectController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/project/create', [ProjectController::class, 'create'])
    ->middleware('auth')->name('project-create');

Route::post('/project', [ProjectController::class, 'store'])
    ->middleware('auth')->name('project-store');

Route::delete('/project/{project}', [ProjectController::class, 'delete'])
    ->middleware('auth')->name('project-delete');

Route::put('/project/{project}', [ProjectController::class, 'update'])
    ->middleware('auth')->name('project-update');

Route::get('/project/{project}', [ProjectController::class, 'show'])
    ->middleware('auth')->name('project-show');

    
// ### Authorization routes ###
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
