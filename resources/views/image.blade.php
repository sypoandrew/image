@extends('admin::layouts.main')

@section('content')

    <div class="flex pb-2 mb-4">
        <h2 class="flex-1 m-0 p-0">
		<a href="{{ route('admin.modules') }}" class="btn mr-4">&#171; Back</a>
		<span class="flex-1">Image Processing</span>
		</h2>
    </div>
    @include('admin::partials.alerts')
	
	<form action="{{ route('admin.modules.image') }}" method="post" class="flex flex-wrap">
		@csrf
		<div class="card mt-4 w-full">
			<h3>Settings</h3>
			<div class="mt-4 w-full">
			<label for="enabled" class="block">
			<label class="checkbox">
			@if(setting('Image.enabled'))
			<input type="checkbox" id="enabled" name="enabled" checked="checked" value="1">
			@else
			<input type="checkbox" id="enabled" name="enabled" value="1">
			@endif
			<span></span>
			</label>Enabled
			</label>
			</div>
			<div class="mt-4 w-full">
			<label for="enabled" class="block">Image report send to email</label>
			<input type="text" id="image_report_send_to_email" name="image_report_send_to_email" autocomplete="off" required="required" class="w-full " value="{{ setting('Image.image_report_send_to_email') }}">
			</div>
			<div class="mt-4 w-full">
			<label for="enabled" class="block">Image report send from email</label>
			<input type="text" id="image_report_send_from_email" name="image_report_send_from_email" autocomplete="off" required="required" class="w-full " value="{{ setting('Image.image_report_send_from_email') }}">
			</div>
			<div class="mt-4 w-full">
			<label for="enabled" class="block">Image report send from name</label>
			<input type="text" id="image_report_send_from_name" name="image_report_send_from_name" autocomplete="off" required="required" class="w-full " value="{{ setting('Image.image_report_send_from_name') }}">
			</div>
		</div>
		
		<div class="card mt-4 p-4 w-full flex flex-wrap"><button type="submit" class="btn btn-secondary">Save</button> </div>
	</form>
	
	<form action="{{ route('admin.modules.image.add_to_library') }}" method="post" class="flex flex-wrap">
		@csrf
		<div class="card mt-4 w-full">
			<h3>Add images to Product Library</h3>
			<p>TBC</p>
			
		</div>
		
		<div class="card mt-4 p-4 w-full flex flex-wrap"><button type="submit" class="btn btn-secondary">Save</button> </div>
	</form>
	
	<form action="{{ route('admin.modules.image.placeholder_image') }}" method="get" class="flex flex-wrap mb-8">
		@csrf
		
		<div class="card mt-4 w-full">
			<h3>Create Placeholder Images</h3>
			<p><strong>NOTE:</strong> this is run via an automated routine every X minutes. Press the button below to run the routine manually.</p>
			<p><strong>NOTE:</strong> an email will be sent after the routine is complete, with any products still missing images reported on. This is usually due to missing colour, wine type data.</p>
		</div>
		<div class="card mt-4 p-4 w-full flex flex-wrap">
			<button type="submit" class="btn btn-secondary">Process</button>
		</div>
	</form>
	
	<form action="{{ route('admin.modules.image.download_image_report') }}" method="get" class="flex flex-wrap mb-8">
		@csrf
		<input type="hidden" name="filename" value="missing_image_report.csv" />
		<div class="card mt-4 w-full">
			<h3>Report Download - Create Placeholder Images</h3>
			<p>Download report for images that require attention.</p>
		</div>
		<div class="card mt-4 p-4 w-full flex flex-wrap">
			<button type="submit" class="btn btn-secondary">Download Report</button>
		</div>
	</form>
	
	<form action="{{ route('admin.modules.image.replace_default_image') }}" method="get" class="flex flex-wrap mb-8">
		@csrf
		
		<div class="card mt-4 w-full">
			<h3>Replace Placeholder Images With Library Images</h3>
			<p><strong>NOTE:</strong> this is run via an automated routine every X minutes. Press the button below to run the routine manually.</p>
			<p><strong>NOTE:</strong> an email will be sent after the routine is complete.</p>
		</div>
		<div class="card mt-4 p-4 w-full flex flex-wrap">
			<button type="submit" class="btn btn-secondary">Process</button>
		</div>
	</form>
	
	<form action="{{ route('admin.modules.image.download_image_report') }}" method="get" class="flex flex-wrap mb-8">
		@csrf
		<input type="hidden" name="filename" value="replace_default_image_report.csv" />
		<div class="card mt-4 w-full">
			<h3>Report Download - Replace Placeholder Images With Library Images</h3>
			<p>Download report for images that require library image adding.</p>
		</div>
		<div class="card mt-4 p-4 w-full flex flex-wrap">
			<button type="submit" class="btn btn-secondary">Download Report</button>
		</div>
	</form>
	
@endsection
