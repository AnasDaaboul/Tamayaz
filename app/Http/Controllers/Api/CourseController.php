<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\CourseTeacher;
use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Http\Requests\AddVideoRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService )
    {

    }
    // public function createCourse(CourseRequest $request)
    // {
    //     $validatedData = $request->validated();
    //     // dD($validatedData);
    //     return $this->courseService->createCourse($validatedData);
    // }
    // public function addVideos(AddVideoRequest $request)
    // {
    //     $validatedData = $request->validated();
    //     // dd($validatedData);
    //     return $this->courseService->addFilesCourses($validatedData['file'] , $validatedData['id']);

    // }
    // public function getAllCourses(Request $request)
    // {
    //     $user = auth()->user();
    //     try{
    //         $student = Student::with('university', 'section')->findOrFail($user['userable_id']);
    //     }
    //     catch (ModelNotFoundException $e) {
    //         return response()->json(['message' => 'Student not found'], 404);
    //     }


    //     $section = $student->section;
    //     $university = $student->university;
    //     $sectionCourses = Course::whereHas('sections', function ($query) use ($section, $university) {
    //         $query->where('sections.id', $section->id)
    //               ->whereHas('universities', function ($query) use ($university) {
    //                   $query->where('universities.id', $university->id);
    //               });
    //     })->with('media')->get();
    //     $otherCourses = Course::whereDoesntHave('sections', function ($query) use ($section, $university) {
    //         $query->where('sections.id', $section->id)
    //               ->whereHas('universities', function ($query) use ($university) {
    //                   $query->where('universities.id', $university->id);
    //               });
    //     })->with('media')->get();


    //     $perPage = 5;
    //     $page = $request->input('page', 1);
    //     $courses = $sectionCourses->merge($otherCourses);
    //     $paginatedCourses = $this->paginate($courses, $perPage, $page);
    //     $coursesEnrolled = $student->courses()->with('media')->get();

    //      $imageUrls = [];
    //     foreach($paginatedCourses as $course)
    //     {
    //         $teacherId = $course->teachers->select("id");
    //         $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $teacherId)->get();
    //         $teacherName = $teacher->first()->first_name . " " . $teacher->first()->last_name;
    //         $media = $course->media->where('collection_name' , 'courses-images')->select('id' , 'file_name');
    //         if(count($media) === 0)
    //         return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
    //         $isPurchased = $coursesEnrolled->contains($course->id);
    //         $imageUrls[] = [
    //             'is_purchased' => $isPurchased ? 1 : 0,
    //             "teacherName"   => $teacherName,
    //             'id' => $course->id,
    //             'course_name' => $course->name,
    //             'description' => $course->description,
    //             'price' => $course->price,
    //             'total_subs'=>$course->total_subs,
    //             'media_id'=>$media?->first()['id'],
    //             'media_name'=>$media?->first()['file_name'],
    //             'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->first()['id'] ."/" .$media?->first()['file_name'],
    //         ];
    //     }
    //     return response()->json([
    //         'data' => $imageUrls,
    //         'pagination' => [
    //             'total' => count($courses),
    //             'per_page' => $perPage,
    //             'current_page' => $page,
    //             'last_page' => ceil(count($courses) / $perPage),
    //             'from' => ($page - 1) * $perPage + 1,
    //             'to' => min($page * $perPage, count($courses)),
    //         ],
    //     ]);

    // }
 public function getAllCourses(Request $request)
{
    // Get authenticated user (if any)
    $user = auth()->user();
    $student = null;
    $purchasedCourseTeacherIds = [];
    
    // If user is logged in, get their purchased course_teacher_ids
    if ($user) {
        $student = Student::find($user->userable_id);
        if ($student) {
            $purchasedCourseTeacherIds = $student->courseTeachers()
                ->pluck('course_teacher_id')
                ->toArray();
        }
    }

    // Retrieve all courses with their media
    $courses = Course::with('media')->get();
    $imageUrls = [];
    foreach ($courses as $course) {
        $media = $course->media->where('collection_name', 'courses-images')->select('id', 'file_name');

        if (count($media) === 0) {
            return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
        
        // Get all course_teacher_ids related to this course
        $courseCourseTeacherIds = CourseTeacher::where('course_id', $course->id)
            ->pluck('id')
            ->toArray();
            
        // Check if user has purchased any teacher for this course
        $locked = 1; // Default to locked
        
        if ($user && $student) {
            // Check if there's any intersection between purchased and course teacher IDs
            $hasMatchingTeacher = !empty(array_intersect($purchasedCourseTeacherIds, $courseCourseTeacherIds));
            $locked = $hasMatchingTeacher ? 0 : 1;
        }

        $imageUrls[] = [
            "teacherName" => "teacherName",
            'id' => $course->id,
            'course_name' => $course->name,
            'description' => $course->description,
            'price' => $course->price,
            'total_subs' => $course->total_subs,
            'media_id' => $media?->first()['id'],
            'media_name' => $media?->first()['file_name'],
            'image_url' => "https://backend1.tamayaz.tech/api/get-image/" . $media?->first()['id'] . "/" . $media?->first()['file_name'],
            'locked' => $locked, // Add the locked variable
        ];
    }

    return response()->json([
        'data' => $imageUrls,
    ]);
}
    private function paginate($items, $perPage, $page)
{
    $offset = ($page - 1) * $perPage;
    return $items->slice($offset, $perPage);
}




    public function search(Request $request)
    {
        $user = auth()->user();
        $student = Student::with('university', 'section')->findOrFail($user['userable_id']);
        $coursesEnrolled = $student->courses()->with('media')->get();
        $searchQuery = $request->input('name');
        $courses = Course::where('name', 'LIKE', "%{$searchQuery}%")
        ->orWhereHas('teachers', function ($query) use ($searchQuery) {
            $query->whereHas('userable', function ($query) use ($searchQuery) {
                $query->where('first_name', 'LIKE', "%{$searchQuery}%")
                ->orWhere('last_name', 'LIKE', "%{$searchQuery}%");
            });
        })
        ->with('media')
        ->get();
        $imageUrls = [];
        foreach ($courses as $course) {
            $isPurchased = $coursesEnrolled->contains($course->id);
            $teacherId = $course->teachers->select("id");
            $teacher = User::where('userable_type', 'App\Models\Teacher')->where('userable_id', $teacherId)->get();
            $teacherName = $teacher->first()->first_name . " " . $teacher->first()->last_name;
            $media = $course->media->where('collection_name', 'courses-images')->select('id', 'file_name');
            $imageUrls[] = [
               'is_purchased' => $isPurchased ? 1 : 0,
                "teacherName"   => $teacherName,
                'id' => $course->id,
                'course_name' => $course->name,
                'description' => $course->description,
                'price' => $course->price,
                'total_subs'=>$course->total_subs,
                'media_id'=>$media?->first()['id'],
                'media_name'=>$media?->first()['file_name'],
                'image_url' => "https://backend1.tamayaz.tech/api/get-image/" . $media?->first()['id'] . "/" . $media?->first()['file_name'],
            ];
        }
        return response()->json($imageUrls);
    }


 public function getCourseTeachers(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);
        $course = Course::with('teachers.userable')->findOrFail($request->input('course_id'));
        $teachersInfo = [];
        // $searchQuery = $request->input('course_id');
        foreach ($course->teachers as $teacher) {

            $teacherUser  = $teacher->userable; // Assuming userable is the relation to the User model
            $teachersInfo[] = [
                'id' => $teacher->id,
                'full_name' => $teacherUser ->full_name, // Assuming there's a full_name attribute
                'info' => $teacher->info,
                'url' => "https://backend1.tamayaz.tech/api/get-image/" . ($teacher->media->isNotEmpty() ? $teacher->media->first()->id . "/" . $teacher->media->first()->file_name : null),
                'course_teacher_id' => $teacher->pivot->id,

            ];
        }
         return response()->json(
        // 'course_id' => $course->id,
        // 'course_name' => $course->name,
        $teachersInfo,
    );
    }

}
