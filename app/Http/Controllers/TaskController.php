<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
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
     * @throws \App\Exceptions\ApiException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required|max:5000',
            'category' => 'string|max:255',
        ]);

        if (! \Auth::user()->hasCredits()) {
            throw new ApiException('Not enough credit.', 422);
        }

        $task = new Task($request->all());
        $task->user_id = \Auth::id();

        \DB::transaction(function () use ($request, $task) {
            \Auth::user()->decrement('credits');
            $task->save();
        });

        return response($task, 201);
    }
}
