<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;

/**
 * @OA\Tag(
 *   name="Branches",
 *   description="Branch (branş) CRUD operations"
 * )
 *
 * @OA\Schema(
 *   schema="Branch",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Matematik"),
 *   @OA\Property(property="slug", type="string", example="matematik"),
 *   @OA\Property(property="code", type="string", example="MATH"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="color", type="string", example="#1E90FF", nullable=true),
 *   @OA\Property(property="icon", type="string", example="calculator", nullable=true),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */
class BranchController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/branches",
     *   tags={"Branches"},
     *   summary="List branches",
     *   @OA\Parameter(name="q", in="query", description="Search by name or code", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="is_active", in="query", description="Filter by active state", required=false, @OA\Schema(type="boolean")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Branch"))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->query('is_active'))) {
            $query->where('is_active', (bool) $request->query('is_active'));
        }

        return response()->json($query->latest()->paginate(20));
    }
    /**
     * @OA\Post(
     *     path="/api/branches",
     *     tags={"Branches"},
     *     summary="Yeni branş oluştur",
     *     description="Sisteme yeni bir branş ekler.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Oluşturulacak branş verileri",
     *         @OA\JsonContent(ref="#/components/schemas/BranchStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Branş başarıyla oluşturuldu",
     *         @OA\JsonContent(ref="#/components/schemas/Branch")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Doğrulama hatası"
     *     )
     * )
     */

    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());
        return response()->json($branch, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/branches/{id}",
     *   tags={"Branches"},
     *   summary="Get a branch",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Branch")),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Branch $branch)
    {
        return response()->json($branch);
    }

    /**
     * @OA\Put(
     *   path="/api/branches/{id}",
     *   tags={"Branches"},
     *   summary="Update branch",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BranchUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Branch")),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());
        return response()->json($branch);
    }

    /**
     * @OA\Delete(
     *   path="/api/branches/{id}",
     *   tags={"Branches"},
     *   summary="Delete branch",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="No Content"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return response()->noContent();
    }
}
