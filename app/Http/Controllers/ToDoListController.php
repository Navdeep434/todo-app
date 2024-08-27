<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TodoTask;

class ToDoListController extends Controller
{
    // Display all tasks
    public function index()
    {
        $tasks = TodoTask::all(); 
        return view('app-pages.todo-listing', compact('tasks'));
    }

    // Add a new task
    public function store(Request $request)
    {
        $request->validate([
            'task' => 'required|string|max:255|unique:todo_tasks,name',
        ], [
            'task.unique' => 'This task already exists in your to-do list.',
        ]);

        $task = TodoTask::create([
            'name' => $request->task,
            'status' => 'Pending',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'task' => $task
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task added successfully!');
    }

    // Mark task as done
    public function markAsDone($id, Request $request)
    {
        $task = TodoTask::findOrFail($id);
        $task->status = 'Done';
        $task->save();

        if ($request->ajax()) {
            return response()->json([
                'task' => $task
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as done!');
    }

    // Delete a task
    public function destroy($id, Request $request)
    {
        TodoTask::destroy($id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }
}
