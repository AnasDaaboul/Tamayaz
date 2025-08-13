<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Course;
use App\Models\Package;
use App\Models\Student;
use App\Models\Favorites;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\CourseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentController extends Controller
{
    public function addToFavorites(FavoriteRequest $request)
{
    $validatedData = $request->validated();
    $user = auth()->user();

    try{
        $student =  Student::find($user['userable_id'])->first();
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Student not found'], 404);
    }


    $favoritableType = $validatedData['favorateable_type']; // Get type from request

    $favoritableType = "App\Models\\"  . $favoritableType ;
    $favoritableId = $validatedData['favorateable_id'];
    if(($validatedData['favorateable_type'] == "Course" || $validatedData['favorateable_type'] == "course"))
    {

        try{
            $course =  Course::findOrFail($favoritableId)->first();
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }
    else if(($validatedData['favorateable_type'] == "package" || $validatedData['favorateable_type'] == "Package"))
    {

        try{
            $package =  Package::findOrFail($favoritableId)->first();
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Package not found'], 404);
        }
    }

    else return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);

    if($this->isFavorited($user['userable_id'] , $favoritableType , $favoritableId))
    return $this->removeFromFavorites($user['userable_id'] , $favoritableType , $favoritableId);

    else{
    $favorites = Favorites::create([
        'favorateable_id' => $favoritableId,
        'favorateable_type' => $favoritableType,
        'student_id' => $user['userable_id'],
    ]);
}
    return response()->json(['Added to favorites!'] , 201);
}




public function isFavorited($studentId , $favoritableType , $favoritableId)
{
    $isFavorited = Favorites::where('student_id', $studentId)
    ->where('favorateable_type' , $favoritableType)
    ->where('favorateable_id' , $favoritableId)->exists();
    return $isFavorited ? 1 : 0;
}
public function removeFromFavorites($studentId , $favoritableType , $favoritableId)
{
    $favorite = Favorites::where('student_id', $studentId)
    ->where('favorateable_type' , $favoritableType)
    ->where('favorateable_id' , $favoritableId)->first();
    // dd($favorite);
    $favorite->delete();

    return response()->json(['removed from favorites'] , 422);
}


public function getFavorites()
{
    $user = auth()->user();
    try{
        $student =  Student::find($user['userable_id'])->first();
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Student not found'], 404);
    }




$favorites = Favorites::where('student_id', $student->id)
                            ->where("favorateable_type" , "App\\Models\\Course")
                            ->with(['favorateable' => function ($query) {
                                $query->with(['media' => function ($query) {
                                    $query->where('collection_name', 'courses-images');
                                }]);
                            }])
                            ->get()
                            ->pluck('favorateable' );
                            $coursesEnrolled = $student->courses()->with('media')->get();

                            if(count($favorites) == 0)
                            return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);


foreach($favorites as $course)
{
    $teacherId = $course->teachers->pluck('id')->first();
        $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $teacherId)->get();
    if (count($course->media) === 0 || count($teacher) === 0) {
        return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        }
}

$transformedFavorites = CourseResource::collection($favorites)->map(function ($course) use ($coursesEnrolled) {
$course->is_purchased = $coursesEnrolled->contains($course->id) ? 1 : 0;
return $course;
})->toArray(request());

    return response()->json($transformedFavorites, 200);
}
}