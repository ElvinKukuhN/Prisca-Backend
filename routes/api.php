<?php

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\Api\DivisiController;
use App\Http\Controllers\Api\Order_Controller;
use App\Http\Controllers\Api\EtalaseController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\api\ShipmentController;
use App\Http\Controllers\Api\Payment__Controller;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\Shipment_Controller;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\NegotiationController;
use App\Http\Controllers\Api\PengembalianController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\CompanyAddressController;
use App\Http\Controllers\Api\ApprovalRequestController;
use App\Http\Controllers\Api\PurchaseRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('/userRegister', [AuthController::class, 'userRegister'])->name('userRegister');
Route::post('/vendorRegister', [AuthController::class, 'vendorRegister'])->name('vendorRegister');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth:api', 'cors',)->group(function () {
    Route::middleware('checkRole:vendor')->prefix('vendor')->group(function () {
        //Product
        Route::post('/product', [ProductController::class, 'store'])->name('addProduct');
        Route::get('/product', [ProductController::class, 'showByUserId'])->name('productByUserId');
        Route::get('/product/{id}', [ProductController::class, 'show'])->name('product');
        Route::post('/product/{id}', [ProductController::class, 'update'])->name('updateProduct');
        Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('deleteProduct');

        //Category, Group, Etalase, Currency
        Route::get('/category', [CategoryController::class, 'index'])->name('showCategory');
        Route::get('/group', [GroupController::class, 'index'])->name('showGroup');
        Route::get('/etalase', [EtalaseController::class, 'index'])->name('showEtalase');
        Route::get('/currency', [CurrencyController::class, 'index'])->name('showCurrency');
        Route::get('/drop', [ProductController::class, 'drop'])->name('showDrop');

        //Profile
        Route::get('/profile', [AuthController::class, 'vendorGetProfile'])->name('showProfileVendor');
        Route::post('/profile', [AuthController::class, 'vendorUpdateProfile'])->name('updateProfileVendor');

        //Master
        Route::post('/master', [AuthController::class, 'vendorMasterCreate'])->name('master');

        // Quotation
        Route::get('/quotation', [QuotationController::class, 'quotationShow'])->name('showQuotation');
        Route::get('/quotation/{id}', [QuotationController::class, 'quotationShowById'])->name('quotationShowById');
        Route::post('/quotation/{id}', [QuotationController::class, 'quotationFromVendor'])->name('quotationFromVendor');
        Route::get('/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');
        Route::post('/quotation/{id}/pdf', [QuotationController::class, 'quotationFixPDFSendToBuyer'])->name('quotationFixPDFSendToBuyer');

        //Negotiaton
        Route::post('/negotiation', [NegotiationController::class, 'create'])->name('negotiation');
        Route::get('/negotiation/{id}', [NegotiationController::class, 'showByRFQ'])->name('ShowNegotiation');

        //Order
        Route::get('/order', [Order_Controller::class, 'showVendor'])->name('showOrder');
        Route::get('/order/{id}', [Order_Controller::class, 'show'])->name('showOrderById');

        //Shipment
        Route::post('/shipment', [Shipment_Controller::class, 'create'])->name('shipmentCreate');
        Route::get('/shipment', [Shipment_Controller::class, 'index'])->name('shipmentIndex');
        Route::get('/shipment/{id}', [Shipment_Controller::class, 'show'])->name('shipmentShowById');

        //Pengembalian
        Route::get('/pengembalian/{id}', [PengembalianController::class, 'getPengembalianByOrderId'])->name('getPengembalianByOrderId');
        Route::post('/pengembalian/{id}', [PengembalianController::class, 'updateStatusByOrderId'])->name('updateStatusByOrderId');
        Route::post('/pengembalian', [PengembalianController::class, 'replaceReturnedItems'])->name('replaceReturnedItems');

        // Invoice
        Route::post('/invoice', [Payment__Controller::class, 'create'])->name('invoiceCreate');
        Route::get('/invoice/{id}', [Payment__Controller::class, 'show'])->name('invoiceById');
        Route::post('/invoice/{id}/pdf', [Payment__Controller::class, 'sendInvoice'])->name('invoicepdf');
        Route::post('/invoice/{id}/success', [Payment__Controller::class, 'makeSuccess'])->name('makeSuccess');
    });

    Route::middleware('checkRole:company')->prefix('buyer')->group(function () {
        //Profile
        Route::get('/profile', [AuthController::class, 'userGetProfile'])->name('showProfileUser');
        Route::post('/profile', [AuthController::class, 'userUpdateProfile'])->name('updateProfileUser');

        //Addresses
        Route::get('/address', [CompanyAddressController::class, 'index'])->name('showAddress');
        Route::post('/address', [CompanyAddressController::class, 'store'])->name('createAddress');
        Route::put('/address/{id}', [CompanyAddressController::class, 'update'])->name('updateAddress');
        Route::delete('/address/{id}', [CompanyAddressController::class, 'destroy'])->name('deleteAddress');

        //Divisi
        Route::get('/divisi', [DivisiController::class, 'divisiIndex'])->name('showDivisi');
        Route::get('/divisi/{code}', [DivisiController::class, 'divisiShow'])->name('showDivisiByCode');
        Route::post('/divisi', [DivisiController::class, 'divisiStore'])->name('createDivisi');

        //Departemen
        Route::get('/departemen', [DepartemenController::class, 'departemenIndex'])->name('showDepartemen');
        Route::post('/departemen', [DepartemenController::class, 'departemenCreate'])->name('createDepartemen');
        Route::get('/departemen/{code}', [DepartemenController::class, 'departemenShow'])->name('showDepartemenByCode');

        //Product
        Route::get('/product', [ProductController::class, 'index'])->name('allProduct');
        Route::get('/product/{id}', [ProductController::class, 'show'])->name('product');

        //Cart
        Route::post('/cart', [CartController::class, 'addToCart'])->name('addToCart');
        Route::get('/cart', [CartController::class, 'getCart'])->name('showCart');
        Route::delete('/cart/{id}', [CartController::class, 'removeCart'])->name('removeCart');
        Route::put('/cart/{id}', [CartController::class, 'updateCart'])->name('updateCart');

        //PR
        Route::post('/purchaseRequest', [PurchaseRequestController::class, 'createPurchaseRequest'])->name('createPurchaseRequest');
        // Route::get('/show/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequest'])->name('showPurchaseRequest');
        Route::get('/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequestByUserId'])->name('showPurchaseRequestByUserId');
        Route::get('/purchaseRequest/{id}', [PurchaseRequestController::class, 'getPurchaseRequestById'])->name('showPurchaseRequestById');
        Route::put('/updateLineItem/{id}', [PurchaseRequestController::class, 'updateLineItem'])->name('updateLineItem');

        //user Approval
        Route::post('/userApproval', [AuthController::class, 'userApprovalAdd'])->name('userApproval.store');
        Route::get('/userApproval', [AuthController::class, 'userApprovalGet'])->name('userApproval.get');
        Route::delete('/userApproval/{id}', [AuthController::class, 'userApprovalDelete'])->name('userApproval.delete');

        //Approval Request
        Route::post('/approvalRequest', [ApprovalRequestController::class, 'approvalRequestCreate'])->name('approvalRequestCreate');
        Route::get('/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestGet'])->name('approvalRequestGet');

        // Quotation
        Route::post('/requestForQuotation', [QuotationController::class, 'quotationRequest'])->name('quotationRequest');
        Route::get('/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');
        Route::get('/quotationFix', [QuotationController::class, 'quotationFixall'])->name('quotationFixall');

        //Negotiaton
        Route::post('/negotiation', [NegotiationController::class, 'create'])->name('negotiation');
        Route::get('/negotiation/{id}', [NegotiationController::class, 'showByRFQ'])->name('ShowNegotiation');

        // Purchase Order
        Route::post('/purchaseOrder', [PurchaseOrderController::class, 'purchaseOrderCreate'])->name('purchaseOrderCreate');
        Route::get('/purchaseOrder', [PurchaseOrderController::class, 'purchaseOrderGetByUserId'])->name('purchaseOrderGetByUserId');
        Route::get('/purchaseOrder/{id}', [PurchaseOrderController::class, 'purchaseOrderGetById'])->name('purchaseOrderGetById');

        // Approval Orders
        Route::post('/approvalOrder', [ApprovalRequestController::class, 'approvalOrderCreate'])->name('approvalOrderCreate');
        Route::get('/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderGet'])->name('approvalOrderGet');

        //Order
        Route::post('/order', [Order_Controller::class, 'create'])->name('createOrder');
        Route::get('/order', [Order_Controller::class, 'index'])->name('showOrder');
        Route::get('/order/{id}', [Order_Controller::class, 'show'])->name('showOrderById');

        //Shipment
        Route::get('/shipment/{id}', [Shipment_Controller::class, 'showResiBuyer'])->name('shipmentShowById');
        Route::post('/shipment/{id}', [Shipment_Controller::class, 'buktiDiterima'])->name('buktiDiterima');

        //Pengembalian
        Route::post('/pengembalian', [PengembalianController::class, 'ajuanPengembalian'])->name('ajuanPengembalian');
        Route::get('/pengembalian/{id}', [PengembalianController::class, 'getPengembalianByOrderId'])->name('getPengembalianByOrderId');

        //Payment
        Route::get('/payment/{id}', [Payment__Controller::class, 'show'])->name('paymentShow');
        Route::post('/payment/{id}', [Payment__Controller::class, 'buktiSend'])->name('paymentBukti');
    });
    Route::middleware('checkRole:user_approval')->prefix('userApproval')->group(function () {
        // Approval Request
        Route::get('/approvalRequest', [ApprovalRequestController::class, 'approvalRequestGetByUserId'])->name('approvalRequestGetByUserId');
        Route::get('/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestDetail'])->name('approvalRequestDetail');
        Route::post('/approvalRequest/{code}/accept', [ApprovalRequestController::class, 'approvalRequestAccept'])->name('approvalRequestAccept');
        Route::post('/approvalRequest/{code}/reject', [ApprovalRequestController::class, 'approvalRequestReject'])->name('approvalRequestReject');

        // Approval Order
        Route::get('/approvalOrder', [ApprovalRequestController::class, 'approvalOrderGetByUserId'])->name('approvalOrderGetByUserId');
        Route::get('/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderDetail'])->name('approvalOrderDetail');
        Route::post('/approvalOrder/{code}/accept', [ApprovalRequestController::class, 'approvalOrderAccept'])->name('approvalOrderAccept');
        Route::post('/approvalOrder/{code}/reject', [ApprovalRequestController::class, 'approvalOrderReject'])->name('approvalOrderReject');
    });
});

