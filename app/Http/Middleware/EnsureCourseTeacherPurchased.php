<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Student;
use App\Models\CourseEnroll;
use App\Models\CourseTeacher;

class EnsureCourseTeacherPurchased
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $course_id = $request->route('course');
        
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Assuming user is a student
        $student = Student::find($user->userable_id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 403);
        }
        
        // Get all course_teacher_ids that this user has purchased
        $purchasedCourseTeacherIds = CourseEnroll::where('student_id', $user->userable_id)
            ->pluck('course_teacher_id')
            ->toArray();
            
            
        // Get all course_teacher_ids related to the requested course
        
        $courseCourseTeacherIds = CourseTeacher::where('course_id', $course_id)
            ->pluck('id')
            ->toArray();
        
            
        // Check if there's any intersection between the two arrays
        $hasMatchingTeacher = !empty(array_intersect($purchasedCourseTeacherIds, $courseCourseTeacherIds));
        
        if (!$hasMatchingTeacher) {
            abort(403, 'You have not purchased a course teacher for this course.');
        }

        return $next($request);
    }
}