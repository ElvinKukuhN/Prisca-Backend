<?php

namespace App\Http\Controllers\Api;

use App\Models\Divisi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DivisiController extends Controller
{

    public function divisiIndex()
    {

        $company_code = auth()->user()->userCompanies->first()->company->code;
        $divisi = Divisi::where('company_code', $company_code)->get();

        if ($divisi) {
            return response()->json([
                'message' => 'Success',
                'divisi' => $divisi
            ], 200);
        } else {
            return response()->json([
                'message' => 'Divisi not found'
            ], 404);
        }
    }

    public function divisiShow($code)
    {
        $divisi = Divisi::where('code', $code)->first();

        if ($divisi) {
            return response()->json([
                'message' => 'Success',
                'divisi' => [
                    'code' => $divisi->code,
                    'name' => $divisi->name
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => 'Divisi not found'
            ], 404);
        }
    }

    public function divisiStore(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $company_code = auth()->user()->userCompanies->first()->company_code;

        $divisiCode = rand(100, 999);

        $divisi = Divisi::create([
            'code' => $divisiCode,
            'company_code' => $company_code,
            'name' => $request->name
        ]);

        if ($divisi) {
            return response()->json([
                'message' => 'Divisi created successfully',
                'data' => $divisi
            ], 201);
        } else {
            return response()->json([
                'message' => 'Failed to create divisi'
            ], 400);
        }
    }
}
