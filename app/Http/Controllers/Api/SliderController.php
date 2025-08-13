<?php

namespace App\Http\Controllers\Api;
use App\Models\Slider;

use App\Http\Controllers\Controller;

class SliderController extends Controller
{
    // public function index()
    // {
    //     $sliders = Slider::with('media')
    //         ->get();
    //     return response()->json([
    //         'success' => true,
    //         'data' => $sliders
    //     ]);
    // }
    public function index()
{
    $sliders = Slider::with('media')->get()->map(function ($slider) {
        $sliderArray = $slider->toArray();
        $sliderArray['media'] = $slider->media->map(function ($media) {
            return [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $media?->id . "/" . $media?->file_name,
            ];
        })->toArray();
        return $sliderArray;
    });

    return response()->json([
        'success' => true,
        'data' => $sliders
    ]);
}

}