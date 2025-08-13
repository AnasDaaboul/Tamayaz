<?php

namespace App\Http\Controllers\Api;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Course;
use App\Models\chapter;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\CourseTeacher;
use Illuminate\Support\Facades\DB;
use App\Models\StudentQuizAttempt;
use App\Models\ChapterContentOrder;
use App\Http\Controllers\Controller;
use App\Services\CourseEnrollService;
use App\Http\Requests\CourseEnrolledRequest;
use App\Http\Requests\CoursePurchaseRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CourseEnrollController extends Controller
{
    public function __construct(private AuthController $authController , private CourseEnrollService $courseEnrollService)
    {
        // $this->middleware('auth:api');
    }
    // public function checkCourseCompletion(Request $request)
    // {
    //     $user = auth()->user();
    //     $studentId = $user->userable_id;
    //     $courseId = $request->course_id;

    //     $course = Course::find($courseId);
    //     $courseWithMedia = $course->getMedia("*");
    //     dd($courseWithMedia);
    //     $videos = $course->media->where("collection_name" , "courses-videos")
    //     ->where("courses-free-video"); // assuming you have a videos relationship in the Course model

    //     $completedVideos = \App\Models\VideoWatch::where('student_id', $studentId)
    //         ->whereIn('media_id', $videos->pluck('id'))
    //         ->count();

    //     if ($completedVideos == $videos->count()) {
    //         // User has completed all videos in the course
    //         return 1;
    //     } else {
    //         return 0;
    //     }
    // }


//     public function getCoursesEnrolled()
//     {
//         $user = auth()->user();

//          return $this->courseEnrollService->getEnrolledCourses($user['userable_id']);

//     }

//     public function checkCourse(CourseEnrolledRequest $request)
//     {
//         $user =  $user = auth()->user();
//         $validatedData = $request->validated();
//         $courseId=$validatedData['course_id'];
//         return $this->courseEnrollService->checkCourseEnrolled($courseId , $user['userable_id']);
//     }

//     public function coursePurchuse(CoursePurchaseRequest $request)
//     {
//         $user = auth()->user();
//         $validatedData = $request->validated();
//         $course_teacher_ids = (array) $validatedData['course_teacher_id'];
//         $student = Student::find($user->userable_id);


//         // Check if the student has already purchased any of the course teachers
//         $alreadyPurchased = $student->courseTeachers()->whereIn('course_teacher_id', $course_teacher_ids)->pluck('course_teacher_id')->toArray();
//         if (!empty($alreadyPurchased)) {
//             return response()->json([
//                 'message' => 'You have already purchased the following course(s): ' . implode(', ', $alreadyPurchased),
//             ], 400);
//         }

//         // Check if the student has already purchased this course teacher
//         $alreadyPurchased = $student->courseTeachers()->where('course_teacher_id', $course_teacher_id)->exists();
//         if ($alreadyPurchased) {
//             return response()->json([
//                 'message' => 'You have already purchased this course.',
//             ], 400);
//         }

//         $student->courseTeachers()->attach($course_teacher_id, ['cobon_id' => 1]);
//         return response()->json([
//             'message' => 'Course purchased successfully',
//         ], 200);
// }

public function coursePurchuse(CoursePurchaseRequest $request)
{
     $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Failed to authenticate user.'], 401);
    }
    $validatedData = $request->validated();
    $course_teacher_ids = (array) $validatedData['course_teacher_id'];
    // dd($course_teacher_ids);
    $student = Student::find($user->userable_id);
    // Check if the student has already purchased any of the course teachers
    $alreadyPurchased = $student->courseTeachers()
        ->whereIn('course_teacher_id', $course_teacher_ids)
        ->pluck('course_teacher_id')
        ->toArray();
    
    if (!empty($alreadyPurchased)) {
        return response()->json([
            'message' => 'You have already purchased the following course(s): ' . implode(', ', $alreadyPurchased),
        ], 400);
    }

    // Attach all new course teachers
    foreach ($course_teacher_ids as $course_teacher_id) {
        $student->courseTeachers()->attach($course_teacher_id);
    }

    return response()->json([
        'message' => 'Courses purchased successfully',
    ], 200);
}


