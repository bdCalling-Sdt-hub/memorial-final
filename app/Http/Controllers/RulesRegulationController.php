<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Privacy;
use App\Models\TermsConditions;
use Illuminate\Http\Request;

class RulesRegulationController extends Controller
{
    //
    public function aboutUs()
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
            ], 200);
        }
    }

    public function termsCondition()
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
            ], 200);
        }
    }

    public function privacyPolicy()
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
            ], 200);
        }
    }
}
