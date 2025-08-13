<?php

namespace App\Http\Controllers\Api;

use App\Models\University;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Section;

class SectionController extends Controller
{
    public function getSectionsForUniversity()
    {
        $universityId = request(['university_id']);
        $university = University::find($universityId)->first();
        if (!$university) {
            return response()->json(['error' => 'University not found'], 404);
        }
        // dd($university);
        $sections = $university->sections()->select()->get();

        return response()->json($sections, 200);

    }
    public function getAllSections()
    {
        $imageUrls =[];
        $sections = Section::with('media')->get();
        if (count($sections) === 0) {
            // $sections is empty
            return response()->json(['message' => 'No sections found'], 404);
        }
        foreach ($sections as $section) {
            $media = $section->media->first();
            if(!$media)
            return response()->json(['message' => 'No sections found'], 404);
            $collection_name = $media->collection_name;
            if ($collection_name =="sections-images") {
                $imageUrls[] = [
                    'id' => $section?->id,
                    'name' => $section?->name,
                    'media_id'=>$media?->id,
                    'media_name'=>$media?->file_name,
                    'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->id ."/" .$media?->file_name,
                ];

            }

          }
        return response()->json($imageUrls, 200);
        // dd($sections);
    }
}