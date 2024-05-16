<?php

namespace App\Http\Controllers\Api\Webapi;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\About;
use App\Models\Contact;
use App\Models\Package;
use App\Models\Privacy;
use App\Models\Story;
use App\Models\TermsConditions;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // ======= CONTACT SECTION ========== //

    public function contact(ContactRequest $request)
    {
        $postContact = new Contact();
        $postContact->full_name = $request->fullName;
        $postContact->phone = $request->phone;
        $postContact->email = $request->email;
        $postContact->address = $request->address;
        $postContact->message = $request->message;
        $postContact->save();
        if ($postContact) {
            return response()->json([
                'status' => 'success',
                'message' => ' Successfully submit your contact form'
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => ' Internal server error'
            ], 500);
        }
    }

    // ============= RECENT STORY ===============//

    public function recentStory()
    {
        $decode_img = [];
        $recent_story = Story::where('story_status', 1)->orderBy('id', 'desc')->paginate(6);
        foreach ($recent_story as $story) {
            $story['story_image'] = json_decode($story['story_image']);
            $decode_img[] = $story;
        }
        if ($decode_img) {
            return response()->json([
                'status' => 'success',
                'data' => $decode_img
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }

    public function allStory()
    {
        $decode_story = [];
        $all_story = Story::where('story_status', 1)->orderBy('id', 'desc')->get();
        foreach ($all_story as $story) {
            $story['story_image'] = json_decode($story['story_image']);
            $decode_story[] = $story;
        }
        if ($decode_story) {
            return response()->json([
                'status' => 'success',
                'data' => $decode_story
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }

    public function storyDetails($id)
    {
        $storyDetails = Story::where('id', $id)->first();
        if ($storyDetails) {
            $storyDetails['story_image'] = json_decode($storyDetails['story_image']);
        }
        if ($storyDetails) {
            return response()->json([
                'status' => 'success',
                'data' => $storyDetails
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }

    // ================ PRICING PAGE ================ //

    public function priceing()
    {
        $priceing_decode = [];
        $priceing = Package::orderBy('id', 'desc')->get();

        foreach ($priceing as $price) {
            $price['feature'] = json_decode($price['feature'], true);
            $priceing_decode[] = $price;
        }

        if ($priceing_decode) {
            return response()->json([
                'status' => 'success',
                'data' => $priceing_decode
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 200);
        }
    }

    public function about()
    {
        $about = About::first();
        if ($about) {
            return response()->json([
                'status' => 'success',
                'data' => $about
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }

    public function terms_condition()
    {
        $terms = TermsConditions::first();
        if ($terms) {
            return response()->json([
                'status' => 'success',
                'data' => $terms
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }

    public function privacy()
    {
        $privacy = Privacy::first();
        if ($privacy) {
            return response()->json([
                'status' => 'success',
                'data' => $privacy
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'data' => []
            ], 402);
        }
    }
}
