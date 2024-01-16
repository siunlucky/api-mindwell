<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeditationRequest;
use App\Http\Requests\UpdateMeditationRequest;
use Illuminate\Support\Facades\Cache;

class MeditationController extends Controller
{
    protected $collection;

    public function __construct()
    {
        parent::__construct();

        $this->collection = $this->database->collection('meditations');
    }

    public function index()
    {
        try {
            $datas = Cache::remember('meditations', 60, function () {
                $documents = $this->collection->documents();
                $meditations = [];

                foreach ($documents as $document) {
                    $meditation = $document->data();
                    $meditation['id'] = $document->id();
                    $meditations[] = $meditation;
                }

                return $meditations;
            });

            return response()->json([
                'status' => "success",
                'data' => $datas
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => "Internal Server Error : " . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMeditationRequest $request)
    {
        try {
            $stuRef = $this->collection->newDocument();

            $idDoc = $stuRef->id();
            $thumbnail = $request->file('thumbnail');
            $video = $request->file('video');
            $localfolder = public_path('firebase-temp-uploads') . "/";

            $thumbnail_firebase_storage_path = "Meditations/Thumbnail/";
            $thumbnail_extension = $thumbnail->getClientOriginalExtension();
            $thumbnail_file = $idDoc . '.' . $thumbnail_extension;
            if ($thumbnail->move($localfolder, $thumbnail_file)) {
                $thumbnail_uploadedfile = fopen($localfolder . $thumbnail_file, 'r');
                $this->storage->upload($thumbnail_uploadedfile, ['name' => $thumbnail_firebase_storage_path . $thumbnail_file]);
                unlink($localfolder . $thumbnail_file);
            }


            $video_firebase_storage_path = "Meditations/Video/";
            $video_extension = $video->getClientOriginalExtension();
            $video_file = $idDoc . '.' . $video_extension;
            if ($video->move($localfolder, $video_file)) {
                $video_uploadedfile = fopen($localfolder . $video_file, 'r');
                $this->storage->upload($video_uploadedfile, ['name' => $video_firebase_storage_path . $video_file]);
                unlink($localfolder . $video_file);
            }

            $thumbnailLink = $thumbnail_firebase_storage_path . $thumbnail_file;
            $videoLink = $video_firebase_storage_path . $video_file;

            $stuRef->set([
                'title' => $request->title,
                'description' => $request->description,
                'videoLink' => $videoLink,
                'thumbnailLink' => $thumbnailLink,
                'meditationType' => $request->meditationType,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);

            $data = $stuRef->snapshot()->data();
            $data['id'] = $idDoc;

            return response()->json([
                'status' => "success",
                'message' => "data inserted successfuly",
                'data' => $data
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => "Internal Server Error : " . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $document = $this->collection->document($id)->snapshot();

            if (!$document->exists()) {
                return response()->json([
                    'status' => "fail",
                    'message' => "Data not found",
                    'data' => null
                ], 404);
            }

            $data = $document->data();
            $data['id'] = $document->id();

            return response()->json([
                'status' => "success",
                'data' => $data
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => "Internal Server Error : " . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeditationRequest $request, string $id)
    {
        try {
            $stuRef = $this->collection->document($id);
            $document = $stuRef->snapshot()->data();

            if (!$stuRef->snapshot()->exists()) {
                return response()->json([
                    'status' => "fail",
                    'message' => "Data not found",
                    'data' => null
                ], 404);
            }

            $localfolder = public_path('firebase-temp-uploads') . "/";

            if ($request->hasFile('thumbnail')) {
                if ($this->storage->object($document['thumbnailLink'])->exists()) {
                    $this->storage->object($document['thumbnailLink'])->delete();
                }

                $thumbnail = $request->file('thumbnail');

                $thumbnail_firebase_storage_path = "Meditations/Thumbnail/";
                $thumbnail_extension = $thumbnail->getClientOriginalExtension();
                $thumbnail_file = $id . '.' . $thumbnail_extension;
                if ($thumbnail->move($localfolder, $thumbnail_file)) {
                    $thumbnail_uploadedfile = fopen($localfolder . $thumbnail_file, 'r');
                    $this->storage->upload($thumbnail_uploadedfile, ['name' => $thumbnail_firebase_storage_path . $thumbnail_file]);
                    unlink($localfolder . $thumbnail_file);
                }

                $thumbnailLink = $thumbnail_firebase_storage_path . $thumbnail_file;
            }

            if ($request->hasFile('video')) {
                if ($this->storage->object($document['videoLink'])->exists()) {
                    $this->storage->object($document['videoLink'])->delete();
                }

                $video = $request->file('video');

                $video_firebase_storage_path = "Meditations/Video/";
                $video_extension = $video->getClientOriginalExtension();
                $video_file = $id . '.' . $video_extension;
                if ($video->move($localfolder, $video_file)) {
                    $video_uploadedfile = fopen($localfolder . $video_file, 'r');
                    $this->storage->upload($video_uploadedfile, ['name' => $video_firebase_storage_path . $video_file]);
                    unlink($localfolder . $video_file);
                }

                $videoLink = $video_firebase_storage_path . $video_file;
            }

            $stuRef->set([
                'title' => $request->title ?? $document['title'],
                'description' => $request->description ?? $document['description'],
                'videoLink' => $videoLink ?? $document['videoLink'],
                'thumbnailLink' => $thumbnailLink ?? $document['thumbnailLink'],
                'meditationType' => $request->meditationType ?? $document['meditationType'],
                'updatedAt' => Carbon::now(),
            ], ['merge' => true]);

            $data = $stuRef->snapshot()->data();
            $data['id'] = $stuRef->id();

            return response()->json([
                'status' => "success",
                'data' => $data
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => "Internal Server Error : " . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = $this->collection->document($id);
            $documentData = $document->snapshot()->data();

            $document->delete();

            if ($documentData['videoLink']) {
                $this->storage->object($documentData['videoLink'])->delete();
            }

            if ($documentData['thumbnailLink']) {
                $this->storage->object($documentData['thumbnailLink'])->delete();
            }

            return response()->json([
                'status' => "success",
                'data' => $documentData
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "error",
                'message' => "Internal Server Error : " . $th->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
