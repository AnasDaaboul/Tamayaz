<?php
namespace App\Services;

use App\Models\Course;

class CourseService
{
    public function createCourse($validatedData)
    {
        $course = Course::create([
            'name'=>$validatedData['name'],
            'price'=>$validatedData['price'],
            'total_subs'=>$validatedData['total_subs'],
            'description'=>$validatedData['description'],
        ]);
        // dd($validatedData);
        $course->teachers()->attach($validatedData['teachers']);
        $course->sections()->attach($validatedData['sections']);
        $course->addMedia($validatedData['image'])
        ->toMediaCollection('courses-images' , 'media');
            return response()->json(['course has been added'],201);
    }



    public function addFilesCourses($file , $courseId)
    {

        $course=Course::findOrFail($courseId);

        $mimeType = $file->getMimeType();
        if (strpos($mimeType, 'application/pdf') !== false) {
            $course->addMedia($file)
            ->toMediaCollection('courses-pdfs' , 'media');
            return response()->json(['pdf has been added'],201);
        } elseif (strpos($mimeType, 'video/') === 0) {
            $course->addMedia($file)
            ->toMediaCollection('courses-videos' , 'media');
            return response()->json(['video has been added'],201);

    }
    return response()->json(['please check the data'],201);

    }
}