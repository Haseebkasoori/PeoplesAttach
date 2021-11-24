<?php

namespace App\Http\Controllers;
use App\Mail\testMail;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;

class MailController extends Controller
{
    public function sendMail(){
        $details = [
            'title' => "EMail Sending",
            'body' => "Body"
        ];
        Mail::to("sumailaabbas123@gmail.com")->send(new testMail($details));
        return "Email sent successfully!";
    }
}