// //Route Untuk Role Vendor
// Route::middleware('auth:api', 'cors', 'checkRole:vendor')->prefix('vendor')->group(function () {
//     //Product
//     Route::post('/product', [ProductController::class, 'store'])->name('addProduct');
//     Route::get('/product', [ProductController::class, 'showByUserId'])->name('productByUserId');
//     Route::get('/product/{id}', [ProductController::class, 'show'])->name('product');
//     Route::post('/product/{id}', [ProductController::class, 'update'])->name('updateProduct');
//     Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('deleteProduct');

//     //Category, Group, Etalase, Currency
//     Route::get('/category', [CategoryController::class, 'index'])->name('showCategory');
//     Route::get('/group', [GroupController::class, 'index'])->name('showGroup');
//     Route::get('/etalase', [EtalaseController::class, 'index'])->name('showEtalase');
//     Route::get('/currency', [CurrencyController::class, 'index'])->name('showCurrency');
//     Route::get('/drop', [ProductController::class, 'drop'])->name('showDrop');

//     //Profile
//     Route::get('/profile', [AuthController::class, 'vendorGetProfile'])->name('showProfileVendor');
//     Route::put('/profile', [AuthController::class, 'vendorUpdateProfile'])->name('updateProfileVendor');

//     //Master
//     Route::post('/master', [AuthController::class, 'vendorMasterCreate'])->name('master');

