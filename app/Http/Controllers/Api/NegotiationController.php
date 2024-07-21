<?php

namespace App\Http\Controllers\Api;

use App\Models\Negotiation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RequestForQoutation;
use Illuminate\Support\Facades\Validator;

class NegotiationController extends Controller
{
    //
    public function create(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'request_for_qoutation_id' => 'required',
                'description' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $negotiation = Negotiation::create([
                'user_id' => auth()->user()->id,
                'request_for_qoutation_id' => $request->request_for_qoutation_id,
                'description' => $request->description,
            ]);

            if ($negotiation) {
                return response()->json([
                    'message' => 'Negotiation created successfully',
                    'negotiation' => $negotiation
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Negotiation failed to create with error', $th->getMessage()
            ], 400);
        }
    }

    public function showByRFQ2($id)
    {

        try {
            $negotiation = Negotiation::where('request_for_qoutation_id', $id)->get();

            if (!$negotiation) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $negotiationMap = [
                'request_for_quotation_id' => $negotiation->request_for_quotation_id,
                'negotiation' => [
                    'id' => $negotiation->id,
                    'description' => $negotiation->description,
                    'created_at' => $negotiation->created_at,
                    'user' => [
                        'id' => $negotiation->user->id,
                        'name' => $negotiation->user->name,
                        'role' => $negotiation->user->role->name
                    ],
                ]
            ];

            if ($negotiation) {
                return response()->json([
                    'message' => 'Negotiation created successfully',
                    'negotiation' => $negotiationMap
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Negotiation failed to get with error', $th->getMessage()
            ], 400);
        }
    }

    public function showByRFQ($id)
    {

        try {
            $negotiation = RequestForQoutation::where('id', $id)->first();

            if (!$negotiation) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $lineItems = $negotiation->negotiation;

            $historyData = [];
            foreach ($lineItems as $lineItem) {
                if ($lineItem->request_for_qoutation_id === $id) {
                    $historyData[] = [
                        'id' => $lineItem->id,
                        'description' => $lineItem->description,
                        'created_at' => $lineItem->created_at,
                        'user' => [
                            'id' => $lineItem->user->id,
                            'name' => $lineItem->user->name,
                            'role' => $lineItem->user->role->name
                        ],
                    ];
                }
            }

            $createdAt = array_column($historyData, 'created_at');

            array_multisort($createdAt, SORT_ASC, $historyData);

            $negotiationMap = [
                'request_for_quotation_id' => $negotiation->id,
                'negotiation' => $historyData
            ];

            if ($negotiation) {
                return response()->json([
                    'message' => 'Negotiation created successfully',
                    'negotiation' => $negotiationMap
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Negotiation failed to get with error', $th->getMessage()
            ], 400);
        }
    }
}
