<?php

namespace Sypo\Image\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Sypo\Image\Models\EmailNotification;


class ImageReportMail extends Mailable
{
    use Queueable, SerializesModels;
	
    public $products;
    public $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($products, $code)
    {
        $this->products = $products;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$notify = new EmailNotification;
		$user = \Auth::user();
		if($user){
			$notify->admin_id = $user->id;
		}
		$notify->code = $this->code;
		$notify->save();
		
		$this
		->subject('VinQuinn '.str_replace('_', ' ', $this->code))
		->to(setting('Image.image_report_send_to_email'))
		->from(setting('Image.image_report_send_from_email'), setting('Image.image_report_send_from_name'))
		->markdown('image::emails.'.$this->code);
		
		return $this;
    }
}