//     // Quotation
//     Route::get('/quotation', [QuotationController::class, 'quotationShow'])->name('showQuotation');
//     Route::get('/quotation/{id}', [QuotationController::class, 'quotationShowById'])->name('quotationShowById');
//     Route::post('/quotation/{id}', [QuotationController::class, 'quotationFromVendor'])->name('quotationFromVendor');
//     Route::get('/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');
//     Route::post('/quotation/{id}/pdf', [QuotationController::class, 'quotationFixPDFSendToBuyer'])->name('quotationFixPDFSendToBuyer');

//     //Order
//     Route::get('/order', [Order_Controller::class, 'showVendor'])->name('showOrder');
//     Route::get('/order/{id}', [Order_Controller::class, 'show'])->name('showOrderById');

//     //Shipment
//     Route::post('/shipment', [Shipment_Controller::class, 'create'])->name('shipmentCreate');
//     Route::get('/shipment', [Shipment_Controller::class, 'index'])->name('shipmentIndex');
//     Route::get('/shipment/{id}', [Shipment_Controller::class, 'show'])->name('shipmentShowById');

//     // Invoice
//     Route::post('/invoice', [Payment__Controller::class, 'create'])->name('invoiceCreate');
//     Route::get('/invoice/{id}', [Payment__Controller::class, 'show'])->name('invoiceById');
//     Route::post('/invoice/{id}/pdf', [Payment__Controller::class, 'sendInvoice'])->name('invoicepdf');
//     Route::post('/invoice/{id}/success', [Payment__Controller::class, 'makeSuccess'])->name('makeSuccess');
// });