public function getCourseTeacherInformation(Request $request)
{
    $request->validate([
        'course_teacher_id' => 'required|exists:course_teachers,id',
    ]);

    try {
        $user = auth()->user();
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to authenticate user.'], 401);
    }


    $courseTeacherId = $request->input('course_teacher_id');
    if ($user) {
        $student = Student::find($user->userable_id);
        $isEnrolled = $student->courseTeachers()->where('course_teacher_id', $courseTeacherId)->exists() ? 1 : 0;
    }
    else{

        $isEnrolled = 0;
    }

        $courseTeacher = CourseTeacher::with('media')->find($courseTeacherId);
        $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $courseTeacher->teacher_id)->get();
        // dd($teacher);
        $teacherId = $teacher->first()->userable_id;
        $teacherName = $teacher->first()->full_name;
        // $courseWithMedia = $courseTeacher->media->filter(function(Media $media){
        //     return in_array($media->collection_name , ['courses-pdfs', 'courses-videos' , 'courses-free-video']);
        // });
        $courseWithMedia = $courseTeacher->media
    ->filter(function (Media $media) {
        return in_array($media->collection_name, ['courses-pdfs', 'courses-videos', 'courses-free-video']);
    })
    ->sortBy('order_column');

        $courseId=$courseTeacher->course_id;
        $course = Course::find($courseId);
        $iconPath = $courseTeacher->media->where('collection_name' , 'courses-icons')->select('id' , 'file_name');
        // dd($iconPath);
        $courseImage = $course->media->where('collection_name' , 'courses-images')->select('id' , 'file_name');
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
                 "pdf_url"=>"https://backend1.tamayaz.tech/api/get-image/" . $courseMedia?->id . "/" . $courseMedia?->file_name,
                 'active' => $isEnrolled,
            ];
        }
        else if($videoMimeType == "courses-free-video")
        {
             $freeVideos[] = [
                    'file_id' => $courseMedia['id'],
                    'file_type' => "video",
                    'file_name' => $courseMedia['file_name'],
                    // 'icon_url' =>  "https://tamayaz.tech/api/get-image/" . $iconPath?->first()['id'] . "/" . $iconPath?->first()['file_name'],
                    "video_name" => $courseMedia['name'],
                    "video_url" => "NAN",
                    'active' => 1,
                ];
        }
        else if($videoMimeType == "courses-videos")
        {
             $videos[] = [
                    'file_id' => $courseMedia['id'],
                    'file_type' => "video",
                    'file_name' => $courseMedia['file_name'],
                    // 'icon_url' =>  "https://tamayaz.tech/api/get-image/" . $iconPath?->first()['id'] . "/" . $iconPath?->first()['file_name'],
                    "video_name" => $courseMedia['name'],
                    "video_url" => "NAN",
                    'active' => $isEnrolled,
                ];
        }
    }
     $videos = array_merge($freeVideos, $videos);
    return response()->json([
        'number'=>$course->description,
        'course_name'=>$course->name,
        'price'=>$course->price,
        'is_purchased' =>$isEnrolled,
        'teacherName' =>$teacherName,
        'teacherId'=>$teacherId,
        'videos' => $videos,
        'pdfs' => $pdfs,

    ], 200);







}









