<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\UserCompany;
use App\Models\MasterVendor;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use App\Models\CompanyAddress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PhpParser\Node\Stmt\Return_;

class AuthController extends Controller
{

    public function userRegister(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',
            'telp'      => 'required|numeric',

        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::where('name', 'company')->first();

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role_id'   => $role->id,
            'telp' => $request->telp
        ]);

        $companyCode = rand(100, 999);

        $company = Company::create([
            'code' => $companyCode,
            'name' => $request->company_name
        ]);

        $userCompany = UserCompany::create([
            'user_id' => $user->id,
            'company_code' => $companyCode,
            'divisi_code' => $request->divisi_code,
            'departemen_code' => $request->departemen_code
        ]);

        if ($user) {
            $company;
            if ($company) {
                $userCompany;
                if ($user && $user->role->name == "company") {
                    return response()->json([
                        'success' => true,
                        'user'    => [
                            'id'    => $user->id,
                            'name'  => $user->name,
                            'email' => $user->email,
                            'company' => [
                                'company_code' => $company->code,
                                'name'  => $company->name
                            ],
                            'role'  => [
                                'id'    => $user->role->id,
                                'name'  => $user->role->name
                            ]
                        ],
                    ], 201);
                }
            }
        }
    }

    public function vendorRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',
            'telp'      => 'required|numeric',

        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::where('name', 'vendor')->first();

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role_id'   => $role->id,
            'telp' => $request->telp
        ]);

        $masterVendor = MasterVendor::create([
            'user_id' => $user->id,
            'alamat'    => $request->vendor_alamat,
            'npwp'       => $request->vendor_npwp,
            'bidang_usaga' => $request->vendor_bidang_usaha,
            'tanggal_berdiri' => $request->vendor_tanggal_berdiri,
            'siup' => $request->vendor_siup,
            'website' => $request->vendor_website
        ]);

        if ($user) {
            $masterVendor;
            if ($masterVendor) {
                if ($user && $user->role->name == "vendor") {
                    return response()->json([
                        'success' => true,
                        'user'    => [
                            'id'    => $user->id,
                            'name'  => $user->name,
                            'email' => $user->email,
                            'master' => $masterVendor,
                            'role'  => [
                                'id'    => $user->role->id,
                                'name'  => $user->role->name
                            ]
                        ],
                    ], 201);
                }
            }
        }
    }

    public function login(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        //if auth failed
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        $user = auth()->guard('api')->user();
        return response()->json([
            'success' => true,
            'user'    => [
                'id' => $user->id,
                'name' => $user->name,
                'email' =>  $user->email,
                'role' => [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name
                ]
            ],
            'token'   => $token
        ], 200);
    }

    public function allUser()
    {
        $users = User::all();

        $userData = [];

        foreach ($users as $user) {
            $userData[] = [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'users'   => $userData,
        ], 200);
    }

    public function logout()
    {
        try {
            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
            if ($removeToken) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logout berhasil'
                ], 200);
            }
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function vendorGetProfile()
    {

        $user = auth()->user();
        $masterVendor = MasterVendor::where('user_id', $user->id)->first();

        // Lanjutkan dengan logika Anda di sini jika token bearer ada dan tidak kosong

        return response()->json([
            'success' => true,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'telp' => $user->telp,
                'master' => $masterVendor,
                'role'  => [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name
                ]
            ],
        ], 200);
    }

    public function vendorUpdateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email',
            'telp'      => 'required',
            'vendor_alamat'    => 'required',
            'vendor_npwp'          => 'required|max:16|regex:/^\d+$/',
            'vendor_bidang_usaha' => 'required',
            'vendor_tanggal_berdiri' => 'required',
            'siup' => 'required',
            'vendor_website' => 'required',
            'vendor_bank' => 'required',
            'vendor_rekening' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $masterVendor = MasterVendor::where('user_id', $user->id)->first();


        if ($request->hasFile('siup')) {
            $siupName = auth()->user()->name . '_siup' . '.' . $request->siup->getClientOriginalExtension();
            $request->siup->move(public_path('vendor/siup'), $siupName);
            $siup = $siupName;
        } else {
            $siup = null;
        }

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'telp' => $request->telp ?? $user->telp
        ]);

        $masterVendor->update([
            'alamat' => $request->vendor_alamat,
            'npwp' => $request->vendor_npwp,
            'bidang_usaha' => $request->vendor_bidang_usaha,
            'tanggal_berdiri' => $request->vendor_tanggal_berdiri,
            'siup' => $siup,
            'website' => $request->vendor_website,
            'bank' => $request->vendor_bank,
            'rekening' => $request->vendor_rekening
        ]);

        if ($user && $masterVendor) {
            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'master' => [
                        'id' => $masterVendor->id,
                        'alamat' => $masterVendor->alamat,
                        'npwp' => $masterVendor->npwp,
                        'bidang_usaha' => $masterVendor->bidang_usaha,
                        'tanggal_berdiri' => $masterVendor->tanggal_berdiri,
                        'siup' => asset('vendor/siup/' . $masterVendor->siup),
                        'website' => $masterVendor->website,
                        'bank' => $masterVendor->bank,
                        'rekening' => $masterVendor->rekening
                    ],
                    'role'  => [
                        'id'    => $user->role->id,
                        'name'  => $user->role->name
                    ]
                ],

            ], 200);
        }
    }

    public function vendorMasterCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alamat'    => 'required',
            'npwp'       => 'required',
            'bidang_usaha' => 'required',
            'tanggal_berdiri' => 'required',
            'siup' => 'required|file|mimes:pdf|max:2048',
            'website' => 'required',
            'bank' => 'required',
            'rekening' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();

        if ($request->hasFile('siup')) {
            $siupName = auth()->user()->name . '_siup' . '.' . $request->siup->getClientOriginalExtension();
            $request->siup->move(public_path('vendor/siup'), $siupName);
            $siup = $siupName;
        } else {
            $siup = null;
        }

        $masterVendor = MasterVendor::create([
            'user_id' => $user->id,
            'alamat'    => $request->vendor_alamat,
            'npwp'       => $request->vendor_npwp,
            'bidang_usaha' => $request->vendor_bidang_usaha,
            'tanggal_berdiri' => $request->vendor_tanggal_berdiri,
            'siup' => $siup,
            'website' => $request->vendor_website,
            'bank' => $request->vendor_bank,
            'rekening' => $request->vendor_rekening
        ]);

        if ($masterVendor) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Master Vendor berhasil diupdate',
                    'user'    => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'master' => [
                            'id' => $masterVendor->id,
                            'alamat' => $masterVendor->alamat,
                            'npwp' => $masterVendor->npwp,
                            'bidang_usaha' => $masterVendor->bidang_usaha,
                            'tanggal_berdiri' => $masterVendor->tanggal_berdiri,
                            'siup' => asset('vendor/siup/' . $masterVendor->siup),
                            'website' => $masterVendor->website,
                            'bank' => $masterVendor->bank,
                            'rekening' => $masterVendor->rekening
                        ],
                        'role'  => [
                            'id'    => $user->role->id,
                            'name'  => $user->role->name
                        ]
                    ],

                ],
                200
            );
        }
    }

    public function userGetProfile()
    {
        $user = auth()->user();
        $userCompany = UserCompany::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'telp' => $user->telp,
                'company' => [
                    'address' => $userCompany->address,
                    'company_code' => $userCompany->company->code,
                    'company_name' => $userCompany->company->name ?? null,
                    'divisi_code' => $userCompany->divisi_code,
                    'divisi_name' => $userCompany->divisi->name ?? null,
                    'departemen_code' => $userCompany->departemen_code,
                    'departemen_name' => $userCompany->departemen->name ?? null
                ],
                'role'  => [
                    'id'    => $user->role->id,
                    'name'  => $user->role->name
                ]
            ],
        ], 200);
    }

    public function userUpdateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email',
            'telp'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $userCompany = UserCompany::where('user_id', $user->id)->first();

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'telp' => $request->telp ?? $user->telp
        ]);

        $userCompany->update([
            'divisi_code' => $request->divisi_code,
            'departemen_code' => $request->departemen_code,
            'address' => $request->address
        ]);

        $companyAddress = CompanyAddress::create([
            'user_companies_id' => $userCompany->id,
            'address' => $request->address
        ]);


        if ($user && $userCompany) {
            $companyAddress;

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'telp' => $user->telp,
                    'company' => [
                        'address' => $userCompany->address ?? null,
                        'company_code' => $userCompany->company->code,
                        'company_name' => $userCompany->company->name ?? null,
                        'divisi_code' => $userCompany->divisi_code,
                        'divisi_name' => $userCompany->divisi->name ?? null,
                        'departemen_code' => $userCompany->departemen_code,
                        'departemen_name' => $userCompany->departemen->name ?? null
                    ],
                    'role'  => [
                        'id'    => $user->role->id,
                        'name'  => $user->role->name
                    ]
                ],

            ], 200);
        }
    }

    public function  userApprovalAdd(Request $request)
    {
        // Validasi data yang dikirim
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::where('name', 'user_approval')->first();

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role_id'   => $role->id,
        ]);

        $userdata = auth()->user();
        $userCompany = UserCompany::where('user_id', $userdata->id)->first();

        $userCompany = UserCompany::create([
            'user_id' => $user->id,
            'company_code' => $userCompany->company_code,
            'divisi_code' => $request->divisi_code,
            'departemen_code' => $request->departemen_code
        ]);

        if ($user) {
            $userCompany;
            if ($userCompany) {
                return response()->json([
                    "success" => true,
                    "message" => "User Approval berhasil ditambahkan",
                    "user" => [
                        "name" => $user->name,
                        "email" => $user->email,
                        "role" => $user->role->name,
                        'company' => [
                            'company_code' => $userCompany->company_code,
                            'company_name' => $userCompany->company->name ?? null,
                            'divisi_code' => $userCompany->divisi_code,
                            'divisi_name' => $userCompany->divisi->name ?? null,
                            'departemen_code' => $userCompany->departemen_code,
                            'departemen_name' => $userCompany->departemen->name ?? null
                        ],
                    ]
                ], 201);
            }
        }
    }

    public function userApprovalGet()
    {
        $userdata = auth()->user();
        $userCompany = UserCompany::where('user_id', $userdata->id)->first();
        $role = Role::where('name', 'user_approval')->first();

        $userApprovals = User::where('role_id', $role->id)
            ->whereHas('userCompanies', function ($query) use ($userCompany) {
                $query->where('company_code', $userCompany->company_code);
            })
            ->get();

        $formattedUserApprovals = [];
        foreach ($userApprovals as $userApproval) {
            $formattedUserApprovals[] = [
                "id" =>  $userApproval->id,
                "name" => $userApproval->name,
                "email" => $userApproval->email,
                "role" => $userApproval->role->name,
                'company' => [
                    'company_code' => $userApproval->userCompanies->first()->company_code,
                    'company_name' => $userApproval->userCompanies->first()->company->name ?? null,
                    'divisi_code' => $userApproval->userCompanies->first()->divisi_code,
                    'divisi_name' => $userApproval->userCompanies->first()->divisi->name ?? null,
                    'departemen_code' => $userApproval->userCompanies->first()->departemen_code,
                    'departemen_name' => $userApproval->userCompanies->first()->departemen->name ?? null
                ],
            ];
        }

        return response()->json([
            "success" => true,
            "userApproval" => $formattedUserApprovals
        ], 200);
    }

    public function userApprovalDelete($id)
    {
        $userApproval = User::find($id);

        if (!$userApproval) {
            return response()->json([
                "success" => false,
                "message" => "User approval not found."
            ], 404);
        }
        $userCompany = UserCompany::where('user_id', $userApproval->id)->first();

        // Hapus user approval
        if ($userApproval) {
            $userCompany->delete();
            $delete = $userApproval->delete();
            if ($delete) {
                return response()->json([
                    "success" => true,
                    "message" => "User approval deleted successfully."
                ], 200);
            }
        }
    }
    public function redirectToGoogleVendor()
    {
        config(['services.google.redirect' => env('GOOGLE_REDIRECT_URI_VENDOR')]);

        return Socialite::driver('google')->redirect();
    }

    // Handle Google callback for Vendor
    public function handleGoogleCallbackVendor()
    {
        return $this->handleGoogleCallback(config(['services.google.redirect' => env('GOOGLE_REDIRECT_URI_VENDOR')]),'vendor');
    }

    // Redirect to Google for Company
    public function redirectToGoogleCompany()
    {
        return Socialite::driver('google')
            ->with(['redirect_uri' => env('GOOGLE_REDIRECT_URI_COMPANY')])
            ->redirect();
    }

    // Handle Google callback for Company
    public function handleGoogleCallbackCompany()
    {
        return $this->handleGoogleCallback(config(['services.google.redirect' => env('GOOGLE_REDIRECT_URI_COMPANY')]),'company');
    }

    public function handleGoogleCallback($redirectUrl, $roleName)
    {
        $userFromGoogle = Socialite::driver('google')->user();;

        $role = Role::where('name', $roleName)->first();

        $userFromDatabase = User::where('google_id', $userFromGoogle->getId())->first();
        $role=$role->name;
        $password = 11111111;
        $frontendUrl = $this->getFrontendUrlFromHeader(request());

        if (!$userFromDatabase) {
            $newUser = new User([
                'google_id' => $userFromGoogle->getId(),
                'name' => $userFromGoogle->getName(),
                'email' => $userFromGoogle->getEmail(),
                'role_id' => $role->id,
                'password' => Hash::make($password),
            ]);

            $newUser->save();

            $token = JWTAuth::fromUser($newUser);

            $user_id = $newUser->id;

            // return response()->json(['token' => $token, 'token_type' => 'Bearer']);
            return redirect()->away('http://localhost:9000/auth/google/redirect?token=' . $token.'&role.name='.$role.'&user_id='.$user_id);
        }

        $token = JWTAuth::fromUser($userFromDatabase);
        $user_id = $userFromDatabase->id;

        return redirect()->away('http://localhost:9000/auth/google/redirect?token=' . $token.'&role.name='.$role.'&user_id='.$user_id);

        // return response()->json([
        //     'token' => $token,
        //     'token_type' => 'Bearer'
        // ]);
    }

    public function getFrontendUrlFromHeader($request) {
        $origin = $request->header('Origin');

        Return $origin;
    }
}
