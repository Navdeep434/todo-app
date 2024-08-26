<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToDoListController;

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

// Route to display the task list
Route::get('/', [ToDoListController::class, 'index'])->name('tasks.index');

// Route to add a new task
Route::post('/tasks', [ToDoListController::class, 'store'])->name('tasks.store');

// Route to mark task as done
Route::patch('/tasks/{task}/done', [ToDoListController::class, 'markAsDone'])->name('tasks.done');

// Route to soft delete a task
Route::delete('/tasks/{task}', [ToDoListController::class, 'destroy'])->name('tasks.destroy');
