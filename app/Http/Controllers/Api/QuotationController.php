<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\RequestForQoutation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class QuotationController extends Controller
{
    public function quotationRequest(Request $request)
    {
        $this->validate($request, [
            'purchase_request_id' => 'required',
        ]);

        $purchase_request = PurchaseRequest::where('user_id', auth()->user()->id)
            ->where('status', 'approved')->first();

        $vendor_id = $purchase_request->lineItems->first()->product->user_id;

        $quotation = RequestForQoutation::create([
            'purchase_request_id' => $request->purchase_request_id,
            'user_id' => $vendor_id,
            'code' => "QUO-" . date("Ymd") . rand(100, 999),
            'company_address' => $request->company_address
        ]);

        if ($quotation) {
            return response()->json([
                'message' => 'quotation created successfully',
                'data' => $quotation
            ], 201);
        } else {
            return response()->json([
                'message' => 'Quotation failed to create'
            ], 400);
        }
    }

    public function quotationShow()
    {
        $quotations = RequestForQoutation::where('user_id', auth()->user()->id)->get();

        // Inisialisasi array untuk menampung data semua line items
        $lineItemsData = [];

        // Iterasi melalui setiap permintaan penawaran harga
        foreach ($quotations as $quotation) {

            // Tambahkan data quotation ke dalam array
            $quotationData = [
                'id' => $quotation->id,
                'code' => $quotation->code,
                'company_name' => $quotation->purchaseRequest->user->userCompanies->first()->company->name ?? null,
                'created_at' => $quotation->created_at->format('d-m-Y'),
            ];

            // Tambahkan data quotation ke dalam array
            $lineItemsData[] = $quotationData;
        }

        return response()->json([
            'message' => 'Success',
            'quotation' => $lineItemsData
        ], 200);
    }

    public function quotationShowById($id)
    {
        $quotation = RequestForQoutation::findOrFail($id);

        if (!$quotation) {
            return response()->json([
                'message' => 'Quotation Not Found !!!'
            ], 404);
        }

        // Inisialisasi array untuk menampung data semua line items
        $lineItemsData = [];

        // Iterasi melalui setiap line item yang terkait dengan purchase request pada permintaan penawaran harga saat ini
        foreach ($quotation->purchaseRequest->lineItems as $lineItem) {
            // Periksa apakah produk pada line item memiliki user yang sama dengan pengguna yang sedang terautentikasi
            if ($lineItem->product->user_id === auth()->user()->id) {
                // Tambahkan data line item ke dalam array jika belum ada
                $exists = false;
                foreach ($lineItemsData as $data) {
                    if ($data['product_id'] === $lineItem->product_id && $data['product_name'] === $lineItem->name && $data['quantity'] === $lineItem->quantity && $data['product_price'] === $lineItem->price) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $product_price = $lineItem->product->commercialInfo->price;
                    $lineItemsData[] = [
                        'product_id' => $lineItem->product_id,
                        'product_name' => $lineItem->name,
                        'quantity' => $lineItem->quantity,
                        'product_price' => $product_price,
                        'amount' => $lineItem->quantity * $product_price
                    ];
                }
            }
        }

        $beratTotal = $quotation->purchaseRequest->lineItems->sum(function ($lineItem) {
            return $lineItem->quantity * $lineItem->product->commercialInfo->berat;
        });


        $hargaOngkir = $beratTotal * 10000;
        return response()->json([
            'message' => 'Success',
            'quotation' => [
                'id' => $quotation->id,
                'code' => $quotation->code,
                'company_name' => $quotation->purchaseRequest->user->userCompanies->first()->company->name ?? null,
                'created_at' => $quotation->created_at->format('d-m-Y'),
                'total_price' => $quotation->purchaseRequest->lineItems->sum(function ($lineItem) {
                    return $lineItem->quantity * $lineItem->product->commercialInfo->price;
                }),
                'harga_ongkir' => $hargaOngkir,
                'line_items' => $lineItemsData
            ]
        ], 200);
    }

    public function quotationFromVendor(Request $request, $id)
    {

        $quotation = RequestForQoutation::findOrFail($id);


        $validator = Validator::make($request->all(), [
            'harga_ongkir' => 'required',
            'quotationsItems.*.product_id' => 'required',
            'quotationItems.*.name' => 'required',
            'quotationItems.*.quantity' => 'required',
            'quotationItems.*.price' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('quotationItems') && is_array($request->quotationItems) && count($request->quotationItems) > 0) {
            $quotationItems = [];
            foreach ($request->quotationItems as $quotationItem) {
                $quotationItemModel = new Quotation([
                    'request_for_qoutation_id' => $id,
                    'product_id' => $quotationItem['product_id'],
                    'name' => $quotationItem['name'],
                    'quantity' => $quotationItem['quantity'],
                    'price' => $quotationItem['price'],
                    'amount' => $quotationItem['quantity'] * $quotationItem['price'],
                ]);
                $quotationItemModel->save();
                $quotationItems[] = $quotationItemModel;
            }

            // Membuat array untuk menyimpan informasi kutipan
            $quotationData = [];
            foreach ($quotationItems as $quotationItem) {
                $quotationData[] = [
                    'id' => $quotationItem->id,
                    'product_id' => $quotationItem->product_id,
                    'name' => $quotationItem->name,
                    'quantity' => $quotationItem->quantity,
                    'price' => $quotationItem->price,
                    'amount' => $quotationItem->amount,
                ];
            }

            $quotation->harga_ongkir = $request->harga_ongkir;
            $quotation->save();

            return response()->json([
                'message' => 'create quotation successfully',
                'quotation' => [
                    'id' => $quotationItem->request_for_qoutation_id,
                    'quotation_items' => $quotationData

                ]
            ]);
        } else {
            return response()->json([
                'message' => 'No quotation provided'
            ], 400);
        }
    }

    public function quotationFixPDFSendToBuyer($id)
    {
        // Ambil data yang dibutuhkan dari request atau model, sesuai kebutuhan Anda
        $quotation = RequestForQoutation::find($id);

        $quotationItem = $quotation->quotations;

        $company_address = $quotation->company_address ?? null;
        $company_name = $quotation->purchaseRequest->user->userCompanies->first()->company->name ?? null;

        $vendor_address = auth()->user()->masterVendor->alamat ?? null;
        $vendor_name = auth()->user()->name ?? null;

        // Load view PDF dengan data quotation
        $pdf = PDF::loadView('pdf.quotation', compact('quotation', 'quotationItem', 'company_address', 'company_name', 'vendor_address', 'vendor_name'));

        // Simpan PDF ke dalam direktori lokal
        $pdfPath = public_path('pdf/quotation'); // Direktori untuk menyimpan PDF
        $pdfName = 'quotation_' . $quotation->code . '.pdf';
        $pdf->save($pdfPath . '/' . $pdfName); // Menyimpan PDF ke direktori lokal

        if ($quotation) {
            $quotation->quo_doc = $pdfName;
            $quotation->save();
        }

        // Return response API
        return response()->json([
            'message' => 'Quotation PDF berhasil dibuat dan dikirim ke pembeli.',
            'pdf_url' => asset('pdf/quotation/' . $pdfName) // URL untuk mengakses PDF
        ]);
    }

    public function quotationFixGet($id)
    {
        $quotation = RequestForQoutation::find($id);

        if (!$quotation) {
            return response()->json([
                'message' => 'Quotation Not Found !!!'
            ], 404);
        }

        // Initialize array to hold all line item data
        $quotationData = [];

        foreach ($quotation->quotations as $quotationItem) {
            $exists = false;
            foreach ($quotationData as $data) {
                if (
                    $data['product_id'] === $quotation->product_id &&
                    $data['product_name'] === $quotationItem->name &&
                    $data['quantity'] === $quotationItem->quantity &&
                    $data['product_price'] === $quotationItem->price
                ) {
                    $exists = true;
                    break;
                }
            }

            // Add line item data to array if it doesn't exist
            if (!$exists) {
                $product_price = $quotationItem->price;
                $quotationData[] = [
                    'id' => $quotationItem->id,
                    'product_id' => $quotationItem->product_id,
                    'product_name' => $quotationItem->name,
                    'quantity' => $quotationItem->quantity,
                    'product_price' => $product_price,
                    'amount' => $quotationItem->quantity * $product_price
                ];
            }
        }

        // Calculate total price
        $total_price = $quotation->quotations->sum(function ($quotationItem) {
            return $quotationItem->quantity * $quotationItem->price;
        });

        // Prepare response data
        $response_data = [
            'id' => $quotation->id,
            'code' => $quotation->code,
            'company_name' => $quotation->purchaseRequest->user->userCompanies->first()->company->name ?? null,
            'updated_at' => $quotation->created_at->format('d-m-Y'),
            'total_price' => $total_price,
            'harga_ongkir' => $quotation->harga_ongkir,
            'pdf' => asset('pdf/quotation/' . $quotation->quo_doc) ?? null, // URL for accessing PDF
            'line_items' => $quotationData
        ];

        return response()->json([
            'message' => 'Success',
            'quotation' => $response_data
        ], 200);
    }

    public function quotationFixall()
    {
        $userId = auth()->user()->id;

        $quotations = DB::table('request_for_qoutations')
            ->join('purchase_requests', 'request_for_qoutations.purchase_request_id', '=', 'purchase_requests.id')
            ->where('purchase_requests.user_id', $userId)
            ->select('request_for_qoutations.*')
            ->distinct()
            ->get();

        $lineItemsData = [];

        foreach ($quotations as $quotation) {
            $quotationData = [
                'id' => $quotation->id,
                'user' => $userId,
                'code' => $quotation->code,
                // 'company_name' => $userCompany->name ?? null,
                'quo_doc' => $quotation->quo_doc,
                'created_at' => date('d-m-Y', strtotime($quotation->created_at)),
            ];

            $lineItemsData[] = $quotationData;
        }

        return response()->json([
            'message' => 'Success',
            'quotation' => $lineItemsData,
            'jumlah' => $quotations->count()
        ], 200);
    }
}
