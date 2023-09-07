<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
class UserController extends Controller
{
    public function register(Request $request): Response
    {

        $validator = Validator::make($request->all(), [
            'company_name'                  => 'required',
            'compay_location'               => 'required',
            'category_id'                   => 'required',
            'agent_count'                   => 'required',
            'company_email'                 => 'required|email',
            'company_phone'                 => 'required',
            'email'                         => 'required|email',
            'first_name'                    => 'required',
            'last_name'                     => 'required',
            'password'                      => 'required',
        ]);

        if($validator->fails()){

            return Response(
                [
                    'response' => [
                        'response_id' => 1,
                        'response_status' => 403,
                        'response_desc' => $validator->errors()->first()
                    ]
                ], 403);
        }
        $company = Company::query()->where('company_email', $request->company_email)->first();
        if ($company)
        {
            return Response(
                [
                    'response' => [
                        'response_id' => 1,
                        'response_status' => 409,
                        'response_desc' => "Company already registered."
                    ]
                ], 409);
        }

        $user = User::query()->where('email', $request->email)->first();
        if ($user)
        {
            return Response(
                [
                    'response' => [
                        'response_id' => 1,
                        'response_status' => 409,
                        'response_desc' => "User already registered."
                    ]
                ], 409);
        }

            DB::beginTransaction();
            try {
                $user                       = new  User();
                $user->first_name           = $request->first_name;
                $user->last_name            = $request->last_name;
                $user->role                 = User::IS_ADMIN;
                $user->email                = $request->email;
                $user->password             = Hash::make($request->password);
                if ($user->save())
                {
                    $company                        = new Company();
                    $company->user_id               = $user->id;
                    $company->company_name          = $request->company_name;
                    $company->compay_location       = $request->compay_location;
                    $company->category_id           = $request->category_id;
                    $company->agent_count           = $request->agent_count;
                    $company->company_email         = $request->company_email;
                    $company->company_phone         = $request->company_phone;
                    $company->save();

                    User::query()->where('id', $user->id)->update(['company_id' => $company->id]);
                }
                DB::commit();
                return Response([
                    'response' => [
                        'response_id' => 0,
                        'response_status' => 200,
                        'response_desc' => "Success"
                    ]
                ], 200);

            }catch (Exception $e) {
                DB::rollback();
                return Response(
                    [
                        'response' => [
                            'response_id' => 1,
                            'response_status' => 500,
                            'response_desc' => "Internal Server Error.",
                            'exception' => $e->getMessage()
                        ],
                    ],500);
            }

    }

    public function login(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){

            return Response(
                [
                    'response' => [
                        'response_id' => 1,
                        'response_status' => 403 ,
                        'response_desc' => $validator->errors()->first()
                    ]
                ], 403 );
        }
        $user = User::query()->where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return Response(
                [
                    'response' => [
                        'response_id' => 1,
                        'response_status' => 403,
                        'response_desc' => "Invalid Credentials."
                    ]
                ], 403 );
        }
        else {
            DB::beginTransaction();
            try {
                if ($user->tokens()->count() > 0) {
                    $user->tokens()->delete();
                }
                $token = $user->createToken("API TOKEN")->plainTextToken;
                DB::commit();
                return Response(
                    [
                        'response' => [
                            'response_id' => 0,
                            'response_status' => 200,
                            'response_desc' => 'success'
                        ],
                        'result' => compact('token', 'user')
                    ],200);
            }catch (Exception $e) {
                DB::rollback();
                return Response(
                    [
                        'response' => [
                            'response_id' => 1,
                            'response_status' => 500,
                            'response_desc' => "Internal Server Error.",
                            'exception' => $e->getMessage()
                        ],
                    ],500);
            }
        }
    }
}
