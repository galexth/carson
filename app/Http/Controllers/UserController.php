<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Stripe\Customer;
use Stripe\PaymentMethod;

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
     * @throws \App\Exceptions\ApiException
     */
    public function ban($id)
    {
        $user = User::findOrFail($id);

        if ($user->isBanned()) {
            throw new ApiException('User is already banned.', 422);
        }

        $user->ban();

        return response($user);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \App\Exceptions\ApiException
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->isApproved()) {
            throw new ApiException('User is already approved.', 422);
        }

        if (! $user->stripe_id) {
            \DB::transaction(function () use ($user) {
                $customer = Customer::create([
                    'email' => $user->email,
                ]);

                $paymentMethod = PaymentMethod::retrieve(
                    'pm_card_visa'
                );

                $paymentMethod->attach([
                    'customer' => $customer->id,
                ]);

                $customer->invoice_settings = ['default_payment_method' => $paymentMethod->id];
                $customer->save();

                $user->stripe_id = $customer->id;
                $user->save();
            });
        }

        $user->approve();

        return response($user);
    }
}
