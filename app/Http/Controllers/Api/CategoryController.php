<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    //
    public function index()
    {
        $category = Category::all();

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }
}
