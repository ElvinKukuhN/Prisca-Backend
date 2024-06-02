<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //

    public function create(Request $request)
    {

        $this->validate($request, [
            'purchase_order_id' => 'required',
            'user_id' => 'required',
            'code' => 'required',
            'status' => 'required'
        ]);
    }

}
