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
       $add_service->title = $request->title;
       if($request->hasfile('image'))
       {
           $file = $request->file('image');
           $extenstion = $file->getClientOriginalName();
           $filename = time().'.'.$extenstion;
           $file->move('uploads/services/', $filename);
           $add_service->image = 'uploads/services/'.$filename;
       }
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
        $update_service = Service::find($id);
        if (!$update_service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }
    
        // Update title and description
        $update_service->title = $request->title ?? $update_service->title;
        $update_service->description = $request->description ?? $update_service->description;
    
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the previous image if it exists
            if ($update_service->image && file_exists(public_path($update_service->image))) {
                unlink(public_path($update_service->image));
            }
    
            // Upload the new image
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $filename);
            $update_service->image = 'uploads/services/' . $filename;
        }
    
        $update_service->save();
    
        if ($update_service) {
            return response()->json(['status' => 'success', 'data' => $update_service], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
          // Find the service record
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        }

        // Unlink the image if it exists
        if ($service->image && file_exists(public_path($service->image))) {
            unlink(public_path($service->image));
        }

        // Delete the service record
        $service_delet = $service->delete();

        if ($service_delet) {
            return response()->json(['status' => 'success', 'message' => 'Service deleted successfully'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Internal server error'], 500);
        }
    }
}
