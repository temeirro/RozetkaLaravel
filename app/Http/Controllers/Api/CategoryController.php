<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    public function getList() {
        $data = Categories::all();
        return response()->json($data)
            ->header("Content-Type", "application/json; charset=utf8");
    }

    public function getById($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category)
            ->header('Content-Type', 'application/json; charset=utf8');
    }
    public function getByName($name)
    {
        $category = Categories::where('name', $name)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category)
            ->header('Content-Type', 'application/json; charset=utf8');
    }


    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */

    public function create(Request $request) {
    $input = $request->all();
    $image = $request->file("image");
    // create image manager with desired driver
    $manager = new ImageManager(new Driver());
    $imageName=uniqid().".webp";


        $imageSave = $manager->read($image);
        // resize image proportionally to 600px width
        $path = public_path("upload/".$imageName);
        // save modified image in new format
        $imageSave->toWebp()->save($path);

    $input["image"]=$imageName;
    $category = Categories::create($input);
    return response()->json($category,201,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішне видалення категорії"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категорії не знайдено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизований"
     *     )
     * )
     */
    public function delete($id)
    {
        // Find the category
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Retrieve the image file path

        $imagePath = public_path("upload/".$category->image); // Assuming the image path is relative to the public directory

        // Delete the category record
        $category->delete();

        // Delete the associated image from the upload folder
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath);
        }

        return response()->json(['message' => 'Category and image deleted successfully']);
    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    public function edit($id, Request $request) {
        $category = Categories::findOrFail($id);
        $imageName=$category->image;
        $inputs = $request->all();
        if($request->hasFile("image")) {
            $image = $request->file("image");
            $imageName = uniqid() . ".webp";
            // create image manager with desired driver
            $manager = new ImageManager(new Driver());
                $imageRead = $manager->read($image);
                $path = public_path('upload/' . $imageName);
                $imageRead->toWebp()->save($path);
                $removeImage = public_path('upload/'. $category->image);
                if(file_exists($removeImage))
                    unlink($removeImage);

        }
        $inputs["image"]= $imageName;
        $category->update($inputs);
        return response()->json($category,200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

}