// //Route Untuk Role Buyer
// Route::middleware('auth:api', 'cors', 'checkRole:company')->prefix('buyer')->group(function () {

//     //Profile
//     Route::get('/profile', [AuthController::class, 'userGetProfile'])->name('showProfileUser');
//     Route::post('/profile', [AuthController::class, 'userUpdateProfile'])->name('updateProfileUser');

//     //Divisi
//     Route::get('/divisi', [DivisiController::class, 'divisiIndex'])->name('showDivisi');
//     Route::get('/divisi/{code}', [DivisiController::class, 'divisiShow'])->name('showDivisiById');
//     Route::post('/divisi', [DivisiController::class, 'divisiStore'])->name('createDivisi');

//     //Departemen
//     Route::get('/departemen', [DepartemenController::class, 'departemenIndex'])->name('showDepartemen');
//     Route::post('/departemen', [DepartemenController::class, 'departemenCreate'])->name('createDepartemen');
//     Route::get('/departemen/{code}', [DepartemenController::class, 'departemenShow'])->name('showDepartemenById');

//     //Product
//     Route::get('/product', [ProductController::class, 'index'])->name('allProduct');
//     Route::get('/product/{id}', [ProductController::class, 'show'])->name('product');

//     //Cart
//     Route::post('/cart', [CartController::class, 'addToCart'])->name('addToCart');
//     Route::get('/cart', [CartController::class, 'getCart'])->name('showCart');
//     Route::delete('/cart/{id}', [CartController::class, 'removeCart'])->name('removeCart');
//     Route::put('/cart/{id}', [CartController::class, 'updateCart'])->name('updateCart');

//     //PR
//     Route::post('/purchaseRequest', [PurchaseRequestController::class, 'createPurchaseRequest'])->name('createPurchaseRequest');
//     // Route::get('/show/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequest'])->name('showPurchaseRequest');
//     Route::get('/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequestByUserId'])->name('showPurchaseRequestByUserId');
//     Route::get('/purchaseRequest/{id}', [PurchaseRequestController::class, 'getPurchaseRequestById'])->name('showPurchaseRequestById');
//     Route::post('/updateLineItem/{id}', [PurchaseRequestController::class, 'updateLineItem'])->name('updateLineItem');

