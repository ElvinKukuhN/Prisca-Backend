<?php

use App\Http\Controllers\Api\ApprovalRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\DivisiController;
use App\Http\Controllers\Api\EtalaseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\QuotationController;
use App\Models\ApprovalRequest;

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

//Route Untuk Role Vendor
Route::middleware('auth:api', 'cors', 'checkRole:vendor')->prefix('vendor')->group(function () {
    //Product
    Route::post('/create/product', [ProductController::class, 'store'])->name('addProduct');
    Route::get('/show/productByUserId', [ProductController::class, 'showByUserId'])->name('productByUserId');
    Route::get('/show/product/{id}', [ProductController::class, 'show'])->name('product');
    Route::post('/updateProduct/{id}', [ProductController::class, 'update'])->name('updateProduct');
    Route::delete('/deleteProduct/{id}', [ProductController::class, 'destroy'])->name('deleteProduct');

    //Category, Group, Etalase, Currency
    Route::get('/show/category', [CategoryController::class, 'index'])->name('showCategory');
    Route::get('/show/group', [GroupController::class, 'index'])->name('showGroup');
    Route::get('/show/etalase', [EtalaseController::class, 'index'])->name('showEtalase');
    Route::get('/show/currency', [CurrencyController::class, 'index'])->name('showCurrency');
    Route::get('/show/drop', [ProductController::class, 'drop'])->name('showDrop');

    //Profile
    Route::get('/show/profile', [AuthController::class, 'vendorGetProfile'])->name('showProfileVendor');
    Route::post('/update/profile', [AuthController::class, 'vendorUpdateProfile'])->name('updateProfileVendor');

    //Master
    Route::post('/master', [AuthController::class, 'vendorMasterCreate'])->name('master');

    // Quotation
    Route::get('/show/quotation', [QuotationController::class, 'quotationShow'])->name('showQuotation');
    Route::get('/show/quotation/{id}', [QuotationController::class, 'quotationShowById'])->name('quotationShowById');
    Route::post('/create/quotation/{id}', [QuotationController::class, 'quotationFromVendor'])->name('quotationFromVendor');
    Route::get('/show/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');
    Route::post('/send/quotation/{id}/pdf', [QuotationController::class, 'quotationFixPDFSendToBuyer'])->name('quotationFixPDFSendToBuyer');
});

//Route Untuk Role Buyer
Route::middleware('auth:api', 'cors', 'checkRole:company')->prefix('buyer')->group(function () {

    Route::get('/show/allUser', [AuthController::class, 'allUser'])->name('allUser');

    //Profile
    Route::get('/show/profile', [AuthController::class, 'userGetProfile'])->name('showProfileUser');
    Route::post('/update/profile', [AuthController::class, 'userUpdateProfile'])->name('updateProfileUser');

    //Divisi
    Route::get('/show/divisi', [DivisiController::class, 'divisiIndex'])->name('showDivisi');
    Route::get('/show/divisi/{code}', [DivisiController::class, 'divisiShow'])->name('showDivisiById');
    Route::post('/createDivisi', [DivisiController::class, 'divisiStore'])->name('createDivisi');

    //Departemen
    Route::get('/show/departemen', [DepartemenController::class, 'departemenIndex'])->name('showDepartemen');
    Route::get('/show/departemen/{code}', [DepartemenController::class, 'departemenShow'])->name('showDepartemenById');
    Route::post('/createDepartemen', [DepartemenController::class, 'departemenCreate'])->name('createDepartemen');

    //Product
    Route::get('/show/allProduct', [ProductController::class, 'index'])->name('allProduct');
    Route::get('/show/product/{id}', [ProductController::class, 'show'])->name('product');

    //Cart
    Route::post('/addToCart', [CartController::class, 'addToCart'])->name('addToCart');
    Route::get('/show/cart', [CartController::class, 'getCart'])->name('showCart');
    Route::delete('/removeCart/{id}', [CartController::class, 'removeCart'])->name('removeCart');
    Route::put('/updateCart/{id}', [CartController::class, 'updateCart'])->name('updateCart');

    //PR
    Route::post('/createPurchaseRequest', [PurchaseRequestController::class, 'createPurchaseRequest'])->name('createPurchaseRequest');
    // Route::get('/show/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequest'])->name('showPurchaseRequest');
    Route::get('/show/purchaseRequest', [PurchaseRequestController::class, 'getPurchaseRequestByUserId'])->name('showPurchaseRequestByUserId');
    Route::get('/show/purchaseRequest/{id}', [PurchaseRequestController::class, 'getPurchaseRequestById'])->name('showPurchaseRequestById');
    Route::post('/updateLineItem/{id}', [PurchaseRequestController::class, 'updateLineItem'])->name('updateLineItem');

    //user Approval
    Route::post('/create/userApproval', [AuthController::class, 'userApprovalAdd'])->name('userApproval.store');
    Route::get('/show/userApproval', [AuthController::class, 'userApprovalGet'])->name('userApproval.get');
    Route::delete('/delete/userApproval/{id}', [AuthController::class, 'userApprovalDelete'])->name('userApproval.delete');

    //Approval Request
    Route::post('/create/approvalRequest', [ApprovalRequestController::class, 'approvalRequestCreate'])->name('approvalRequestCreate');
    Route::get('/show/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestGet'])->name('approvalRequestGet');

    // Quotation
    Route::post('/create/requestForQuotation', [QuotationController::class, 'quotationRequest'])->name('quotationRequest');
    Route::get('/show/quotationFix/{id}', [QuotationController::class, 'quotationFixGet'])->name('quotationFixGet');

    // Purchase Order
    Route::post('/create/purchaseOrder/{id}', [PurchaseOrderController::class, 'purchaseOrderCreate'])->name('purchaseOrderCreate');
    Route::get('/show/purchaseOrder', [PurchaseOrderController::class, 'purchaseOrderGetByUserId'])->name('purchaseOrderGetByUserId');
    Route::get('/show/purchaseOrder/{id}', [PurchaseOrderController::class, 'purchaseOrderGetById'])->name('purchaseOrderGetById');

    // Approval Orders
    Route::post('/create/approvalOrder', [ApprovalRequestController::class, 'approvalOrderCreate'])->name('approvalOrderCreate');
    Route::get('/show/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderGet'])->name('approvalOrderGet');
});

//Route Untuk User Approval
Route::middleware('auth:api', 'cors', 'checkRole:user_approval')->prefix('userApproval')->group(function () {
    // Approval Request
    Route::get('/show/approvalRequest', [ApprovalRequestController::class, 'approvalRequestGetByUserId'])->name('approvalRequestGetByUserId');
    Route::post('/accept/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestAccept'])->name('approvalRequestAccept');
    Route::post('/reject/approvalRequest/{code}', [ApprovalRequestController::class, 'approvalRequestReject'])->name('approvalRequestReject');

    // Approval Order
    Route::get('/show/approvalOrder', [ApprovalRequestController::class, 'approvalOrderGetByUserId'])->name('approvalOrderGetByUserId');
    Route::post('/accept/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderAccept'])->name('approvalOrderAccept');
    Route::post('/reject/approvalOrder/{code}', [ApprovalRequestController::class, 'approvalOrderReject'])->name('approvalOrderReject');
});


Route::post('/userRegister', [AuthController::class, 'userRegister'])->name('userRegister');
Route::post('/vendorRegister', [AuthController::class, 'vendorRegister'])->name('vendorRegister');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
