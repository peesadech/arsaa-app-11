<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use Illuminate\Http\Request;

class PermissionTypeController extends Controller
{
    public function __construct()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/permission-types",
     *     summary="Get all permission types",
     *     tags={"Permission Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of permission types"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function index()
    {
        return response()->json(PermissionType::all());
    }

    /**
     * @OA\Post(
     *     path="/api/permission-types",
     *     summary="Create a new permission type",
     *     tags={"Permission Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"permissionType_name"},
     *                 @OA\Property(property="permissionType_name", type="string", example="System"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Permission type created"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/image/permissionTypes');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $image->move($destinationPath, $name);
            $data['permissionType_image_path'] = '/image/permissionTypes/' . $name;
        }

        $type = PermissionType::create($data);

        return response()->json($type, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/permission-types/{id}",
     *     summary="Update a permission type",
     *     tags={"Permission Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissionType_name"},
     *             @OA\Property(property="permissionType_name", type="string", example="System Update"),
     *             @OA\Property(property="permissionType_image_path", type="string", example="/image/permissionTypes/123.jpg")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Permission type updated"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function update(Request $request, $id)
    {
        $type = PermissionType::findOrFail($id);

        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name,' . $id . ',permissionType_id',
            'permissionType_image_path' => 'nullable|string',
        ]);

        $type->update($data);

        return response()->json($type);
    }

    /**
     * @OA\Delete(
     *     path="/api/permission-types/{id}",
     *     summary="Delete a permission type",
     *     tags={"Permission Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Permission type deleted"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function destroy($id)
    {
        $type = PermissionType::findOrFail($id);
        $type->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/permission-types/search/{keyword}",
     *     summary="Search permission types by name",
     *     tags={"Permission Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="path",
     *         required=true,
     *         description="Keyword to search in permission type name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="List of matched permission types"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function search($keyword)
    {
        $types = PermissionType::where('permissionType_name', 'like', '%' . $keyword . '%')->get();
        return response()->json($types);
    }
}
