<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Stripe\Charge;

class CreditController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \App\Exceptions\ApiException
     */
    public function index(Request $request)
    {
        $startingAfter = (int) $request->input('starting_after');
        $limit = (int) $request->input('limit') ?: 10;

        if ($limit > 100) {
            throw new ApiException('Limit can range between 1 and 100.', 422);
        }

        try {
            $charges = \Auth::user()->charges([
                'limit' => $limit,
                'starting_after' => $startingAfter
            ]);
        } catch (\Exception $e) {
            throw new ApiException('Failed to buy credits. Please contact the support team.', 400);
        }

        return response($charges);

    }
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \App\Exceptions\ApiException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function buy(Request $request)
    {
        $this->validate($request, [
            'credits' => 'required|integer|max:1000|min:1',
        ]);

        $credits = (int) $request->input('credits');

        $creditPrice = 5; //USD

        try {
            \DB::transaction(function () use ($creditPrice, $credits) {
                \Auth::user()->increment('credits', $credits);
                Charge::create([
                    // amount in cents
                    'amount' => $credits * $creditPrice * 100,
                    'currency' => 'usd',
                    'customer' => \Auth::user()->stripe_id,
                    'description' => 'Credit charge',
                    'metadata' => [
                        'credits' => $credits,
                        'price' => $creditPrice
                    ]
                ]);
            });
        } catch (\Exception $e) {
            throw new ApiException('Failed to buy credits. Please contact the support team.', 400);
        }

        return response(['status' => true, 'credits' => \Auth::user()->credits]);
    }
}
