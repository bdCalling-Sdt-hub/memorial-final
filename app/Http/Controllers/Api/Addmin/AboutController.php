<?php

namespace App\Http\Controllers\Api\Addmin;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Privacy;
use App\Models\TermsConditions;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function settings()
    {
        $auth = auth()->user();
        $privacy = Privacy::orderBy('id', 'desc')->first();
        $terms = TermsConditions::orderBy('id', 'desc')->first();
        $about = About::orderBy('id', 'desc')->first();

        return response()->json([
            'status' => 'success',
            'login_user' => $auth,
            'privacy' => $privacy,
            'terms' => $terms,
            'about' => $about
        ], 200);
    }

    public function edit_privacy($id)
    {
        $privacy = Privacy::where('id', $id)->orderBy('id', 'desc')->first();
        if ($privacy) {
            return response()->json([
                'status' => 'success',
                'data' => $privacy
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 200);
        }
    }

    public function update_privacy(Request $request)
    {
        $privacy_update = Privacy::find($request->id);
        $privacy_update->description = $request->description;
        $privacy_update->save();
        if ($privacy_update) {
            return response()->json([
                'status' => 'success',
                'data' => $privacy_update
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 402);
        }
    }

    public function edit_terms($id)
    {
        $terms = TermsConditions::where('id', $id)->orderBy('id', 'desc')->first();
        if ($terms) {
            return response()->json([
                'status' => 'success',
                'data' => $terms
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 200);
        }
    }

    public function update_terms(Request $request)
    {
        $update_terms = TermsConditions::find($request->id);
        $update_terms->description = $request->description;
        $update_terms->save();
        if ($update_terms) {
            return response()->json([
                'status' => 'success',
                'data' => $update_terms
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 402);
        }
    }

    public function edit_about($id)
    {
        $about = About::where('id', $id)->orderBy('id', 'desc')->first();
        if ($about) {
            return response()->json([
                'status' => 'success',
                'data' => $about
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 200);
        }
    }

    public function update_about(Request $request)
    {
        $update_about = About::find($request->id);
        $update_about->description = $request->description;
        $update_about->save();
        if ($update_about) {
            return response()->json([
                'status' => 'success',
                'data' => $update_about
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 402);
        }
    }
}
