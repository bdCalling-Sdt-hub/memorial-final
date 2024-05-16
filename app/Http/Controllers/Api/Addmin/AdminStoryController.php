<?php

namespace App\Http\Controllers\Api\Addmin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class AdminStoryController extends Controller
{
    public function user_story(Request $request)
    {
        $category = $request->catId;
        if ($category) {
            $user_story = Story::where('category_id', $category)->where('story_status', 1)->orderBy('id', 'desc')->paginate(10);
        } else {
            $user_story = Story::where('story_status', 1)->orderBy('id', 'desc')->paginate(10);
        }

        if ($user_story->count() > 0) {
            $user_story->transform(function ($story) {
                $story->story_image = json_decode($story->story_image, true);
                return $story;
            });
            return response()->json([
                'status' => 'success',
                'data' => $user_story
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }

    public function userRequest(Request $request)
    {
        $category_id = $request->id;

        $user_story = Story::where('story_status', 0)->orderBy('id', 'desc')->paginate(10);

        if ($user_story->count() > 0) {
            $user_story->transform(function ($story) {
                $story->story_image = json_decode($story->story_image, true);
                return $story;
            });
            return response()->json([
                'status' => 'success',
                'data' => $user_story
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }

    public function story_status(Request $request)
    {
        $update_status = Story::find($request->id);
        $update_status->story_status = $request->status;
        $update_status->save();
        if ($update_status) {
            if ($request->status == 1){
                $result = app('App\Http\Controllers\NotificationController')->sendNotification('Your story has been approved',$update_status->created_at,$update_status);
                return response()->json([
                    'status' => 'success',
                    'message' => 'User story has been approved',
                    'notification' => $result
                ], 200);
            }elseif ($request->status == 2){
                return response()->json([
                    'status' => 'success',
                    'message' => 'User story has been reject',
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'story update failed',
            ], 402);
        }
    }

    public function story_details($id)
    {
        $story_details = Story::where('id', $id)->with('category')->first();
        if ($story_details) {
            $story_details['story_image'] = json_decode($story_details['story_image'], true);
        }
        if ($story_details) {
            return response()->json([
                'status' => 'success',
                'data' => $story_details
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }
    }
}
