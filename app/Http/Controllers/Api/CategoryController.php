<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function getList() {
        $data = Categories::all();
        return response()->json($data)
            ->header("Content-Type", "application/json; charset=utf8");
    }

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

    public function delete($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Delete the category
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function edit($id, Request $request)
    {
        $input = $request->all();
        $file = Categories::find($id);
        $image = $request->file("image");

        $manager = new ImageManager(new Driver());

        $newFileName = uniqid().".webp";
        $imageSave = $manager->read($image);

        $path = public_path("upload/".$newFileName);
            if (is_file($path)) {
                unlink($path);
            }
        $imageSave->toWebp()->save($path);

        $file->image=$newFileName;
        $file->name = $input['name'];
        $file->save();

        return response()->json($file);
    }

}
