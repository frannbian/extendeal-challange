<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Api\V1\Paint;
use App\Http\Requests\Api\V1\{StorePaintRequest, UpdatePaintRequest, GetPaintsRequest}
;use App\Http\Resources\Api\V1\PaintResource;
use App\Repositories\V1\PaintRepository;
use Illuminate\Http\Request;
use Cache;
class PaintController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/paints",
     *      operationId="getPaintsList",
     *      tags={"Paints"},
     *      summary="Get list of paints",
     *      description="Returns list of paints",
     *      @OA\Parameter(
     *       name="X-HTTP-USER-ID",
     *       required=true,
     *       in="header",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *      ),
     *      @OA\Parameter(
     *       name="token",
     *       required=true,
     *       in="query",
     *       @OA\Schema(
     *           type="string",
     *       )
     *      ),
     *      @OA\Parameter(
     *          name="filters",
     *          in="query",
     *          description="Filters",
     *          required=false,
     *          @OA\Schema(
     *              ref="#/components/schemas/GetPaintsFiltersRequest"
     *          ),
     *       ),
     *      @OA\Parameter(
     *          name="fields",
     *          in="query",
     *          description="Fields",
     *          required=false,
     *          @OA\Schema(
     *              ref="#/components/schemas/GetPaintsFieldsRequest"
     *          ),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(GetPaintsRequest $request)
    {
        $cacheKey = md5(json_encode($request->validated()));
        $paints = Cache::tags(['paints'])->remember($cacheKey, 3600, function () use ($request) {
            return PaintRepository::get($request);
        });

        return $paints;
    }

    /**
     * @OA\Post(
     *      path="/api/v1/paints",
     *      operationId="storePaint",
     *      tags={"Paints"},
     *      summary="Store new paint",
     *      description="Returns paint data",
     *      @OA\Parameter(
     *       name="X-HTTP-USER-ID",
     *       required=true,
     *       in="header",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *      ),
     *      @OA\Parameter(
     *       name="token",
     *       required=true,
     *       in="query",
     *       @OA\Schema(
     *           type="string",
     *       )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StorePaintRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Paint")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(StorePaintRequest $request)
    {
        $paint = PaintRepository::save($request);

        Cache::tags('paints')->flush();

        return $paint
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/paints/{id}",
     *      operationId="getPaintById",
     *      tags={"Paints"},
     *      summary="Get paint information",
     *      description="Returns paint data",
     *      @OA\Parameter(
     *       name="X-HTTP-USER-ID",
     *       required=true,
     *       in="header",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *      ),
     *      @OA\Parameter(
     *       name="token",
     *       required=true,
     *       in="query",
     *       @OA\Schema(
     *           type="string",
     *       )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          description="Paint id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Paint")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show(Paint $paint)
    {
        return new PaintResource($paint->load(['painter', 'country']));
    }

    /**
     * @OA\Put(
     *      path="/api/v1/paints/{id}",
     *      operationId="updatePaint",
     *      tags={"Paints"},
     *      summary="Update existing paint",
     *      description="Returns updated paint data",
     *      @OA\Parameter(
     *       name="X-HTTP-USER-ID",
     *       required=true,
     *       in="header",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *      ),
     *      @OA\Parameter(
     *       name="token",
     *       required=true,
     *       in="query",
     *       @OA\Schema(
     *           type="string",
     *       )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          description="Paint id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StorePaintRequest")
     *      ),
     *      @OA\Response(
     *          response=202,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Paint")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function update(UpdatePaintRequest $request, Paint $paint)
    {
        $paint = PaintRepository::save($request, $paint->id);

        Cache::tags('paints')->flush();
        
        return $paint
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/paints/{id}",
     *      operationId="deletePaint",
     *      tags={"Paints"},
     *      summary="Delete existing paint",
     *      description="Deletes a record and returns no content",
     *      @OA\Parameter(
     *       name="X-HTTP-USER-ID",
     *       required=true,
     *       in="header",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *      ),
     *      @OA\Parameter(
     *       name="token",
     *       required=true,
     *       in="query",
     *       @OA\Schema(
     *           type="string",
     *       )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          description="Paint id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function destroy(Paint $paint)
    {
        $paint->delete();
        Cache::tags('paints')->flush();
        return response(null, 204);
    }
}