public function getCourseTeacherInformation2(Request $request)
{
    $request->validate([
        'course_teacher_id' => 'required|exists:course_teachers,id',
    ]);

    try {
        $user = auth()->user();
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to authenticate user.'], 401);
    }

    $courseTeacherId = $request->input('course_teacher_id');
    $isEnrolled = 0;

    if ($user) {
        $student = Student::find($user->userable_id);
        $isEnrolled = $student->courseTeachers()->where('course_teacher_id', $courseTeacherId)->exists() ? 1 : 0;
    }

    $courseTeacher = CourseTeacher::with(['chapters.media', 'chapters.quizzes.questions', 'media'])->find($courseTeacherId);
    $teacher = User::where('userable_type', 'App\Models\Teacher')
                   ->where('userable_id', $courseTeacher->teacher_id)
                   ->first();

    $course = Course::find($courseTeacher->course_id);

    $chapters = [];
    foreach ($courseTeacher->chapters as $chapter) {
        $chapterData = [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'order' => $chapter->order,
            'contents' => []
        ];

        // Fetch the unified order of media and quizzes for the chapter
        $contentOrders = ChapterContentOrder::where('chapter_id', $chapter->id)
            ->orderBy('order')
            ->get();

            // dd($contentOrders);

        foreach ($contentOrders as $contentOrder) {
            if ($contentOrder->content_type === 'free_video' || $contentOrder->content_type ==='premium_video' || $contentOrder->content_type ==='pdf' ) {

                $media = $chapter->media->firstWhere('id', $contentOrder->content_id);
                if ($media) {

                    $mediaType = $media->collection_name;

                    $mediaData = [
                        'type' => $mediaType,
                        'file_id' => $media->id,
                        'file_name' => $media->file_name,
                        'name' => $media->name,
                        'active' => $mediaType === 'courses-free-video' ? 1 : $isEnrolled
                    ];

                    if ($mediaType === 'courses-pdfs') {
                        $mediaData['file_type'] = 'pdf';
                        $mediaData['pdf_url'] = "https://backend1.tamayaz.tech/api/get-image/{$media->id}/{$media->file_name}";
                    } elseif (in_array($mediaType, ['courses-videos', 'courses-free-video'])) {
                        $mediaData['file_type'] = 'video';
                        $mediaData['video_url'] = "NAN";
                    }

                    $chapterData['contents'][] = $mediaData;
                }
            } elseif ($contentOrder->content_type === 'quiz') {
                $quiz = $chapter->quizzes->firstWhere('id', $contentOrder->content_id);
                if ($quiz) {
                    $quizData = [
                        'type' => 'quiz',
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'description' => $quiz->description,
                        'passing_score' => $quiz->passing_score,
                        'number_of_attempts' => $quiz->number_of_attempts,
                        'active' => $isEnrolled,
                        'questions' => []
                    ];

                    if ($isEnrolled) {
                        foreach ($quiz->questions as $question) {
                            $quizData['questions'][] = [
                                'id' => $question->id,
                                'question_text' => $question->question_text,
                                'options' => $question->options,
                                'points' => $question->points,
                                'correct_answer' => $question->correct_answer
                            ];
                        }
                    }

                    $chapterData['contents'][] = $quizData;
                }
            }
        }

        $chapters[] = $chapterData;
    }

    return response()->json([
        'number' => $course->description,
        'course_name' => $course->name,
        'price' => $course->price,
        'is_purchased' => $isEnrolled,
        'teacherName' => $teacher->full_name,
        'teacherId' => $teacher->userable_id,
        'chapters' => $chapters
    ], 200);
}

    public function updateChapterContentOrder(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'content_order' => 'required|array',
            'content_order.*.content_type' => 'required|in:media,quiz',
            'content_order.*.content_id' => 'required|integer',
            'content_order.*.order' => 'required|integer'
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get the chapter and verify ownership/permissions
            $chapter = chapter::with('course')->findOrFail($request->chapter_id);
            $courseTeacher = $chapter->course;

            if (!$courseTeacher || $courseTeacher->teacher_id !== $user->userable_id) {
                return response()->json(['error' => 'Unauthorized to modify this chapter'], 403);
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                // Delete existing order entries for this chapter
                ChapterContentOrder::where('chapter_id', $chapter->id)->delete();

                // Create new order entries
                foreach ($request->content_order as $index => $content) {
                    ChapterContentOrder::create([
                        'chapter_id' => $chapter->id,
                        'content_type' => $content['content_type'],
                        'content_id' => $content['content_id'],
                        'order' => $content['order']
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Chapter content order updated successfully'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update chapter content order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function submitQuizAnswers(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array'
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $student = Student::find($user->userable_id);
            $quiz = Quiz::with(['questions', 'chapter.course'])->findOrFail($request->quiz_id);

            // Check if student is enrolled in the course
            $isEnrolled = $student->courseTeachers()
                ->where('course_teacher_id', $quiz->chapter->course_teacher_id)
                ->exists();

            if (!$isEnrolled) {
                return response()->json(['error' => 'You are not enrolled in this course'], 403);
            }

            // Check number of attempts
            // $attemptCount = StudentQuizAttempt::where('student_id', $student->id)
            //     ->where('quiz_id', $quiz->id)
            //     ->count();

            // if ($attemptCount >= $quiz->number_of_attempts) {
            //     return response()->json([
            //         'error' => 'You have reached the maximum number of attempts for this quiz'
            //     ], 403);
            // }

            // Calculate score
            $totalPoints = 0;
            $earnedPoints = 0;
            $answers = $request->answers;

            foreach ($quiz->questions as $question) {
                $totalPoints += $question->points;
                if (isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer) {
                    // dd($answers);
                    $earnedPoints += $question->points;
                }
            }

            $score = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed = $score >= $quiz->passing_score;

            // Create attempt record
            $attempt = StudentQuizAttempt::create([
                'student_id' => $student->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'answers' => $answers,
                'passed' => $passed,
                'started_at' => now(),
                'completed_at' => now()
            ]);

            // $remainingAttempts = $quiz->number_of_attempts - ($attemptCount + 1);

            return response()->json([
                'message' => 'Quiz submitted successfully',
                'score' => $score,
                'passed' => $passed,
                'total_points' => $totalPoints,
                'earned_points' => $earnedPoints,
                // 'remaining_attempts' => $remainingAttempts
            ], 200);

        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Failed to submit quiz answers'], 500);
        }
    }

    public function getQuizDetails(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id'
        ]);

        try {
            $user = auth()->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate user.'], 401);
        }

        $quizId = $request->input('quiz_id');
        $quiz = Quiz::with(['chapter.course', 'questions'])->findOrFail($quizId);

        // Check if user is enrolled in the course
        $isEnrolled = false;
        if ($user) {
            $student = Student::find($user->userable_id);
            $courseTeacherId = $quiz->chapter->course_teacher_id;
            $isEnrolled = $student->courseTeachers()->where('course_teacher_id', $courseTeacherId)->exists();
            // $isEnrolled = true;
            // Get student's previous attempts
            $attempts = StudentQuizAttempt::where('student_id', $student->id)
                ->where('quiz_id', $quizId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $response = [
            'quiz_id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit,
            'passing_score' => $quiz->passing_score,
            'is_active' => $quiz->is_active,
            'is_enrolled' => $isEnrolled
        ];

        if ($isEnrolled) {
            $response['questions'] = $quiz->questions->map(function($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'points' => $question->points,
                    'correct_answer' => $question->correct_answer
                ];
            });

            $response['attempts'] = $attempts->map(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'passed' => $attempt->passed,
                    'started_at' => $attempt->started_at,
                    'completed_at' => $attempt->completed_at
                ];
            });
        }

        return response()->json($response, 200);
    }

}
