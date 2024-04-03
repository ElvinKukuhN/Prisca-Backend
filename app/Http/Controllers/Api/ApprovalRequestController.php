<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Models\PurchaseRequest;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;

class ApprovalRequestController extends Controller
{
    public function approvalRequestCreate(Request $request)
    {
        if ($request->has('approvalRequest') && is_array($request->approvalRequest) && count($request->approvalRequest) > 0) {
            $approvalRequestData = [];
            $sequence = 0; // Mulai dari 0
            foreach ($request->approvalRequest as $approvalRequest) {
                $approvalRequestModel = new ApprovalRequest([
                    'user_id' => $approvalRequest['user_id'],
                    'doc_code' => $approvalRequest['doc_code'],
                    'sequence' => $sequence,
                    'approval_status' => $approvalRequest['approval_status'],
                    'doc_type' => "pr",
                    'last_activity' => now()
                ]);
                $approvalRequestModel->save();
                $approvalRequestData[] = $approvalRequestModel->toArray();
                $sequence++;
            }

            return response()->json([
                'message' => 'Approval Request created successfully',
                'approvalRequest' => $approvalRequestData
            ], 201);
        }
    }

    public function approvalRequestGet($code)
    {
        // Cari permintaan persetujuan berdasarkan doc_code
        $approvalRequests = ApprovalRequest::where('doc_code', $code)->where('doc_type', 'pr')
            ->orderBy('sequence', 'asc')
            ->get();

        // Periksa apakah ada hasil
        if ($approvalRequests->isEmpty()) {
            return response()->json([
                'message' => 'No approval requests found for the given doc_code'
            ], 404);
        }

        // Inisialisasi array untuk menyimpan data permintaan persetujuan
        $approvalRequestsData = [];

        // Loop melalui setiap permintaan persetujuan
        foreach ($approvalRequests as $approvalRequest) {
            // Tambahkan data permintaan persetujuan ke dalam array
            $approvalRequestsData[] = [
                'id' => $approvalRequest->id,
                'user_id' => $approvalRequest->user_id,
                'user_name' => $approvalRequest->user->name, // Ambil nama pengguna dari relasi 'user'
                'doc_code' => $approvalRequest->doc_code,
                'sequence' => $approvalRequest->sequence,
                'approval_status' => $approvalRequest->approval_status,
                'last_activity' => $approvalRequest->last_activity
            ];
        }

        // Kembalikan respons JSON dengan data permintaan persetujuan
        return response()->json([
            'message' => 'Approval requests found for the given doc_code',
            'approvalRequests' => $approvalRequestsData
        ], 200);
    }

    public function approvalRequestGetByUserId()
    {
        $userId = auth()->id();
        // Cari permintaan persetujuan berdasarkan user_id
        $approvalRequests = ApprovalRequest::where('user_id', $userId)
            ->where('doc_type', 'pr')
            ->where('approval_status', 'pending')
            ->orderBy('sequence', 'asc')
            ->get();

        // Periksa apakah ada hasil
        if ($approvalRequests->isEmpty()) {
            return response()->json([
                'message' => 'No approval requests found for the given user_id'
            ], 200);
        }

        // Inisialisasi array untuk menyimpan data permintaan persetujuan
        $approvalRequestsData = [];

        // Loop melalui setiap permintaan persetujuan
        foreach ($approvalRequests as $approvalRequest) {
            // Tambahkan data permintaan persetujuan ke dalam array
            $approvalRequestsData[] = [
                'id' => $approvalRequest->id,
                'user_id' => $approvalRequest->user_id,
                'user_name' => $approvalRequest->user->name, // Ambil nama pengguna dari relasi 'user'
                'doc_code' => $approvalRequest->doc_code,
                'sequence' => $approvalRequest->sequence,
                'approval_status' => $approvalRequest->approval_status,
                'last_activity' => $approvalRequest->last_activity
            ];
        }

        // Kembalikan respons JSON dengan data permintaan persetujuan
        return response()->json([
            'message' => 'Approval requests found for the given user_id',
            'approvalRequests' => $approvalRequestsData
        ], 200);
    }

    public function approvalRequestAccept($code)
    {
        // Ambil parameter status dari request
        $status = 'approved';

        // Pastikan parameter status tersedia dalam request
        if ($status !== null) {
            // Ambil semua permintaan persetujuan untuk doc_code yang diberikan, diurutkan berdasarkan sequence secara menaik
            $approvalRequests = ApprovalRequest::where('doc_code', $code)
                ->where('approval_status', 'pending') // Hanya ambil yang memiliki status pending
                ->orderBy('sequence', 'asc') // Urutkan berdasarkan sequence secara ascending
                ->get();

            // Periksa apakah ada hasil
            if ($approvalRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No pending approval requests found for the given doc_code'
                ], 200);
            }

