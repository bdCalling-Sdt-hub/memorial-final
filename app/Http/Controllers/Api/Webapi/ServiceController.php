<?php

namespace App\Http\Controllers\Api\Webapi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $service = Service::all();
        if($service){
            return response()->json(['status'=>'success','data'=>$service],200);
        }else{
            return response()->json(['status'=>false,'message'=>'Record not found'],200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
       $add_service = new Service();
       $add_service->description = $request->description;
       $add_service->save();
       if($add_service){
        return response()->json(['status'=>'success','data'=>$add_service],200);
        }else{
            return response()->json(['status'=>false,'message'=>'Internal server error'],402);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::where('id',$id)->first();
        if($service){
            return response()->json(['status'=>'success','data'=>$service],200);
        }else{
            return response()->json(['status'=>false,'message'=>'Record not found'],402);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $update_service =  Service::find($request->id);
        $update_service->description = $request->description ?? $update_service->description;
        $update_service->save();
        if($update_service){
         return response()->json(['status'=>'success','data'=>$update_service],200);
         }else{
             return response()->json(['status'=>false,'message'=>'Internal server error'],402);
         }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service_delet = Service::where('id', $id)->delete();
        if($service_delet){
            return response()->json(['status'=>'success','message'=>'Service delete successfully'],200);
        }else{
            return response()->json(['status'=>false,'message'=>'Record not found'],402);
        }
    }
}
