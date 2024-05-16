<?php

namespace App\Http\Controllers\Api\Addmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
//    public function package()
//    {
//        $pakege = Package::get();
//
//        if ($pakege) {
//            return response()->json([
//                'status' => 'success',
//                'data' => $pakege
//            ]);
//        } else {
//            return response()->json([
//                'status' => false,
//                'data' => []
//            ]);
//        }
//        $package_list = Package::get();
//
//        $formatted_package = $package_list->map(function($package){
//            $features = [];
//            $features[] = ['feature' => $package->word_limit . ' Character Limit'];
//            $features[] = ['feature' => $package->image_limit . ' Image Limit'];
//            // You can add more dynamic features here if needed
//
//            // Merge dynamic features with existing features
//            $package->feature = array_merge(json_decode($package->feature, true), $features);
//
//            return $package;
//        });
//
//        return response()->json([
//            'status' => 'success',
//            'data' => $formatted_package
//        ]);

//    }
    public function package(){
        $package_list = Package::get();

        $formatted_package = $package_list->map(function($package){
            $package->feature = json_decode($package->feature);
            return $package;
        });

        return response()->json([
            'message' => 'success',
            'data' => $formatted_package
        ]);
    }
    public function count_package_subscriber()
    {
        $package = Package::get();
        $totalSubscribers = 0;
        $package_data = [];

        foreach ($package as $pack) {
            $subCount = Subscription::where('package_id', $pack->id)->count();
            $totalSubscribers += $subCount;
            $package_data[] = [
                'package_name' => $pack->package_name,
                'story_count' => $subCount
            ];
        }

        $package_data[] = [
            'package_name' => 'total user',
            'total_subscribers' => $totalSubscribers,
        ];

        return response()->json($package_data);
    }

    public function userList(Request $request)
    {
        $package_id = $request->id;

        if ($package_id == 0) {
            $subscribe_user = Subscription::with('user', 'package')->orderBy('id', 'desc')->paginate(8);
        } else {
            $subscribe_user = Subscription::where('package_id', $package_id)->with('user', 'package')->orderBy('id', 'desc')->paginate(8);
        }

        if ($subscribe_user) {
            return response()->json([
                'status' => 'success',
                // 'total_user' => $total_user,
                'subscribe_packag' => $this->count_package_subscriber(),
                'data' => $subscribe_user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 200);
        }
    }

    public function userDetails($id)
    {
        $user_details = Subscription::where('id', $id)->with('user', 'package')->first();

        if ($user_details) {
            return response()->json([
                'status' => 'success',
                'data' => $user_details,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ], 200);
        }
    }

    public function search_subscriber(Request $request)
    {
        $search = Subscription::with('user', 'package')
            ->whereHas('user', function ($query) use ($request) {
                $query->where('fullName', 'like', '%' . $request->name . '%');
            })
            ->get();

        if ($search) {
            return response()->json([
                'status' => 'success',
                'data' => $search
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }
}
