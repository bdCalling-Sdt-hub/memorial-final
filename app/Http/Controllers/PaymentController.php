<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
class PaymentController extends Controller
{
    //
    public function paypalPayment(Request $request)
    {
        $provider = new PayPalClient;
//        $provider = \PayPal::setProvider();
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => url('/success'),
                "cancel_url" => "https://example.com/cancelUrl",
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->price
                    ]
                ]
            ]
        ]);

        if($response['id'] && $response['id'] != null){
            foreach ($response['links'] as $link){
                if ($link['rel'] == 'approve'){
                    return response()->json([
                        'link' => $link['href']
                    ]);
                }
                if ($link['rel'] == 'approve'){
                    return response()->json([
                        'link' => $link['href']
                    ]);
                }else{

                }
            }
        }
        return response()->json([
            'data' => $response,
        ]);
    }

    public function paypalSuccess(Request $request){
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);

        dd($response);
    }
}
