<?php

namespace Sypo\Image\Http\Controllers;

use Illuminate\Http\Request;
use Aero\Admin\Facades\Admin;
use Aero\Admin\Http\Controllers\Controller;
use Spatie\Valuestore\Valuestore;

class ModuleController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Show main settings form
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('image::image', $this->data);
    }
    
	/**
     * Update settings
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
		if($request->isMethod('post')) {
			$validator = \Validator::make($request->all(), [
				'image_report_send_to_email' => 'required|email:rfc,dns',
				'image_report_send_from_email' => 'required|email:rfc,dns',
				'image_report_send_from_name' => 'required',
			]);
			
			if($validator->fails()){
				return redirect()->back()->withErrors($validator->errors()->all());
			}
			
			$valuestore = Valuestore::make(storage_path('app/settings/Image.json'));
			$valuestore->put('enabled', (int) $request->input('enabled'));
			$valuestore->put('image_report_send_to_email', $request->input('image_report_send_to_email'));
			$valuestore->put('image_report_send_from_email', $request->input('image_report_send_from_email'));
			$valuestore->put('image_report_send_from_name', $request->input('image_report_send_from_name'));
			
			
			return redirect()->back()->with('message', 'Settings updated');
		}
		else{
			abort(403);
		}
    }
    
	/**
     * Add image to library
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function add_to_library(Request $request)
    {
		return redirect()->back()->with('message', 'Successfully uploaded image');
		
		$imageName = time().'.'.$request->file->getClientOriginalExtension();
		$imageName = $request->file->getClientOriginalExtension();
        $request->file->move(storage_path('app/image_library'), $imageName);
         
    	return response()->json(['success'=>'You have successfully upload file.']);
    }
    
	/**
     * Manually run the Placeholder image
     *
     * @return void
     */
    public function placeholder_image(Request $request)
    {
    	\Artisan::call('sypo:image:placeholder');
		
		return redirect()->back()->with('message', 'Successfully run the placeholder image routine');
    }
    
	/**
     * Manually run the Replace default image routine
     *
     * @return void
     */
    public function replace_default_image(Request $request)
    {
    	\Artisan::call('sypo:image:update');
		
		return redirect()->back()->with('message', 'Successfully run the replace default image routine');
    }
    
	/**
     * Download the image report file
     *
     * @return void
     */
    public function download_image_report(Request $request)
    {
        return response()->download(storage_path('app/'. $request->input('filename')));
    }
}
