<?php

namespace App\Http\Controllers;

use App\Events\SendNotificationEvent;
use App\Models\Story;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{

    public function addStory(Request $request)
    {
        $user = auth()->user();
        $checkSubscriptionHaveStory = $this->checkSubscriptionHaveStory($user);
        if ($checkSubscriptionHaveStory === false) {
            $inappropriateWords = ['word1', 'word2', 'word3'];
            Validator::extend('no_inappropriate_words', function ($attribute, $value) use ($inappropriateWords) {
                foreach ($inappropriateWords as $word) {
                    if (stripos($value, $word) !== false) {
                        return false; // If any inappropriate word is found, return false
                    }
                }
                return true; // If no inappropriate words are found, return true
            });
            $validator = Validator::make($request->all(), [
                'user_id' => '',
                'category_id' => '',
                'subscription_id' => '',
                'story_title' => '',
                'story_image.*' => 'required|mimes:jpeg,png,jpg,gif,svg',
                'music' => '',
                'music_type' => '',
                'description' => 'no_inappropriate_words',
                'story_status' => '',
                'birth_date' => 'required',
                'death_date' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $subscription_id = $request->subscription_id;
            $subscription_details = Subscription::with('package')->where('id', $subscription_id)->first();
            $package_id = $subscription_details['package']['id'];
            $amount = $subscription_details['package']['amount'];
            $word_limit = $subscription_details['package']['word_limit'];
            $image_limit = $subscription_details['package']['image_limit'];

            if ($package_id == 1) {
                $wordLimit = $word_limit;
                $imageLimit = $image_limit;
            } elseif ($package_id == 2) {
                $wordLimit = $word_limit;
                $imageLimit = $image_limit;
            } elseif ($package_id == 3) {
                $wordLimit = $word_limit;
                $imageLimit = $image_limit;
            }
            // Validate description length based on word limit
//        $descriptionLength = str_word_count($request->description);
            $descriptionLength = strlen($request->description);
            if ($descriptionLength > $wordLimit) {
                return response()->json(['message' => 'Description exceeds word limit for this subscription.'], 400);
            }
            // Validate image count based on image limit
            if (count($request->file('story_image')) > $imageLimit) {
                return response()->json(['message' => 'Number of images exceeds limit for this subscription.'], 400);
            }

            $story = new Story();
            $story->user_id = $request->user_id;
            $story->category_id = $request->category_id;
            $story->subscription_id = $request->subscription_id;
            $story->story_title = $request->story_title;
            $story->music_type = $request->music_type;
            $story->description = $request->description;
            $story->birth_date = $request->birth_date;
            $story->death_date = $request->death_date;
            $story_music = array();
            if ($request->hasFile('music')) {
                foreach ($request->file('music') as $music) {
                    $musicName = time() . '.' . $music->getClientOriginalExtension();
                    $music->move(public_path('music'), $musicName);
                    $path = '/music/' . $musicName;
                    $story_music[] = $path;
                }
            }
            $story_image = array();
            if ($request->hasFile('story_image')) {
                foreach ($request->file('story_image') as $image) {
                    $imageName = uniqid() . '_' . $image->getClientOriginalName(); // Use a combination of uniqueid() and original filename for better uniqueness
                    $image->move(public_path('story-image'), $imageName);
                    $path = '/story-image/' . $imageName;
                    $story_image[] = $path;
                }
            }
            $story->music = json_encode($story_music);
            $story->story_image = json_encode($story_image, true);
            $story->save();
            $result = app('App\Http\Controllers\NotificationController')->sendAdminNotification('has posted a story,needs to approve', $story->created_at, auth()->user()->fullName, $story);
            event(new SendNotificationEvent('has posted a story,needs to approve', $story->created_at, auth()->user()));
            return response()->json([
                'message' => 'Story add successfully',
                'data' => $story,
                'music' => json_decode($story['music']),
                'image' => json_decode($story['story_image']),
                'notification' => $result,
            ], 200);

        }else{
            return response()->json([
                'message' => 'Using this subscription, User already have story ! You need subscription',
            ],400);
        }
    }

    public function checkSubscriptionHaveStory($user){

        // Check if the user has a subscription for story reposting
        $latest_subscription = $user->subscription()->latest()->first();
        if ($latest_subscription){
           $latest_story = Story::where('subscription_id',$latest_subscription->id)->where('story_status',1)->where('archived', 0)->first();
           if (empty($latest_story)){
               return false;
           }else{
               return true;
           }
        }
    }

    public function filterStoryByCategory(Request $request)
    {
        $category_id = $request->category_id;
        $category_name = $request->category_name;
        $story_title = $request->story_title;
        $username = $request->username;

        $query = Story::query()->with('category', 'user')->where('story_status', 1)->where('archived', 0);

        if ($category_id !== null) {
            $query->where('category_id', $category_id);
        }
        if ($category_name !== null) {
            $query->whereHas('category', function ($q) use ($category_name) {
                $q->where('category_name', $category_name);
            });
        }
        if ($story_title !== null) {
            $query->where('story_title', 'like', '%' . $story_title . '%');
        }
        if ($username !== null) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('fullName', $username);
            });
        }

        $perPage = 10;
        $story_list = $query->paginate($perPage);
//        $story_list = $query->get();

        $formatted_stories = $story_list->map(function ($story) {
            $story->story_image = json_decode($story->story_image);
            $story->music = json_decode($story->music);
            return $story;
        });
        return response()->json([
            'message' => 'success',
            'data' => $formatted_stories
        ]);
    }

    public function storyDetails(Request $request)
    {
        $story_id = $request->story_id;
        $story_details = Story::with('category', 'user')->where('id', $story_id)->get();

        $formatted_stories = $story_details->map(function ($story) {
            $story->story_image = json_decode($story->story_image);
            $story->music = json_decode($story->music);
            return $story;
        });

        return response()->json([
            'message' => 'success',
            'data' => $formatted_stories
        ]);
    }

    public function myStory()
    {
        $auth_user_id = auth()->user()->id;
        $story_details = Story::with('category', 'user')->where([
            'user_id' => $auth_user_id,
            'story_status' => 1,
            'archived' => 0,
        ])->get();

        $formatted_stories = $story_details->map(function ($story) {
            $story->story_image = json_decode($story->story_image);
            $story->music = json_decode($story->music);
            return $story;
        });

        return response()->json([
            'message' => 'success',
            'data' => $formatted_stories
        ]);
    }

    public function pendingStory()
    {
        $auth_user_id = auth()->user()->id;
        $story_details = Story::with('category', 'user')->where('user_id', $auth_user_id)->where('story_status', 0)->get();

        $formatted_stories = $story_details->map(function ($story) {
            $story->story_image = json_decode($story->story_image);
            $story->music = json_decode($story->music);
            return $story;
        });

        return response()->json([
            'message' => 'success',
            'data' => $formatted_stories
        ]);
    }
    public function deleteStory(Request $request)
    {

        $story_id = $request->story_id;

        $story = Story::find($story_id);
        if ($story) {
            $story_music = json_decode($story->music);
            $story_images = json_decode($story->story_image);
            foreach ($story_music as $musicPath) {
                $absoluteMusicPath = public_path($musicPath);

                if (file_exists($absoluteMusicPath)) {
                    unlink($absoluteMusicPath);
                }
            }

            // Delete each image file associated with the story
            foreach ($story_images as $imagePath) {
                $absoluteImagePath = public_path($imagePath);

                if (file_exists($absoluteImagePath)) {
                    unlink($absoluteImagePath);
                }
            }
            $story->delete();

            return response()->json([
                'message' => 'Story and associated files deleted successfully!'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Story Not Found'
            ], 404);
        }
    }

    public function archiveStory()
    {
        $check_user = auth()->user()->id;
        if ($check_user) {
            $archive_story = Story::with('category', 'user')->where('user_id', $check_user)->where('archived', 1)->get();
            $formatted_stories = $archive_story->map(function ($story) {
                $story->story_image = json_decode($story->story_image);
                $story->music = json_decode($story->music);
                return $story;
            });

            return response()->json([
                'message' => 'Archive Story',
                'data' => $formatted_stories
            ]);
        }
    }

