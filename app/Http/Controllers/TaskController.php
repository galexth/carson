<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class TaskController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function index(Request $request)
    {
        $offset = (int) $request->input('offset') ?: 0;
        $limit = (int) $request->input('limit') ?: 10;

        $tasks = Task::take($limit)
            ->orderByDesc('created_at')
            ->skip($offset)
            ->get();

        return response($tasks);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required|max:5000',
            'category' => 'string|max:255',
        ]);

        $this->authorize('store', Task::class);

        $task = \DB::transaction(function () use ($request) {
            \Auth::user()->decrement('credit');
            return Task::create($request->all());
        });

        return response($task, 201);
    }
}
