<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class CreditController extends Controller
{
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
            'credit' => 'required|integer|max:1000|min:1',
        ]);

        $credit = (int) $request->input('credit');

        $creditPrice = 5; //USD

        try {
            \Stripe\Charge::create([
                // amount in cents
                'amount' => $credit * $creditPrice * 100,
                'currency' => 'usd',
                'customer' => \Auth::user()->stripe_id,
                'description' => 'Credit charge',
                'metadata' => [
                    'credit' => $credit,
                    'price' => $creditPrice
                ]
            ]);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), 400);
        }

        return response(['status' => true]);
    }
}
