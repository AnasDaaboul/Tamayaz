<?php

namespace App\Http\Controllers\Api;

// use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\CourseEnroll;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;
use App\Models\Teacher;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(private AuthService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register' , 'teacherInfo' , 'allTeachers']]);

    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(SignupRequest $request)
    {
        $validatedData = $request->validated();
        $credentials = ["mobile_number"=>$validatedData['mobile_number'] , "password"=>$validatedData['password']];
        $createUser = $this->authService->createUserStudent($validatedData);
       return $this->createTokenForLogin($credentials);
    }

    public function createTokenForLogin($credentials)
    {
        // Attempt to create a token with the given credentials
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'عذراً ، كلمة المرور أو اسم المستخدم غير صحيحين.'], 422);
        }

        // Return a response with the generated token
        return $this->respondWithToken($token, $credentials['mobile_number']);
    }



    public function login()
    {
        // $userAgent = $request->header('User-Agent');
        // if ($this->isMobile($userAgent)) {
        // return response()->json(['message' => 'This is a mobile device']);

        $credentials = request(['mobile_number', 'password' ]);
        return $this->createTokenForLogin($credentials);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $student = Student::where('id', $user->userable_id)->first();
        
        // Get the courses the student has purchased
        $enrolledCourses = CourseEnroll::where('student_id', $student->id)
            ->with(['courseTeacher.course.media'])
            ->get()
            ->map(function($enrollment) {
                $course = $enrollment->courseTeacher->course;
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'price' => $course->price,
                    'media' => $course->media->map(function($media) {
                        return [
                            'id' => $media->id,
                            'file_name' => $media->file_name,
                            'image_url' => "https://backend1.tamayaz.tech/api/get-image/{$media->id}/{$media->file_name}"
                        ];
                    })
                ];
            });
        
        // Get user information
        $userData = [
            'full_name' => $user->full_name,
            'school_name' => $user->school_name,
            'mobile_number' => $user->mobile_number,
            'courses' => $enrolledCourses
        ];
        
        return response()->json($userData);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user = auth()->user();
        $mobile_number = $user->mobile_number;
        return $this->respondWithToken(auth()->refresh() , $mobile_number);

    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token , $mobile_number)
    {
        $user = User::where('mobile_number', $mobile_number)->get();

        $userUserableId = User::where('mobile_number', $mobile_number)->first()->userable_id;
        $student = Student::find($userUserableId);
        // dd($student->id);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'student_id' => $student->id
        ]);

    }
    public function teacherInfo(Request $request)
    {
        $teacherId = $request->id;
        // $teacher = Teacher::find($teacherId)->with('userable' , 'media')->get();
        $teacher = Teacher::with('userable', 'media')->find($teacherId);
        // dd($teacher);
        $response = [
            'teacherName' => $teacher->userable->first_name . " " . $teacher->userable->last_name,
            'teacher_info' => $teacher->info,
            'media_id'=>$teacher->media?->first()['id'],
            'media_name'=>$teacher->media?->first()['file_name'],
            'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $teacher->media?->first()['id'] ."/" .$teacher->media?->first()['file_name'],

        ];
        return response()->json($response);

    }
    // public function allTeachers()
    // {
    //     $teachers = Teacher::with('userable', 'media')->get();
    //     // dd($teachers);
    //     // Format the output to only include the specified fields
    //     $formattedTeachers = $teachers->map(function ($teacher) {
    //         return [
    //             'info' => $teacher->info,
    //             'id' => $teacher->userable->id,
    //             'full_name' => $teacher->userable->full_name,
    //             'url' => "https://tamayaz.tech/api/get-image/" . ($teacher->media->isNotEmpty() ? $teacher->media->first()->id . "/" . $teacher->media->first()->file_name : null),

    //         ];
    //     });

    //     return response()->json($formattedTeachers, 200);
    // }
    public function allTeachers()
{
    // Load all teachers with their userable, media, and pivot relationships
    $teachers = Teacher::with(['userable', 'media', 'course'])->get();

    $formattedTeachers = $teachers->map(function ($teacher) {
        return $teacher->course->map(function ($course) use ($teacher) {
            return [
                'info' => $teacher->info,
                'id' => $teacher->userable->id,
                'full_name' => $teacher->userable->full_name,
                'url' => "https://backend1.tamayaz.tech/api/get-image/" . ($teacher->media->isNotEmpty() ? $teacher->media->first()->id . "/" . $teacher->media->first()->file_name : null),
                'course_teacher_id' => $course->pivot->id,
            ];
        });
    })->flatten(1); // Flatten the collection to a single level

    return response()->json($formattedTeachers, 200);
}



    public function delete(Request $request)
{
    try {
        $user = auth()->user();
        $student = Student::find($user->userable_id);

        // Delete the student record
        $student->delete();

        // Delete the user record
        $user->delete();

        // Return a success response
        return response()->json(['message' => 'Account deleted successfully']);
    } catch (\Exception $e) {
        // Return an error response
        return response()->json(['error' => 'Failed to delete account'], 422);
    }
}
}
