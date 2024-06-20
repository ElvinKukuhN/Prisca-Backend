<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Models\Other;
use App\Models\Grosir;
use App\Models\Etalase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PurchaseQTY;
use Illuminate\Http\Request;
use App\Models\Product_Image;
use App\Models\CommercialInfo;
use App\Models\SpecificationDetail;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'image' => 'required|array',
            'image.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_id' => 'required',
            'category_id' => 'required',
            'product_category_name' => 'required',
            'status' => 'required',
            'productSpecification' => 'required',
            'technicalSpecification' => 'required',
            'feature' => 'required',
            'partNumber' => 'required',
            'satuan' => 'required',
            // 'video' => 'required',
            'condition' => 'required',
            'etalase_id' => 'required',
            'currency_id' => 'required',
            'price' => 'required',
            'payment_terms' => 'required',
            'discount' => 'required',
            'price_exp' => 'required',
            'stock' => 'required',
            'pre_order' => 'required',
            'contract' => 'required',
            'min' => 'required',
            'max' => 'required',
            'incomterm' => 'required',
            'warranty' => 'required',
            'maintenance' => 'required',
            'sku' => 'required',
            'tags' => 'required',
        ]);

        // Mengelola file gambar yang diunggah
        $images = [];
        if ($request->hasFile('image')) {
            $counter = 1;
            foreach ($request->file('image') as $image) {
                $imageName = $request->name . '-' . $counter . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $images[] = $imageName;
                $counter++;
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Image not provided'
            ], 400);
        }

        // Membuat produk dengan data yang diberikan
        $product = Product::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'group_id' => $request->group_id,
            'category_id' => $request->category_id,
            'brand' => $request->brand,
            'product_category_name' => $request->product_category_name,
            'status' => $request->status ?? 'inactive'
        ]);

        if ($request->hasFile('video')) {
            $videoName = time() . '-' . uniqid() . '.' . $request->video->getClientOriginalExtension();
            $request->video->move(public_path('videos'), $videoName);
            $video = $videoName;
        } else {
            $video = null;
        }
        $specificationProduct = SpecificationDetail::create([
            'product_id' => $product->id,
            'productSpecification' => $request->productSpecification,
            'technicalSpecification' => $request->technicalSpecification,
            'feature' => $request->feature,
            'partNumber' => $request->partNumber,
            'satuan' => $request->satuan,
            'video' => $video,
            'condition' => $request->condition,
        ]);

        $commercialInfo = CommercialInfo::create([
            'product_id' => $product->id,
            'etalase_id' => $request->etalase_id,
            'currency_id' => $request->currency_id,
            'price' => $request->price,
            'payment_terms' => $request->payment_terms,
            'discount' => $request->discount,
            'price_exp' => $request->price_exp,
            'stock' => $request->stock,
            'pre_order' => $request->pre_order,
            'contract' => $request->contract,
        ]);

        $purchaseQTY = PurchaseQTY::create([
            'commercial_info_id' => $commercialInfo->id,
            'min' => $request->min,
            'max' => $request->max,
        ]);

        $grosir = Grosir::create([
            'commercial_info_id' => $commercialInfo->id,
            'qty' => $request->qty,
            'price' => $request->grosir_price,
        ]);

        $other = Other::create([
            'product_id' => $product->id,
            'incomterm' => $request->incomterm,
            'warranty' => $request->warranty,
            'maintenance' => $request->maintenance,
            'sku' => $request->sku,
            'tags' => $request->tags,
        ]);

        if ($product) {
            $specificationProduct;
            $other;
            if ($commercialInfo) {
                $purchaseQTY;
                $grosir;
            }
            foreach ($images as $image) {
                $productImage = Product_Image::create([
                    'product_id' => $product->id,
                    'image' => $image
                ]);
            }
            if ($productImage) {
                $productImagesData = [];
                foreach ($product->productImage as $productImage) {
                    $productImagesData[] = [
                        'id' => $productImage->id,
                        'image' => $productImage->image,
                    ];
                }
                return response()->json([
                    'success' => true,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'group' => $product->group->name,
                        'category' => $product->category->name,
                        'brand' => $product->brand,
                        'product_category_name' => $product->product_category_name,
                        'status' => $product->status,
                        'product_image' => $productImagesData,
                        'detail' => [
                            'productSpecification' => $specificationProduct->productSpecification,
                            'technicalSpecification' => $specificationProduct->technicalSpecification,
                            'feature' => $specificationProduct->feature,
                            'partNumber' => $specificationProduct->partNumber,
                            'satuan' => $specificationProduct->satuan,
                            'video' => asset('videos/' . $specificationProduct->video),
                            'condition' => $specificationProduct->condition,
                        ],
                        'commercial_info' => [
                            'commercialInfo' => $product->commercialInfo,
                            'purchaseQty' => $product->commercialInfo->purchaseQTY,
                            'grosir' => $product->commercialInfo->grosir,
                        ],
                        'other' => $other,
                        'author' => [
                            'id' => $product->user->id,
                            'name' => $product->user->name,
                        ]
                    ]
                ], 201);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Product failed to save'
        ], 409);
    }

    public function index()
    {
        $products = Product::where('status', 'active')->with('productImage')->with('specificationDetail')->get();

        $productData = [];
        foreach ($products as $product) {
            $productImagesData = [];
            foreach ($product->productImage as $productImage) {
                // $base64_image = base64_encode(file_get_contents(public_path('images/' . $productImage->image)));
                $productImagesData[] = [
                    'id' => $productImage->id,
                    'image' => $productImage->image,
                    'url_image' => url('images/' . $productImage->image),
                ];
            }

            $productData[] = [
                'id' => $product->id,
                'status' => $product->status,
                'name' => $product->name,
                'category' => $product->category->name,
                'brand' => $product->brand,
                'price' => $product->commercialInfo->price,
                'vendor_id' => $product->user->id,
                'vendor_name' => $product->user->name,
                'images' => $productImagesData,
            ];
        }

        return response()->json([
            'success' => true,
            'products' => $productData
        ], 200);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if ($product) {
            $productImagesData = [];
            foreach ($product->productImage as $productImage) {
                // $base64_image = base64_encode(file_get_contents(public_path('images/' . $productImage->image)));
                $productImagesData[] = [
                    'id' => $productImage->id,
                    'image' => $productImage->image,
                    'url_image' => url('images/' . $productImage->image),
                ];
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'group' => $product->group->name,
                    'category' => $product->category->name,
                    'product_category_name' => $product->product_category_name,
                    'status' => $product->status,
                    'brand' => $product->brand,
                    'stocks' => $product->commercialInfo->stock,
                    'detail' => [
                        'productSpecification' => $product->specificationDetail->productSpecification,
                        'technicalSpecification' => $product->specificationDetail->technicalSpecification,
                        'feature' => $product->specificationDetail->feature,
                        'partNumber' => $product->specificationDetail->partNumber,
                        'satuan' => $product->specificationDetail->satuan,
                        'video' => asset('videos/' . $product->specificationDetail->video),
                        'condition' => $product->specificationDetail->condition,
                    ],
                    'commercial_info' => [
                        'commercialInfo' => [
                            'etalase' => $product->commercialInfo->etalase->name,
                            'currency' => $product->commercialInfo->currency->name,
                            'price' => $product->commercialInfo->price,
                            'payment_terms' => $product->commercialInfo->payment_terms,
                            'discount' => $product->commercialInfo->discount,
                            'price_exp' => $product->commercialInfo->price_exp,
                            'stock' => $product->commercialInfo->stock,
                            'pre_order' => $product->commercialInfo->pre_order,
                            'contract' => $product->commercialInfo->contract,
                        ],
                        'purchaseQty' => [
                            'min' => $product->commercialInfo->purchaseQTY->min,
                            'max' => $product->commercialInfo->purchaseQTY->max,
                        ],
                        'grosir' => [
                            'qty' => $product->commercialInfo->grosir->qty,
                            'price' => $product->commercialInfo->grosir->price,
                        ],
                    ],
                    'other' => [
                        'incomterm' => $product->other->incomterm,
                        'warranty' => $product->other->warranty,
                        'maintenance' => $product->other->maintenance,
                        'sku' => $product->other->sku,
                        'tags' => $product->other->tags,
                    ],
                    'images' => $productImagesData,
                    // 'author' => [
                    //     'id' => $product->user->id,
                    //     'name' => $product->user->name,
                    // ]
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if ($product) {
            $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'image' => 'array',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'group_id' => 'required',
                'category_id' => 'required',
                'product_category_name' => 'required',
                'status' => 'required',
                'productSpecification' => 'required',
                'technicalSpecification' => 'required',
                'feature' => 'required',
                'partNumber' => 'required',
                'satuan' => 'required',
                // 'video' => 'required',
                'condition' => 'required',
                'etalase_id' => 'required',
                'currency_id' => 'required',
                'price' => 'required',
                'payment_terms' => 'required',
                'discount' => 'required',
                'price_exp' => 'required',
                'stock' => 'required',
                'pre_order' => 'required',
                'contract' => 'required',
                'min' => 'required',
                'max' => 'required',
                'incomterm' => 'required',
                'warranty' => 'required',
                'maintenance' => 'required',
                'sku' => 'required',
                'tags' => 'required',
            ]);

            $product->name = $request->name;
            $product->group_id = $request->group_id;
            $product->category_id = $request->category_id;
            $product->brand = $request->brand;
            $product->product_category_name = $request->product_category_name;
            $product->status = $request->status;
            $product->save();

            //Bagian Specification produk
            $specificationProduct = SpecificationDetail::where('product_id', $product->id)->first();
            if ($request->hasFile('video')) {
                // Hapus video lama jika ada
                if ($specificationProduct->video) {
                    $oldVideoPath = public_path('videos') . '/' . $specificationProduct->video;
                    if (file_exists($oldVideoPath)) {
                        unlink($oldVideoPath);
                    }
                }

                // Mengunggah video baru
                $videoName = time() . '-' . uniqid() . '.' . $request->video->getClientOriginalExtension();
                $request->video->move(public_path('videos'), $videoName);
                $video = $videoName;
            } else {
                $video = null;
            }
            $specificationProduct->productSpecification = $request->productSpecification;
            $specificationProduct->technicalSpecification = $request->technicalSpecification;
            $specificationProduct->feature = $request->feature;
            $specificationProduct->partNumber = $request->partNumber;
            $specificationProduct->satuan = $request->satuan;
            $specificationProduct->video = $video ? $video : $specificationProduct->video;
            $specificationProduct->condition = $request->condition;
            $specificationProduct->save();

            $commercialInfo = CommercialInfo::where('product_id', $product->id)->first();
            $commercialInfo->etalase_id = $request->etalase_id;
            $commercialInfo->currency_id = $request->currency_id;
            $commercialInfo->price = $request->price;
            $commercialInfo->discount = $request->discount;
            $commercialInfo->price_exp = $request->price_exp;
            $commercialInfo->stock = $request->stock;
            $commercialInfo->pre_order = $request->pre_order;
            $commercialInfo->contract = $request->contract;
            $commercialInfo->payment_terms = $request->payment_terms;
            $commercialInfo->save();

            $purchaseQTY = PurchaseQTY::where('commercial_info_id', $commercialInfo->id)->first();
            $purchaseQTY->min = $request->min;
            $purchaseQTY->max = $request->max;
            $purchaseQTY->save();

            $grosir = Grosir::where('commercial_info_id', $commercialInfo->id)->first();
            $grosir->qty = $request->qty;
            $grosir->price = $request->grosir_price;
            $grosir->save();

            $other = Other::where('product_id', $product->id)->first();
            $other->incomterm = $request->incomterm;
            $other->warranty = $request->warranty;
            $other->maintenance = $request->maintenance;
            $other->sku = $request->sku;
            $other->tags = $request->tags;
            $other->save();

            if ($request->hasFile('image')) {
                $oldImages = Product_Image::where('product_id', $product->id)->get();

                // Menghapus gambar lama
                foreach ($oldImages as $oldImage) {
                    $imagePath = public_path('images') . '/' . $oldImage->image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    $oldImage->delete();
                }

                $images = [];
                foreach ($request->file('image') as $image) {
                    $imageName = $oldImages;
                    $image->move(public_path('images'), $imageName);
                    $images[] = $imageName;
                }

                // Menambahkan gambar baru
                foreach ($images as $image) {
                    Product_Image::create([
                        'product_id' => $product->id,
                        'image' => $image,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'group' => $product->group->name,
                    'category' => $product->category->name,
                    'brand' => $product->brand,
                    'product_category_name' => $product->product_category_name,
                    'detail' => [
                        'productSpecification' => $specificationProduct->productSpecification,
                        'technicalSpecification' => $specificationProduct->technicalSpecification,
                        'feature' => $specificationProduct->feature,
                        'partNumber' => $specificationProduct->partNumber,
                        'satuan' => $specificationProduct->satuan,
                        'video' => asset('videos/' . $specificationProduct->video),
                        'condition' => $specificationProduct->condition,
                    ],
                    'commercial_info' => [
                        'commercialInfo' => [
                            'etalase' => $product->commercialInfo->etalase->name,
                            'currency' => $product->commercialInfo->currency->name,
                            'price' => $product->commercialInfo->price,
                            'payment_terms' => $product->commercialInfo->payment_terms,
                            'discount' => $product->commercialInfo->discount,
                            'price_exp' => $product->commercialInfo->price_exp,
                            'stock' => $product->commercialInfo->stock,
                            'pre_order' => $product->commercialInfo->pre_order,
                            'contract' => $product->commercialInfo->contract,
                        ],
                        'purchaseQty' => [
                            'min' => $product->commercialInfo->purchaseQTY->min,
                            'max' => $product->commercialInfo->purchaseQTY->max,
                        ],
                        'grosir' => [
                            'qty' => $product->commercialInfo->grosir->qty,
                            'price' => $product->commercialInfo->grosir->price,
                        ],
                    ],
                    'other' => [
                        'incomterm' => $product->other->incomterm,
                        'warranty' => $product->other->warranty,
                        'maintenance' => $product->other->maintenance,
                        'sku' => $product->other->sku,
                        'tags' => $product->other->tags,
                    ],
                    'image' => $product->productImage,
                    'author' => [
                        'id' => $product->user->id,
                        'name' => $product->user->name,
                    ]
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product) {
            $productImages = Product_Image::where('product_id', $product->id)->get();
            foreach ($productImages as $productImage) {
                $imagePath = 'images/' . $productImage->image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $productImage->delete();
            }
            $specificationProduct = SpecificationDetail::where('product_id', $product->id)->first();
            $specificationProduct->delete();
            $commercialInfo = CommercialInfo::where('product_id', $product->id)->first();
            $purchaseQTY = PurchaseQTY::where('commercial_info_id', $commercialInfo->id)->first();
            $purchaseQTY->delete();
            $grosir = Grosir::where('commercial_info_id', $commercialInfo->id)->first();
            $grosir->delete();
            $commercialInfo->delete();
            $delete = $product->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ], 200);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    public function showByUserId()
    {
        $products = Product::where('user_id', auth()->user()->id)->with('productImage')->get();

        $productData = [];
        foreach ($products as $product) {
            $productImagesData = [];
            foreach ($product->productImage as $productImage) {
                // $base64_image = base64_encode(file_get_contents(public_path('images/' . $productImage->image)));
                $productImagesData[] = [
                    'id' => $productImage->id,
                    'image' => $productImage->image,
                    'url_image' => url('images/' . $productImage->image),
                ];
            }

            $productData[] = [
                'id' => $product->id,
                'name' => $product->name,
                'group' => $product->group->name,
                'category' => $product->category->name,
                'product_category_name' => $product->product_category_name,
                'status' => $product->status,
                'brand' => $product->brand,
                'detail' => [
                    'productSpecification' => $product->specificationDetail->productSpecification,
                    'technicalSpecification' => $product->specificationDetail->technicalSpecification,
                    'feature' => $product->specificationDetail->feature,
                    'partNumber' => $product->specificationDetail->partNumber,
                    'satuan' => $product->specificationDetail->satuan,
                    'video' => asset('videos/' . $product->specificationDetail->video),
                    'condition' => $product->specificationDetail->condition,
                ],
                'commercial_info' => [
                    'commercialInfo' => [
                        'etalase' => $product->commercialInfo->etalase->name,
                        'currency' => $product->commercialInfo->currency->name,
                        'price' => $product->commercialInfo->price,
                        'payment_terms' => $product->commercialInfo->payment_terms,
                        'discount' => $product->commercialInfo->discount,
                        'price_exp' => $product->commercialInfo->price_exp,
                        'stock' => $product->commercialInfo->stock,
                        'pre_order' => $product->commercialInfo->pre_order,
                        'contract' => $product->commercialInfo->contract,
                    ],
                    'purchaseQty' => [
                        'min' => $product->commercialInfo->purchaseQTY->min,
                        'max' => $product->commercialInfo->purchaseQTY->max,
                    ],
                    'grosir' => [
                        'qty' => $product->commercialInfo->grosir->qty,
                        'price' => $product->commercialInfo->grosir->price,
                    ],
                ],
                'other' => [
                    'incomterm' => $product->other->incomterm,
                    'warranty' => $product->other->warranty,
                    'maintenance' => $product->other->maintenance,
                    'sku' => $product->other->sku,
                    'tags' => $product->other->tags,
                ],
                'images' => $productImagesData,
            ];
        }

        return response()->json([
            'success' => true,
            'products' => $productData
        ], 200);
    }

    public function drop()
    {
        $categories = Category::all();
        $groups = Group::all();
        $currencies = Currency::all();
        $etalases = Etalase::all();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'groups' => $groups,
                'currencies' => $currencies,
                'etalases' => $etalases,
            ],
        ], 200);
    }
}
