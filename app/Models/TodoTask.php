<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoTask extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'todo_tasks';

    // Specify fillable fields for mass assignment
    protected $fillable = ['name', 'status'];
}
