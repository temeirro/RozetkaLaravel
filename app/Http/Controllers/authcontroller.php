<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class authcontroller extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/login",
     *   tags={"Auth"},
     *   summary="Login",
     *   operationId="login",
     *   @OA\RequestBody(
     *     required=true,
     *     description="User login data",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         required={"email", "password"},
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="password", type="string"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json"
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *   )
     * )
     */
    public function login(Request $request) {
        $validation = Validator::make($request->all(),[
            'email'=> 'required|email',
            'password'=> 'required|string|min:6'
        ], [
            'email.required' => 'Пошта є побов\'язковим.',
            'email.email' => 'Пошта є невалідною.',
            'password.required' => 'Пароль не може буть пустим.',
            'password.min' => 'Довжина пароля має бути мінімум 6 символів.',
        ]);
        if($validation->fails()) {
            return response()->json($validation->errors(), Response::HTTP_BAD_REQUEST);
        }
        if(!$token = auth()->attempt($validation->validated())) {
            return response()->json(['error'=>'Дані вказано не вірно'], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json(['token'=>$token], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/api/register",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "lastName", "name", "phone", "image", "password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="lastName",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    public function register(Request $request) {

        $validation = Validator::make($request->all(),[
            'name'=> 'required|string',
            'lastName'=> 'required|string',
            'image'=> 'required|string',
            'phone'=> 'required|string',
            'email'=> 'required|string',
            'password'=> 'required|string|min:6',
        ]);

        if($validation->fails()) {
            return response()->json($validation->errors(), Response::HTTP_BAD_REQUEST);
        }

        $input = $request->all();
        $imageName = uniqid().".webp";
        // create image manager with desired driver
        $manager = new ImageManager(new Driver());
            $imageRead = $manager->read($input["image"]);
            $path=public_path('upload/'.$imageName);
            $imageRead->toWebp()->save($path);

        $user = User::create(array_merge(
            $validation->validated(),
            ['password' => bcrypt($request->password), 'image'=> $imageName]
        ));
        return response()->json(['token'=>$user], Response::HTTP_OK);
    }

    public function getUserImageNameByEmail(Request $request) {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['image_name' => $user->image], Response::HTTP_OK);
    }



}
