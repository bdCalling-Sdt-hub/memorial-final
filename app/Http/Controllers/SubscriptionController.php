<?php

namespace App\Http\Controllers;
use App\Events\SendNotificationEvent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    //

    public function userSubscription(Request $request){
        $status = $request->status;

        // if subscription is successful
        if ($status == 'successful') {

            $auth_user = $request->user_id;
            $user = Subscription::where('user_id', $auth_user)->latest()->first();

            if (!$user || $user->package_id != $request->package_id) {
                $subscription = new Subscription();
            } else {
                $subscription = $user;
            }
            $subscription->package_id = $request->package_id;
            $subscription->user_id = $request->user_id;
            $subscription->tx_ref = $request->tx_ref;
            $subscription->amount = $request->amount;
            $subscription->currency = $request->currency;
            $subscription->payment_type = $request->payment_type;
            $subscription->status = $request->status;
            $subscription->email = $request->email;
            $subscription->name = $request->name;
            $subscription->save();
            if ($subscription) {
                $user = User::find($auth_user);
                $subscriptions = Subscription::where('user_id',$auth_user)->first();
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                if ($subscriptions){
                    $newEndDate = Carbon::parse($subscription->end_date)->addMonth();
                    $subscription->end_date = $newEndDate;
                    $subscription->update();
                    $admin_result = app('App\Http\Controllers\NotificationController')->sendAdminNotification('Purchase a subscription',$subscription->created_at,$subscription->name,$subscription);
                    event(new SendNotificationEvent('Purchase a subscription',$subscription->created_at,auth()->user()));
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'subscription complete',
                    'data' => $subscription,
                ], 200);
            }

        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your subscription is canceled'
            ]);
            // Put desired action/code after transaction has been cancelled here
        } else {
            // return getMessage();
            // Put desired action/code after transaction has failed here
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your transaction has been failed'
            ]);
        }
    }

    public function mySubscription(Request $request){
        $auth_user_id = auth()->user()->id;
        $my_subscription = Subscription::with('package')
            ->where('user_id', $auth_user_id)
            ->orderBy('created_at', 'desc') // Assuming 'created_at' is the column storing subscription creation timestamp
            ->first();


        if ($my_subscription){
            if(is_string($my_subscription->package->feature)) {
                $features = [];
                $features[] = ['feature' => $my_subscription->package->word_limit . ' Word Limit'];
                $features[] = ['feature' => $my_subscription->package->image_limit . ' Image Limit'];
                // You can add more dynamic features here if needed

                // Merge dynamic features with existing features
                $my_subscription->package->feature = array_merge(json_decode($my_subscription->package->feature, true), $features);
//                $my_subscription->package->feature = json_decode($my_subscription->package->feature);
            }
        }
        if ($my_subscription){
            return response()->json([
                'message' => 'success',
                'data' => $my_subscription
            ]);
        } else {
            return response()->json([
                'message' => 'success',
                'data' => []
            ]);
        }
    }

    public function purchaseNewSubscription(Request $request)
    {
        $status = $request->status;

        // if subscription is successful
        if ($status == 'successful') {
            $auth_user = auth()->user()->id;
            $subscription = new Subscription();
            $subscription->package_id = $request->package_id;
            $subscription->user_id = $auth_user;
            $subscription->tx_ref = $request->tx_ref;
            $subscription->amount = $request->amount;
            $subscription->currency = $request->currency;
            $subscription->payment_type = $request->payment_type;
            $subscription->status = $status;
            $subscription->email = auth()->user()->email;
            $subscription->name = auth()->user()->name;
            $subscription->save();
            if ($subscription) {
                $user = User::find($auth_user);
                $subscriptions = Subscription::where('user_id', $auth_user)->first();
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                if ($subscriptions) {
                    $newEndDate = Carbon::parse($subscription->created_at)->addMonth();
                    $subscription->end_date = $newEndDate;
                    $subscription->update();
                    $admin_result = app('App\Http\Controllers\NotificationController')->sendAdminNotification('Purchased a subscription', $subscription->created_at, $subscription);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'subscription complete',
                    'data' => $subscription,
                ], 200);
            }

        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your subscription is canceled'
            ]);
        } else {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your transaction has been failed'
            ]);
        }
    }
}
