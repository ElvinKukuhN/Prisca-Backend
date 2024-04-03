<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departemen;

class DepartemenController extends Controller
{

    public function departemenIndex()
    {
        $departemen = Departemen::all();

        return response()->json([
            'message' => 'Success',
            'data' => $departemen
        ], 200);
    }

    public function departemenShow($code)
    {
        $departemen = Departemen::where('code', $code)->first();

        if ($departemen) {
            return response()->json([
                'message' => 'Success',
                'data' => [
                    'code' => $departemen->code,
                    'name' => $departemen->name
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => 'Departemen not found'
            ], 404);
        }
    }

    public function departemenCreate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $departemenCode = rand(100, 999);

        $departemen = Departemen::create([
            'code' => $departemenCode,
            'divisi_code' => $request->divisi_code,
            'name' => $request->name
        ]);

        if ($departemen) {
            return response()->json([
                'message' => 'Departemen created successfully',
                'data' => $departemen
            ], 201);
        } else {
            return response()->json([
                'message' => 'Failed to create departemen'
            ], 400);
        }
    }
}
