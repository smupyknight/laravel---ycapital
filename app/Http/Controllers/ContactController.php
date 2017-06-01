<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use Mail;

class ContactController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// $this->middleware('auth');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return view('pages.contact')
		     ->with('title','Contact Us');
	}

	/**
	 * Handling of contact post data.
	 *
	 * @return post data
	 */
	public function postIndex(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'enquiry_type' => 'required',
			'name' => 'required',
			'email' => 'required|email|max:255',
			'mobile' => 'required|numeric',
			'message' => 'required',
			'answer' => 'required|in:5',
		]);

		if ($validator->fails()) {
			return redirect('contact-us')
			     ->withErrors($validator)
			     ->withInput();
		}

		$data = $request->all();

		Mail::send('email.inquiry',array('data' => $data),function($message) use ($data){
			$message->subject('Alares Inquiry from '.$data['name']);
			$message->sender('info@' . env('MAIL_FROM_DOMAIN'));
			$message->from($data['email']);
			$message->to('info@' . env('MAIL_FROM_DOMAIN'), 'ALARES');
		});

		Mail::send('email.thank-you',array('data' => $data),function($message) use ($data){
			$message->subject('Thank You for your Inquiry!');
			$message->from('info@' . env('MAIL_FROM_DOMAIN'), 'ALARES');
			$message->to($data['email']);
		});

		return view('pages.contact')
		     ->with('title','Contact Us')
		     ->with('message','Inquiry Succesfully Sent');
	}

}
