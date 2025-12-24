<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaFileRequest;
use App\Http\Requests\Media\UpdateMediaFileRequest;

use App\Models\MediaFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *   name="Media",
 *   description="Genel medya dosyası yönetimi"
 * )
 */
class MediaFileController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/media",
     *   tags={"Media Upload"},
     *   summary="Yeni medya dosyası yükle",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     content={
     *       @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *           type="object",
     *           required={"file","type"},
     *           @OA\Property(property="file", type="string", format="binary"),
     *           @OA\Property(property="type", type="string"),
     *           @OA\Property(property="file_name", type="string")
     *         )
     *       )
     *     }
     *   ),
     *
     *   @OA\Response(response=201, description="Medya başarıyla yüklendi")
     * )
     */
    public function store(StoreMediaFileRequest $request)
    {
        $user = auth()->user();
        $file = $request->file('file');

        // Klasör yapısı: media/{type}/yyyy/mm
        $directory = sprintf(
            'media/%s/%s/%s',
            $request->type,
            now()->format('Y'),
            now()->format('m')
        );

        $storedName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $storedName, 'public');

        $media = MediaFile::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'file_name' => $request->file_name,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Dosya başarıyla yüklendi.',
            'data' => $media
        ], 201);
    }



    /**
     * @OA\Put(
     *   path="/api/media/{media}",
     *   tags={"Media Upload"},
     *   summary="Medya dosyası adını güncelle",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="media",
     *     in="path",
     *     required=true,
     *     description="Media ID",
     *     @OA\Schema(type="integer")
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateMediaFileRequest")
     *   ),
     *
     *   @OA\Response(response=200, description="Medya güncellendi"),
     *   @OA\Response(response=403, description="Yetkiniz yok")
     * )
     */
    public function update(UpdateMediaFileRequest $request, MediaFile $media)
    {
        if ($media->user_id !== auth()->id()) {
            abort(403, 'Bu dosyayı güncelleme yetkiniz yok.');
        }

        $media->update([
            'file_name' => $request->file_name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Dosya bilgileri güncellendi.',
            'data' => $media
        ], 200);
    }


    /**
     * @OA\Get(
     *   path="/api/media/{media}",
     *   tags={"Media Upload"},
     *   summary="ID ile dosya bilgisi getir",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="media",
     *     in="path",
     *     required=true,
     *     description="Media ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Dosya bilgisi getirildi")
     * )
     */
    public function show(MediaFile $media)
    {
        return response()->json([
            'status' => true,
            'message' => 'Dosya bilgisi getirildi.',
            'data' => $media
        ], 200);
    }
    /**
     * @OA\Delete(
     *   path="/api/media/{media}",
     *   tags={"Media Upload"},
     *   summary="Dosyayı sil",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="media",
     *     in="path",
     *     required=true,
     *     description="Media ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Dosya silindi")
     * )
     */
    public function destroy(MediaFile $media)
    {
        if ($media->user_id !== auth()->id()) {
            abort(403, 'Bu dosyayı silme yetkiniz yok.');
        }

        // Fiziksel dosyayı sil
        Storage::disk('public')->delete($media->file_path);

        $media->delete();

        return response()->json([
            'status' => true,
            'message' => 'Dosya başarıyla silindi.'
        ], 200);
    }
}