            // Inisialisasi array untuk menyimpan informasi pengguna yang mengubah statusnya
            $changedByUsers = [];


            // Inisialisasi variabel untuk menandai apakah ada pengguna dengan urutan lebih rendah yang belum menyetujui
            $lowerSequenceUserNotApproved = false;

            if ($approvalRequests) {
                // Loop melalui setiap permintaan persetujuan
                foreach ($approvalRequests as $approvalRequest) {

                    // Jika ada pengguna dengan urutan lebih rendah yang belum menyetujui, set variabel $lowerSequenceUserNotApproved menjadi true
                    if ($lowerSequenceUserNotApproved) {
                        continue;
                    }

                    if ($approvalRequest->user_id != auth()->id() || $approvalRequest->approval_status != 'pending') {
                        // Jika ada pengguna dengan urutan lebih rendah yang belum menyetujui, set variabel $lowerSequenceUserNotApproved menjadi true
                        if ($approvalRequest->user_id != auth()->id()) {
                            $lowerSequenceUserNotApproved = true;
                            return response()->json([
                                'message' => 'Cannot ' . $status . ' request. Lower sequence user has not approved yet.'
                            ], 403);
                        }
                        continue;
                    } else {
                        // Simpan informasi pengguna yang mengubah statusnya
                        $changedByUsers[] = [
                            'name' => $approvalRequest->user->name,
                            'status' => $status
                        ];

                        // Lakukan approval atau reject sesuai inputan status dari request
                        $approvalRequest->approval_status = $status;
                        $approvalRequest->last_activity = now(); // Update last_activity
                        $approvalRequest->save();
                        // Periksa apakah masih ada permintaan persetujuan yang tertunda setelah iterasi selesai
                        $remainingRequests = ApprovalRequest::where('doc_code', $code)
                            ->where('approval_status', 'pending')
                            ->exists();

                        // Jika tidak ada permintaan persetujuan yang tertunda lagi
                        if (!$remainingRequests) {
                            // Ubah status PurchaseRequest menjadi 'approved' juga
                            $purchaseRequest = PurchaseRequest::where('code', $code)->first();
                            if ($purchaseRequest) {
                                $purchaseRequest->status = 'approved';
                                $purchaseRequest->updated_at = now(); // Update last_activity
                                $purchaseRequest->save();
                            }
                        }

                        // Persiapan pesan respons
                        $message = 'Approval request ' . $status . ' successfully';
                        if (!empty($changedByUsers)) {
                            $message .= ' by the following users:';
                            foreach ($changedByUsers as $user) {
                                $message .= ' User ' . $user['name'] . ' (' . $user['status'] . '),';
                            }
                            $message = rtrim($message, ','); // Menghapus koma terakhir
                        }

                        return response()->json([
                            'message' => $message
                        ], 200);
                    }
                }
            }
        } else {
            // Jika parameter status tidak tersedia dalam request, kembalikan respons kesalahan
            return response()->json([
                'message' => 'Parameter status is missing'
            ], 400);
        }
    }

    public function approvalRequestReject($code)
    {
        // Ambil parameter status dari request
        $status = 'rejected';

        if ($status != null) {
            // Ambil semua permintaan persetujuan untuk doc_code yang diberikan, diurutkan berdasarkan sequence secara menaik
            $approvalRequests = ApprovalRequest::where('doc_code', $code)
                ->where('approval_status', 'pending') // Hanya ambil yang memiliki status pending
                ->orderBy('sequence', 'asc') // Urutkan berdasarkan sequence secara ascending
                ->get();

            // Periksa apakah ada hasil
            if ($approvalRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No pending approval requests found for the given doc_code'
                ], 404);
            }

            try {
                // Lakukan penolakan pada setiap permintaan persetujuan yang masih pending
                foreach ($approvalRequests as $approvalRequest) {
                    // Lakukan penolakan pada permintaan persetujuan
                    $approvalRequest->approval_status = $status;
                    $approvalRequest->save();
                }

                // Ubah status PurchaseRequest menjadi 'rejected'
                $purchaseRequest = PurchaseRequest::where('code', $code)->first();
                if ($purchaseRequest) {
                    $purchaseRequest->status = $status;
                    $purchaseRequest->save();
                }

                return response()->json([
                    'message' => 'All pending approval requests have been rejected and PurchaseRequest status has been updated to rejected'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to reject all pending approval requests' . $e->getMessage()
                ], 500);
            }
        } else {
            // Jika parameter status tidak tersedia dalam request, kembalikan respons kesalahan
            return response()->json([
                'message' => 'Parameter status is missing'
            ], 400);
        }
    }

    public function approvalOrderCreate(Request $request)
    {
        if ($request->has('approvalOrder') && is_array($request->approvalOrder) && count($request->approvalOrder) > 0) {
            $approvalOrderData = [];
            $sequence = 0; // Mulai dari 0
            foreach ($request->approvalOrder as $approvalOrder) {
                $approvalOrderModel = new ApprovalRequest([
                    'user_id' => $approvalOrder['user_id'],
                    'doc_code' => $approvalOrder['doc_code'],
                    'sequence' => $sequence,
                    'approval_status' => $approvalOrder['approval_status'],
                    'doc_type' => "po",
                    'last_activity' => now()
                ]);
                $approvalOrderModel->save();
                $approvalOrderData[] = $approvalOrderModel->toArray();
                $sequence++;
            }

            return response()->json([
                'message' => 'Approval Order created successfully',
                'approvalOrder' => $approvalOrderData
            ], 201);
        }
    }

    public function approvalOrderGet($code)
    {
        // Cari permintaan persetujuan berdasarkan doc_code
        $approvalOrders = ApprovalRequest::where('doc_code', $code)->where('doc_type', 'po')
            ->orderBy('sequence', 'asc')
            ->get();

        // Periksa apakah ada hasil
        if ($approvalOrders->isEmpty()) {
            return response()->json([
                'message' => 'No approval orders found for the given doc_code'
            ], 404);
        }

        // Inisialisasi array untuk menyimpan data permintaan persetujuan
        $approvalOrdersData = [];

        // Loop melalui setiap permintaan persetujuan
        foreach ($approvalOrders as $approvalOrder) {
            // Tambahkan data permintaan persetujuan ke dalam array
            $approvalOrdersData[] = [
                'id' => $approvalOrder->id,
                'user_id' => $approvalOrder->user_id,
                'user_name' => $approvalOrder->user->name, // Ambil nama pengguna dari relasi 'user'
                'doc_code' => $approvalOrder->doc_code,
                'sequence' => $approvalOrder->sequence,
                'approval_status' => $approvalOrder->approval_status,
                'last_activity' => $approvalOrder->last_activity
            ];
        }

        // Kembalikan respons JSON dengan data permintaan persetujuan
        return response()->json([
            'message' => 'Approval orders found for the given doc_code',
            'approvalOrders' => $approvalOrdersData
        ], 200);
    }

    public function approvalOrderGetByUserId()
    {
        $userId = auth()->id();
        // Cari permintaan persetujuan berdasarkan user_id
        $approvalOrders = ApprovalRequest::where('user_id', $userId)
            ->where('doc_type', 'po')
            ->where('approval_status', 'pending')
            ->orderBy('sequence', 'asc')
            ->get();

        // Periksa apakah ada hasil
        if ($approvalOrders->isEmpty()) {
            return response()->json([
                'message' => 'No approval orders found for the given user_id'
            ], 200);
        }

        // Inisialisasi array untuk menyimpan data permintaan persetujuan
        $approvalOrdersData = [];

        // Loop melalui setiap permintaan persetujuan
        foreach ($approvalOrders as $approvalOrder) {
            // Tambahkan data permintaan persetujuan ke dalam array
            $approvalOrdersData[] = [
                'id' => $approvalOrder->id,
                'user_id' => $approvalOrder->user_id,
                'user_name' => $approvalOrder->user->name, // Ambil nama pengguna dari relasi 'user'
                'doc_code' => $approvalOrder->doc_code,
                'sequence' => $approvalOrder->sequence,
                'approval_status' => $approvalOrder->approval_status,
                'last_activity' => $approvalOrder->last_activity
            ];
        }

        // Kembalikan respons JSON dengan data permintaan persetujuan
        return response()->json([
            'message' => 'Approval orders found for the given user_id',
            'approvalOrders' => $approvalOrdersData
        ], 200);
    }


    public function approvalOrderAccept($code)
    {
        // Ambil parameter status dari request
        $status = 'approved';

        // Pastikan parameter status tersedia dalam request
        if ($status !== null) {
            // Ambil semua permintaan persetujuan untuk doc_code yang diberikan, diurutkan berdasarkan sequence secara menaik
            $approvalOrders = ApprovalRequest::where('doc_code', $code)
                ->where('approval_status', 'pending') // Hanya ambil yang memiliki status pending
                ->orderBy('sequence', 'asc') // Urutkan berdasarkan sequence secara ascending
                ->get();

            // Periksa apakah ada hasil
            if ($approvalOrders->isEmpty()) {
                return response()->json([
                    'message' => 'No pending approval requests found for the given doc_code'
                ], 200);
            }

            // Inisialisasi array untuk menyimpan informasi pengguna yang mengubah statusnya
            $changedByUsers = [];


            // Inisialisasi variabel untuk menandai apakah ada pengguna dengan urutan lebih rendah yang belum menyetujui
            $lowerSequenceUserNotApproved = false;

            if ($approvalOrders) {
                // Loop melalui setiap permintaan persetujuan
                foreach ($approvalOrders as $approvalOrder) {

                    // Jika ada pengguna dengan urutan lebih rendah yang belum menyetujui, set variabel $lowerSequenceUserNotApproved menjadi true
                    if ($lowerSequenceUserNotApproved) {
                        continue;
                    }

                    if ($approvalOrder->user_id != auth()->id() || $approvalOrder->approval_status != 'pending') {
                        // Jika ada pengguna dengan urutan lebih rendah yang belum menyetujui, set variabel $lowerSequenceUserNotApproved menjadi true
                        if ($approvalOrder->user_id != auth()->id()) {
                            $lowerSequenceUserNotApproved = true;
                            return response()->json([
                                'message' => 'Cannot ' . $status . ' request. Lower sequence user has not approved yet.'
                            ], 403);
                        }
                        continue;
                    } else {
                        // Simpan informasi pengguna yang mengubah statusnya
                        $changedByUsers[] = [
                            'name' => $approvalOrder->user->name,
                            'status' => $status
                        ];

                        // Lakukan approval atau reject sesuai inputan status dari request
                        $approvalOrder->approval_status = $status;
                        $approvalOrder->last_activity = now(); // Update last_activity
                        $approvalOrder->save();
                        // Periksa apakah masih ada permintaan persetujuan yang tertunda setelah iterasi selesai
                        $remainingRequests = ApprovalRequest::where('doc_code', $code)
                            ->where('approval_status', 'pending')
                            ->exists();

                        // Jika tidak ada permintaan persetujuan yang tertunda lagi
                        if (!$remainingRequests) {
                            // Ubah status PurchaseRequest menjadi 'approved' juga
                            $purchaseRequest = PurchaseOrder::where('code', $code)->first();
                            if ($purchaseRequest) {
                                $purchaseRequest->status = 'approved';
                                $purchaseRequest->updated_at = now(); // Update last_activity
                                $purchaseRequest->save();
                            }
                        }

                        // Persiapan pesan respons
                        $message = 'Order request ' . $status . ' successfully';
                        if (!empty($changedByUsers)) {
                            $message .= ' by the following users:';
                            foreach ($changedByUsers as $user) {
                                $message .= ' User ' . $user['name'] . ' (' . $user['status'] . '),';
                            }
                            $message = rtrim($message, ','); // Menghapus koma terakhir
                        }

                        return response()->json([
                            'message' => $message
                        ], 200);
                    }
                }
            }
        } else {
            // Jika parameter status tidak tersedia dalam request, kembalikan respons kesalahan
            return response()->json([
                'message' => 'Parameter status is missing'
            ], 400);
        }
    }

    public function approvalOrderReject($code)
    {
        // Ambil parameter status dari request
        $status = 'rejected';

        if ($status != null) {
            // Ambil semua permintaan persetujuan untuk doc_code yang diberikan, diurutkan berdasarkan sequence secara menaik
            $approvalOrders = ApprovalRequest::where('doc_code', $code)
                ->where('approval_status', 'pending') // Hanya ambil yang memiliki status pending
                ->orderBy('sequence', 'asc') // Urutkan berdasarkan sequence secara ascending
                ->get();

            // Periksa apakah ada hasil
            if ($approvalOrders->isEmpty()) {
                return response()->json([
                    'message' => 'No pending approval requests found for the given doc_code'
                ], 404);
            }

            try {
                // Lakukan penolakan pada setiap permintaan persetujuan yang masih pending
                foreach ($approvalOrders as $approvalOrder) {
                    // Lakukan penolakan pada permintaan persetujuan
                    $approvalOrder->approval_status = $status;
                    $approvalOrder->save();
                }

                // Ubah status PurchaseRequest menjadi 'rejected'
                $purchaseRequest = PurchaseOrder::where('code', $code)->first();
                if ($purchaseRequest) {
                    $purchaseRequest->status = $status;
                    $purchaseRequest->save();
                }

                return response()->json([
                    'message' => 'All pending approval requests have been rejected and PurchaseOrder status has been updated to rejected'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to reject all pending approval requests' . $e->getMessage()
                ], 500);
            }
        } else {
            // Jika parameter status tidak tersedia dalam request, kembalikan respons kesalahan
            return response()->json([
                'message' => 'Parameter status is missing'
            ], 400);
        }
    }

}
