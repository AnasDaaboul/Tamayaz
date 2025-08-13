<?php
namespace App\Services;

use App\Models\User;
use App\Models\Cobon;
use App\Models\Course;
use App\Models\Package;
use App\Models\Student;
use App\Models\Favorites;
use App\Models\CourseEnroll;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseEnrollService
{



    public function giveTeacherBalance($courseId)
    {
        $course = Course::find($courseId);
        $course->total_subs+=1;
        $course->save();
        $teachers = $course->teachers;
        foreach ($teachers as $teacher) {
        $teacher->balance =$teacher->balance + ($teacher->percentage * $course->price);
        $teacher ->save();
        }
    }

    public function getEnrolledCourses($studentId)
{

    $imageUrls =[];
    try{
        $student = Student::findOrFail($studentId);
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    $coursesEnrolled = $student->courses()->with('media')->get();

    $isEmpty = $coursesEnrolled->isEmpty();
    if ($isEmpty) {
        return response()->json(['message' => 'لا يوجد كورسات مضافة'], 404); // Handle no enrolled courses
    }
    foreach ($coursesEnrolled as $course) {
        $teacherId = $course->teachers->pluck('id')->first();
        $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $teacherId)->get();
        if (count($teacher) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
        $teacherName = $teacher->first()->first_name . " " . $teacher->first()->last_name;

        $media = $course->media->where("collection_name" , "courses-images");

        if(count($media) === 0)
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);


            $imageUrls[] = [
                'is_purchased' =>  1 ,
                "teacherName"   => $teacherName,
                "teacherId"=> $teacherId,
                'id' => $course->id,
                'course_name' => $course->name,
                'price' => $course->price,
                'total_subs' => $course->total_subs,
                'description' => $course->description,
                'media_id'=>$media?->first()['id'],
                'media_name'=>$media?->first()['file_name'],
                'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->first()['id'] ."/" .$media?->first()['file_name'],
                'number'=>$course->description,
            ];


      }
      return response()->json($imageUrls , 200);
}






//     public function checkCourseEnrolled($courseId , $studentId)
// {
//     $iconPath = null; // Initialize $iconPath to null
//     $videoPath = []; // Initialize $videoPath to an empty array

//     $student = Student::findOrFail($studentId);
//     $course = Course::find($courseId);
//     if(!$course)
//     return response()->json(['message' => 'Error: ' . "course not found"], 500);
//     $isEnrolled = $student->courses()->where('courses.id', $courseId)->exists();
//     $courseWithMedia = $course->getMedia("*");
//     foreach ($courseWithMedia as $courseMedia)
//         {
//          $videoMimeType = $courseMedia->collection_name;

//             if($videoMimeType == "courses-videos")
//             {
//                 $videoUrl = $courseMedia->getUrl();
//             $videoPath[] = [
//                 'file_id'=>$courseMedia['id'],
//                 'file_type'=>"video",
//                 'file_name'=>$courseMedia['file_name'],
//             ];
//         }
//             else if($videoMimeType == "courses-pdfs")
//             {
//                 $videoUrl = $courseMedia->getUrl();
//                 $videoPath[] = [
//                     'file_id'=>$courseMedia['id'],
//                     'file_type'=>"pdf",
//                     'file_name'=>$courseMedia['file_name'],
//                     'file_url'=>$videoUrl,
//                 ];
//             }
//             else if($videoMimeType == "courses-icons")
//             {
//                 $iconPath = "https://ac20cf.com/api/get-image/" . $courseMedia?->id . "/" . $courseMedia?->file_name;
//             }

//         }
//         if($isEnrolled)
//         {
//             $videoPath = array_map(function($file) {
//                 $file['active'] = 1;
//                 $file['icon'] = $iconPath;
//                 return $file;
//             }, $videoPath);

//             return response()->json($videoPath, 200);
//         }
//     else
//     {
//         $videoPath = array_map(function($file) {
//             $file['active'] = 0;
//             return $file;
//         }, $videoPath);

//         return response()->json($videoPath, 200);
//     }


// }

private function hasWatchedVideo($studentId, $mediaId)
{
    $watchHistory = \App\Models\VideoWatch::where('student_id', $studentId)
        ->where('media_id', $mediaId)
        ->first();

    return $watchHistory ? $watchHistory->watched : 0;
}
private function hasLikedVideo($studentId, $mediaId)
{
    $LikedVideo = \App\Models\StudentMediaLike::where('student_id', $studentId)
        ->where('media_id', $mediaId)
        ->where('liked' , true)
        ->first();

    return $LikedVideo ? $LikedVideo->liked : 0;
}
private function hasDisLikedVideo($studentId, $mediaId)
{
    $DisLikedVideo = \App\Models\StudentMediaLike::where('student_id', $studentId)
        ->where('media_id', $mediaId)
        ->where('disliked' , true)
        ->first();

    return $DisLikedVideo ? $DisLikedVideo->disliked : 0;
}


public function checkCourseEnrolled($courseId, $studentId)
{
    $videoPath = [];
   try{
        $student = Student::findOrFail($studentId);
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Student not found'], 404);
    }
    try{
       $course = Course::findOrFail($courseId);
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Course not found'], 404);
    }
    $teacherId = $course->teachers->pluck('id')->first();

    $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $teacherId)->get();
    if (count($teacher) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
    $teacherName = $teacher->first()->first_name . " " . $teacher->first()->last_name;
    $favorites = Favorites::where('student_id', $student->id)->where('favorateable_type' , 'App\Models\Course')->where('favorateable_id' , $courseId)->exists();


    $isEnrolled = $student->courses()->where('courses.id', $courseId)->exists();
    try{
        $courseWithMedia = $course->media->filter(function(Media $media){
            return in_array($media->collection_name , ['courses-images', 'courses-pdfs', 'courses-icons']);
        });
        $coursesVideosIds = $course->videos()->select('media_id')->orderBy('order')->get();
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Course not found'], 404);
    }
    // $courseWithMedia = $course->getMedia("*");

    if (count($courseWithMedia) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
    $iconPath=$courseWithMedia->where('collection_name' , 'courses-icons')->select('id' , 'file_name');
    if (count($iconPath) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }

    $freeVideo=$courseWithMedia->where('collection_name' , 'courses-free-video');
    if (count($courseWithMedia) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
    $videos = [];
    $pdfs = [];
    $freeVideos = [];

    foreach ($courseWithMedia as $courseMedia) {

        $videoMimeType = $courseMedia->collection_name;


         if ($videoMimeType == "courses-pdfs") {

            $pdfs[] = [
                'file_id' => $courseMedia['id'],
                'file_type' => "pdf",
                'file_name' => $courseMedia['file_name'],
                 "pdf_url"=>"https://ac20cf.com/api/get-image/" . $courseMedia?->id . "/" . $courseMedia?->file_name,
                 'active' => $isEnrolled ? 1 : 0,
            ];
        }
    }

        foreach ($coursesVideosIds as $mediaId) {
            $courseMedia = Media::find($mediaId->media_id);
            if($courseMedia->collection_name == "courses-free-video")
            {
                $videos[] = [
                    'file_id' => $courseMedia['id'],
                    'file_type' => "video",
                    'file_name' => $courseMedia['file_name'],
                    'icon_url' =>  "https://ac20cf.com/api/get-image/" . $iconPath?->first()['id'] . "/" . $iconPath?->first()['file_name'],
                    "video_name" => $courseMedia['name'],
                    "video_url" => "NAN",
                    'active' => 1,
                    'watched' => $this->hasWatchedVideo($studentId, $courseMedia['id']), // Add this line
                    'liked'=>$this->hasLikedVideo($studentId, $courseMedia['id']),
                    'disliked'=>$this->hasDisLikedVideo($studentId, $courseMedia['id']),
                ];

            }
            else
            {

            $videos[] = [
                'file_id' => $courseMedia['id'],
                'file_type' => "video",
                'file_name' => $courseMedia['file_name'],
                'icon_url' =>  "https://ac20cf.com/api/get-image/" . $iconPath?->first()['id'] . "/" . $iconPath?->first()['file_name'],
                "video_name" => $courseMedia['name'],
                "video_url" => "NAN",
                'active' => $isEnrolled ? 1 : 0,
                'watched' => $this->hasWatchedVideo($studentId, $courseMedia['id']), // Add this line
                'liked'=>$this->hasLikedVideo($studentId, $courseMedia['id']),
                'disliked'=>$this->hasDisLikedVideo($studentId, $courseMedia['id']),
            ];
        }
}
    // $videos = array_merge([
    //     [
    //         $freeVideos,
    //     ]
    // ], $videos);
    $videos = array_merge($freeVideos, $videos);
    return response()->json([
        'is_purchased' => $isEnrolled ? 1 : 0,
        'teacherName' =>$teacherName,
        'teacherId'=>$teacherId,
        'favorites'=>$favorites,
        'videos' => $videos,
        'pdfs' => $pdfs,
    ], 200);

}


    public function purchaseCourseWithcourseId($courseId , $cobon , $studentId)
    {

        try{
            $student = Student::findOrFail($studentId);
         }
         catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Student not found'], 404);
         }
         try{
            $course = Course::findOrFail($courseId);
         }
         catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Student not found'], 404);
         }




        $cobon = Cobon::where('name' , $cobon)->firstOrFail();
        $checkType = ($cobon['cobonable_type'] === "App\Models\Course");

        if($cobon['cobonable_id'] == $courseId && $cobon['active'] == 0  && $checkType)
        {
            $isEnrolled = $student->courses()->where('courses.id', $courseId)->exists();
            if(!$isEnrolled)
            {
                $coursesEnrolled = CourseEnroll::create([
                    'cobon_id'=>$cobon['id'],
                    'student_id'=>$studentId,
                    'course_id'=>$courseId,
                ]);
                $cobon->active=1; $cobon->save();
                $this->giveTeacherBalance($courseId);
                return response()->json(['course purchased successfuly'] , 200);

            }
            else return response()->json(['this course is purchased before'] , 201);
        }
        else return response()->json(['invalid cobon'] , 201);

    }

    public function purchaseCourseWithpackageId($packageId , $cobon , $studentId)
    {
        try{
            $student = Student::findOrFail($studentId);
         }
         catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Student not found'], 404);
         }
         try{
            $package = Package::findOrFail($packageId);
         }
         catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Package not found'], 404);
         }




        $packageCourses = $package->courses()->get();

        $cobon = Cobon::where('name' , $cobon)->firstOrFail();
        $checkType = ($cobon['cobonable_type'] === "App\Models\Package");

        if($cobon['cobonable_id'] == $packageId && $cobon['active'] == 0) //check if the enterd cobon matches the choosed package
        {
            $isEnrolledPackage = $student->packages()->where('packages.id', $packageId)->exists(); //return true if the package purchased before


            if($isEnrolledPackage) return response()->json(['on of the package courses is purchased before'] , 201);


            foreach($packageCourses as $packageCourse)      // to check if the user purchased one of the package courses before
            {


            $isEnrolledCorses = $student->courses()->where('courses.id' , $packageCourse['id'])->exists();
                if($isEnrolledCorses)
                break;
            }

            if($isEnrolledCorses)
            {
                return response()->json(["Can't activate this package because you purchased one of it's courses"] , 201);
            }


          else if(!$isEnrolledPackage)
            {
             foreach($packageCourses as $packageCourse)
             {
                $this->giveTeacherBalance($packageCourse->id);

                $coursesEnrolled = CourseEnroll::create([
                    'cobon_id'=>$cobon['id'],
                    'student_id'=>$studentId,
                    'course_id'=>$packageCourse['id'],
                ]);

             }


                $cobon->active=1; $cobon->save();
                return response()->json(['package purchased successfuly'] , 200);
            }
            else return response()->json(['this package is purchased before'] , 201);
        }
        else return response()->json(['invalid cobon'] , 201);

    }

    public function purchaseCourseWithOutcourseId($cobon , $studentId)
    {
        $cobon = Cobon::where('name' , $cobon)->firstOrFail();
        $cobonableId = $cobon['cobonable_id'];
        $cobonType = $cobon['cobonable_type'];
        // dd($cobonType);
        if($cobonType === "App\Models\Package")
        {
            return $this->purchaseCourseWithpackageId($cobonableId , $cobon['name'] , $studentId);
        }
        else if($cobonType === "App\Models\Course")
        {

            return $this->purchaseCourseWithcourseId($cobonableId , $cobon['name'] , $studentId);
        }

        else return response()->json(['invalid cobon'] , 201);
    }
}