//     //user Approval
//     Route::post('/userApproval', [AuthController::class, 'userApprovalAdd'])->name('userApproval.store');
//     Route::get('/userApproval', [AuthController::class, 'userApprovalGet'])->name('userApproval.get');
//     Route::delete('/userApproval/{id}', [AuthController::class, 'userApprovalDelete'])->name('userApproval.delete');

//     //Approval Request
//     Route::post('/approvalRequest', [ApprovalRequestController::class, 'approvalRequestCreate'])->name('approvalRequestCreate');
//     Route::get('/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestGet'])->name('approvalRequestGet');

//     // Quotation
//     Route::post('/requestForQuotation', [QuotationController::class, 'quotationRequest'])->name('quotationRequest');
//     Route::get('/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');
//     Route::get('/quotationFix', [QuotationController::class, 'quotationFixall'])->name('quotationFixall');

//     // Purchase Order
//     Route::post('/purchaseOrder', [PurchaseOrderController::class, 'purchaseOrderCreate'])->name('purchaseOrderCreate');
//     Route::get('/purchaseOrder', [PurchaseOrderController::class, 'purchaseOrderGetByUserId'])->name('purchaseOrderGetByUserId');
//     Route::get('/purchaseOrder/{id}', [PurchaseOrderController::class, 'purchaseOrderGetById'])->name('purchaseOrderGetById');

//     // Approval Orders
//     Route::post('/approvalOrder', [ApprovalRequestController::class, 'approvalOrderCreate'])->name('approvalOrderCreate');
//     Route::get('/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderGet'])->name('approvalOrderGet');

//     //Order
//     Route::post('/order', [Order_Controller::class, 'create'])->name('createOrder');
//     Route::get('/order', [Order_Controller::class, 'index'])->name('showOrder');
//     Route::get('/order/{id}', [Order_Controller::class, 'show'])->name('showOrderById');

//     //Shipment
//     Route::get('/shipment/{id}', [Shipment_Controller::class, 'showResiBuyer'])->name('shipmentShowById');
//     Route::post('/shipment/{id}', [Shipment_Controller::class, 'buktiDiterima'])->name('shipmentShowById');

//     //Payment
//     Route::get('/payment/{id}', [Payment__Controller::class, 'show'])->name('paymentShow');
//     Route::post('/payment/{id}', [Payment__Controller::class, 'buktiSend'])->name('paymentBukti');
// });

// //Route Untuk User Approval0
// Route::middleware('auth:api', 'cors', 'checkRole:user_approval')->prefix('userApproval')->group(function () {
//     // Approval Request
//     Route::get('/approvalRequest', [ApprovalRequestController::class, 'approvalRequestGetByUserId'])->name('approvalRequestGetByUserId');
//     Route::get('/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestDetail'])->name('approvalRequestDetail');
//     Route::post('/approvalRequest/{code}/accept', [ApprovalRequestController::class, 'approvalRequestAccept'])->name('approvalRequestAccept');
//     Route::post('/approvalRequest/{code}/reject', [ApprovalRequestController::class, 'approvalRequestReject'])->name('approvalRequestReject');

//     // Approval Order
//     Route::get('/approvalOrder', [ApprovalRequestController::class, 'approvalOrderGetByUserId'])->name('approvalOrderGetByUserId');
//     Route::get('/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderDetail'])->name('approvalOrderDetail');
//     Route::post('/approvalOrder/{code}/accept', [ApprovalRequestController::class, 'approvalOrderAccept'])->name('approvalOrderAccept');
//     Route::post('/approvalOrder/{code}/reject', [ApprovalRequestController::class, 'approvalOrderReject'])->name('approvalOrderReject');
// });
