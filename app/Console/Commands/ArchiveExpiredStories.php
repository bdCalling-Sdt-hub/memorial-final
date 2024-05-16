<?php

namespace App\Console\Commands;

use App\Models\Story;
use App\Models\Subscription;
use Illuminate\Console\Command;

class ArchiveExpiredStories extends Command
{

    protected $signature = 'stories:archive';
    protected $description = 'Archive stories of expired subscriptions';

//    public function handle()
//    {
//        $expiredSubscriptions = Subscription::where('end_date', '<', now())->get();
//        foreach ($expiredSubscriptions as $subscription) {
//            Story::where('user_id', $subscription->user_id)->update(['archived' => true]);
//            $subscription->update(['subscription_status' => true]);
//        }
//    }
//    public function handle()
//    {
//        $expiredSubscriptions = Subscription::where('end_date', '<', now())->get();
//
//        foreach ($expiredSubscriptions as $subscription) {
//            // Update subscription status
//            $subscription->update(['subscription_status' => 1]); // Assuming you want to set it to false for expired subscriptions
//
//            // Update story archive status
//            Story::where('user_id', $subscription->user_id)->update(['archived' => true]);
//        }
//    }
    public function handle()
    {
        $expiredSubscriptions = Subscription::where('end_date', '<', now())->get();

        foreach ($expiredSubscriptions as $subscription) {
            try {
                // Update subscription status
                $subscription->update(['subscription_status' => 1]);

                // Update story archive status
                Story::where('user_id', $subscription->user_id)->update(['archived' => true]);
            } catch (\Exception $e) {
                // Log the error
                \Log::error('Error updating subscription: ' . $e->getMessage());
                // Or display the error for debugging purposes
                // dd($e->getMessage());
            }
        }
    }


}
