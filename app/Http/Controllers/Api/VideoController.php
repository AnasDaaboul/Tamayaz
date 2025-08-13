<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;


class VideoController extends Controller
{
    public function streamVideo($id, $name, Request $request)
    {
        $media = Media::findOrFail($id);
        
        if ($media->file_name !== $name) {
            return response()->json(['error' => 'File name does not match'], 422);
        }
        
        $path = storage_path("app/media/$id/$name");
        $fileSize = filesize($path);
    $start = 0;
    $length = $fileSize;
    $end = $fileSize - 1;
    if ($request->headers->has('Range')) {
        $range = $request->header('Range');
        preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);

        // Extract start and end from the Range header
        $start = intval($matches[1]);
        if (isset($matches[2])) {
            $end = intval($matches[2]);
        }
        $length = $end - $start + 1;
    }
        
        // $stream = new \App\Http\VideoStream($path);

        // return response()->stream(function() use ($stream) {
        //     $stream->start();
        // });
        $stream = new StreamedResponse(function () use ($path, $start, $length) {
        $video = fopen($path, 'rb');
        fseek($video, $start);
        echo fread($video, $length);
        fclose($video);
    });
    

    // Set the necessary headers for video streaming
    $stream->setStatusCode($request->headers->has('Range') ? 206 : 200);
    $stream->headers->set('Content-Type', 'video/mp4');
    $stream->headers->set('Accept-Ranges', 'bytes');
    $stream->headers->set('Content-Length', $length);
    $stream->headers->set('Content-Range', "bytes $start-$end/$fileSize");

    return $stream;
        
        
        
    }
    
    
    
    


// public function streamVideo($id, $name)
// {
//     $media = Media::find($id);
//     if (!$media) {
//         return response()->json(['error' => 'Media not found'], 404);
//     }

//     $path = "media/$id/$name";
//     if (!Storage::exists($path)) {
//         return response()->json(['error' => 'File not found'], 404);
//     }

//     $pathToFile = Storage::path($path);
//     $mimeType = $media->mime_type; // Ensure this is correct (e.g., 'video/mp4')
//     $size = $media->size;

//     // Default to full content
//     $start = 0;
//     $end = $size - 1;
//     $length = $size;
//     $statusCode = 200;
//     $headers = [
//         'Content-Type' => $mimeType,
//         'Content-Length' => $length,
//         'Accept-Ranges' => 'bytes',
//         'Cache-Control' => 'no-store, no-cache, must-revalidate',
//         'Pragma' => 'no-cache',
//         'Expires' => '0',
//         'Content-Disposition' => 'inline',
//         'Access-Control-Allow-Origin' => '*',
//         'Access-Control-Expose-Headers' => 'Content-Range, Accept-Ranges',
//     ];

//     // Handle partial content (Range requests)
//     if (request()->headers->has('Range')) {
//         $range = request()->header('Range');
//         list($start, $end) = $this->parseRangeHeader($range, $size);
//         $length = $end - $start + 1;
//         $statusCode = 206;
//         $headers['Content-Range'] = "bytes $start-$end/$size";
//         $headers['Content-Length'] = $length;
//     }

//     $stream = fopen($pathToFile, 'rb');
//     fseek($stream, $start);

//     return response()->stream(function () use ($stream, $length) {
//         $remaining = $length;
//         $chunkSize = 8192; // 8KB chunks

//         while (!feof($stream) && $remaining > 0) {
//             $read = $remaining > $chunkSize ? $chunkSize : $remaining;
//             echo fread($stream, $read);
//             $remaining -= $read;
//             flush(); // Send data to client immediately
//         }
//         fclose($stream);
//     }, $statusCode, $headers);
// }

// protected function parseRangeHeader($rangeHeader, $fileSize)
// {
//     $range = substr($rangeHeader, 6); // Strip 'bytes=' prefix
//     list($start, $end) = explode('-', $range, 2);

//     $start = intval($start);
//     $end = $end === '' ? $fileSize - 1 : intval($end);

//     // Validate bounds
//     $end = min($end, $fileSize - 1);
//     $start = max($start, 0);

//     return [$start, $end];
// }




































// public function streamVideo($id, $name)
// {
//     $media = Media::find($id);

//     if (!$media) {
//         return response()->json(['error' => 'Media not found'], 404);
//     }

//     $path = "media/$id/$name";
//     $pathToFile = Storage::path($path);

//     if (!Storage::exists($path)) {
//         return response()->json(['error' => 'File not found'], 404);
//     }

//     $mimeType = $media->mime_type; // Ensure correct MIME type
//     $size = filesize($pathToFile);
//     $start = 0;
//     $end = $size - 1;

//     // Handle Range header
//     if (request()->headers->has('Range')) {
//         $range = request()->header('Range');
//         preg_match('/bytes=(\d*)-(\d*)/', $range, $matches);
//         $start = isset($matches[1]) && $matches[1] !== '' ? intval($matches[1]) : $start;
//         $end = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : $end;
//     }

//     $length = $end - $start + 1;

//     // Open file stream
//     $stream = fopen($pathToFile, 'rb');
//     fseek($stream, $start);

//     return response()->stream(function () use ($stream, $length) {
//         echo fread($stream, $length);
//         fclose($stream);
//     }, 206
//     // , [
//     //     'Content-Type' => $mimeType,
//     //     'Content-Length' => $length,
//     //     'Content-Range' => "bytes $start-$end/$size",
//     //     'Accept-Ranges' => 'bytes',
//     //     'Cache-Control' => 'no-store, no-cache, must-revalidate',
//     //     'Pragma' => 'no-cache',
//     //     'Expires' => '0',
//     //     'Content-Disposition' => 'inline',
//     //     'Access-Control-Allow-Origin' => '*',
//     //     'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
//     //     'Access-Control-Allow-Headers' => 'Range, Content-Type, Content-Range, Accept-Ranges',
//     //     'Access-Control-Expose-Headers' => 'Content-Range, Accept-Ranges',
//     // ]
//     );
// }

 }