//    public function rePostStory(Request $request)
//    {
//        $story_id = $request->id;
//        $auth_user_id = auth()->user()->id;
//        $story = Story::where('id',$story_id)->where('user_id',$auth_user_id)->where('archived',1)->first();
//        if ($story){
//            $story->archived = 0;
//            $story->story_status = 1;
//            $story->update();
//            return response()->json([
//                'message' => 'story updated successfully',
//                'data' => $story,
//            ]);
//        }else{
//            return response()->json([
//                'message' => 'story is not found',
//                'data' => []
//            ]);
//        }
//    }

    public function rePostStory(Request $request)
    {
        $auth_user = auth()->user();
        $auth_user_id = $auth_user->id;
        $story_id = $request->id;

        // Check if the user has a subscription for story reposting
        $latest_subscription = $auth_user->subscription()->latest()->first();

        // If the latest subscription exists and it allows story reposting
        if ($latest_subscription) {
            // Check if the user has already reposted a story
            $reposted_story = Story::where('id',$story_id)->where('story_status',1)->where('archived', 1)->first();

            if (!empty($reposted_story)) {
                // Proceed with reposting the story
                $story = Story::where('id', $story_id)->where('user_id', $auth_user_id)->where('archived', 1)->first();

                if ($story) {
                    $story->archived = 0;
                    $story->story_status = 1;
                    $story->subscription_id = $latest_subscription->id;
                    $story->update();

                    return response()->json([
                        'message' => 'Story reposted successfully',
                        'data' => $story,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Story not found or not eligible for reposting',
                        'data' => [],
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'You have already reposted a story',
                    'data' => [],
                ]);
            }
        } else {
            return response()->json([
                'message' => 'You don\'t have a subscription for story reposting',
                'data' => [],
            ]);
        }
    }

}
