<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index() 
    {
        return response->json("OK",200);
    }

    public function store(Request $request) 
    {


    }

    public function balance(Request $request) 
    {

    }
}
