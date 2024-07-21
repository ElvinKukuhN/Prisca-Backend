<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyAddress;
use Illuminate\Support\Facades\Validator;

class CompanyAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $userCompaniesId = auth()->user()->userCompanies->first()->id;
            $address = CompanyAddress::where('user_companies_id', $userCompaniesId)->get();

            if (!$address) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json($address);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Address failed to get with error', $th->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $address = CompanyAddress::create([
                'user_companies_id' => auth()->user()->userCompanies->first()->id,
                'address' => $request->address,
            ]);

            if ($address) {
                return response()->json([
                    'message' => 'Address created successfully',
                    'address' => $address
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Address failed to create with error', $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $address = CompanyAddress::where('id', $id)->first();

            $address->update([
                'address' => $request->address,
            ]);

            if ($address) {
                return response()->json([
                    'message' => 'Address updated successfully',
                    'address' => $address->address
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Address failed to update with error', $th->getMessage()
            ], 400);
        }
    }


    public function destroy(string $id)
    {
        try {
            $address = CompanyAddress::where('id', $id)->first();

            if ($address) {
                $address->delete();
                return response()->json([
                    'message' => 'Address deleted successfully',
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Address failed to delete with error', $th->getMessage()
            ], 400);
        }
    }
}
