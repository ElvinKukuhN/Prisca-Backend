<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etalase;
use Illuminate\Http\Request;

class EtalaseController extends Controller
{
    public function index()
    {
        $etalase = Etalase::all();

        return response()->json([
            'success' => true,
            'etalase' => $etalase
        ]);

    }

    public function show($id)
    {
        $etalase = Etalase::find($id);

        if (!$etalase) {
            return response()->json([
                'success' => false,
                'message' => 'Etalase not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'etalase' => $etalase
        ]);
    }

    public function store(Request $request)
    {
        $etalase = new Etalase();
        $etalase->name = $request->name;
        $etalase->save();

        return response()->json([
            'success' => true,
            'etalase' => $etalase
        ]);
    }

    public function update(Request $request, $id)
    {
        $etalase = Etalase::find($id);

        if (!$etalase) {
            return response()->json([
                'success' => false,
                'message' => 'Etalase not found'
            ], 400);
        }

        $etalase->name = $request->name;
        $etalase->save();

        return response()->json([
            'success' => true,
            'etalase' => $etalase
        ]);
    }

    public function destroy($id)
    {
        $etalase = Etalase::find($id);

        if (!$etalase) {
            return response()->json([
                'success' => false,
                'message' => 'Etalase not found'
            ], 400);
        }

        $etalase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etalase deleted successfully'
        ]);
    }
}
