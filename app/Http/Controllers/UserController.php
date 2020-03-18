<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class UserController extends Controller
{
    /**
     * @param $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function show($id)
    {
        $user = User::with(['tasks'])->findOrFail($id);
        $user->append(['last_purchases']);

        return response($user);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function pending(Request $request)
    {
        $offset = (int) $request->input('offset') ?: 0;
        $limit = (int) $request->input('limit') ?: 10;

        $users = User::where('status', User::STATUS_PENDING)
            ->take($limit)
            ->orderByDesc('created_at')
            ->skip($offset)
            ->get();


        return response($users);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function ban($id)
    {
        $user = User::findOrFail($id);

        $user->ban();

        return response($user);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        $user->approve();

        return response($user);
    }
}
