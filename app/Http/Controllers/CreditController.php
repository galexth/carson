<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Stripe\Customer;
use Stripe\PaymentIntent;

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
        $startingAfter = $request->input('starting_after');
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
            throw new ApiException('Failed to get charges list. Please contact the support team.', 400);
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

                $customer = Customer::retrieve(\Auth::user()->stripe_id);

                $paymentIntent = PaymentIntent::create([
                    'amount' => $credits * $creditPrice * 100,
                    'currency' => 'usd',
                    'customer' => \Auth::user()->stripe_id,
                    'payment_method' => $customer->invoice_settings['default_payment_method'],
                    'confirmation_method' => 'automatic',
                    'confirm' => true,
                    'metadata' => [
                        'credits' => $credits,
                        'price' => $creditPrice
                    ]
                ]);

                if (in_array($paymentIntent->status, [
                    PaymentIntent::STATUS_REQUIRES_ACTION,
                    PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD
                ])) {
                    throw new ApiException('Payment failed. Please contact the support team.', 400);
                }

            });
        } catch (\Exception $e) {
            throw new ApiException('Failed to buy credits. Please contact the support team.', 400);
        }

        return response(['status' => true, 'credits' => \Auth::user()->credits]);
    }
}
