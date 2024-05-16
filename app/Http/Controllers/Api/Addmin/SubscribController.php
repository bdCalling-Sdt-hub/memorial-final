<?php

namespace App\Http\Controllers\Api\Addmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class SubscribController extends Controller
{
    public function edit_subscribe_package($id)
    {
        $subscribe = Package::where('id', $id)->first();
        if ($subscribe) {
            $subscribe['feature'] = json_decode($subscribe['feature'], true);

            if ($subscribe) {
                return response()->json([
                    'status' => 'success',
                    'data' => $subscribe
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'data' => []
                ], 200);
            }
        }
    }

    public function update_package(Request $request)
    {
        $update_package = Package::find($request->id);
        $update_package->package_name = $request->package_name;
        $update_package->amount = $request->amount;
        $update_package->duration = $request->duration;
        $update_package->word_limit = $request->word_limit;
        $update_package->image_limit = $request->image_limit;
        $update_package->feature = $request->feature;
        $update_package->save();
        if ($update_package) {
            return response()->json([
                'status' => 'success',
                'message' => 'Package update success fully',
                'data' => $update_package
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Package update faile',
                'data' => []
            ], 200);
        }
    }

    public function deletePackage($id)
    {
        $delete_package = Package::where('id', $id)->delete();
        if ($delete_package) {
            return response()->json([
                'status' => 'success',
                'message' => 'Package delete success',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Package delete faile',
            ], 402);
        }
    }
}
