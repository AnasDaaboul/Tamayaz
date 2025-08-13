<?php

use App\Http\Controllers\CompetitiveMatchController;
use App\Http\Controllers\CompetitiveArenaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseEnrollController;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Course;
use App\Models\Student;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\CompetitiveGameController;
use App\Http\Controllers\Api\SliderController;
use App\Models\CourseTeacher;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/slider' , [SliderController::class ,'index' ]);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    
});

// مسارات المباريات التنافسية (القديمة)
Route::prefix('competitive')->middleware('ensure.course.teacher.purchased')->group(function () {
    Route::post('matches/{course}', [CompetitiveMatchController::class, 'findMatch']);
    Route::post('matches/{match}/answer', [CompetitiveMatchController::class, 'submitAnswer']);
    Route::get('leaderboard/{course}', [CompetitiveMatchController::class, 'getLeaderboard']);
    
});

// واجهة برمجة التطبيقات لخادم Node.js للمباريات التنافسية
// هذه المسارات تستخدم من قبل خادم Node.js للتواصل مع Laravel
Route::prefix('competitive-nodejs')->group(function () { 
    // إنهاء مباراة
    Route::post('/check-course-purchased/{course}', function () {
        return response()->json('true' , 200);
    })->middleware(['ensure.course.teacher.purchased']);
    Route::post('matches/finalize', [\App\Http\Controllers\API\CompetitiveMatchResultController::class, 'completeMatch']);
    
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class , 'login'])->name('login');//MAKE THE ERROR RESPONSE IN ONE VARIABLE
    Route::post('logout', [AuthController::class , 'logout']);
    Route::post('refresh', [AuthController::class , 'refresh']);
    Route::post('me', [AuthController::class , 'me']);
    Route::post('delete', [AuthController::class , 'delete']);
    Route::post('register' ,[AuthController::class , 'register']);//MAKE THE ERROR RESPONSE IN ONE VARIABLE
});

Route::post('allteachers', [AuthController::class , 'allTeachers']);
Route::post('allCourses' , [CourseController::class , 'getAllCourses']);
Route::post('course-teachers' , [CourseController::class , 'getCourseTeachers']);

// Competitive Sen Jem Game Routes
Route::prefix('competitive-game')->middleware('auth:api')->group(function () { // Assuming auth:api middleware for authenticated users
    Route::post('create-match', [CompetitiveGameController::class, 'createMatch']);
    Route::post('courses-units', [CompetitiveGameController::class, 'getCoursesWithUnits']);
    Route::post('get-questions', [CompetitiveGameController::class, 'getGameQuestions']);
    Route::post('finalize-match', [CompetitiveGameController::class, 'finalizeMatch']);
    Route::post('answer', [CompetitiveGameController::class, 'submitAnswer']);
    //NEWAPIS
    Route::get('my-games', [CompetitiveGameController::class, 'myGames']);
    Route::get('match-questions', [CompetitiveGameController::class, 'getMatchQuestions']);
    Route::post('change-question', [CompetitiveGameController::class, 'changeQuestion']);
    Route::post('get-question-options', [CompetitiveGameController::class, 'getQuestionOptions']);
    Route::post('use-third-lifeline', [CompetitiveGameController::class, 'useThirdLifeline']);

});
Route::post('course-information' , [CourseEnrollController::class , 'getCourseTeacherInformation']);



//************************************************************************************************************************************/
Route::post('course-information11' , [CourseEnrollController::class , 'getCourseTeacherInformation2']);
//********************************************************************************************************************************** */

Route::post('chapter-content-order/update', [CourseEnrollController::class, 'updateChapterContentOrder']);
Route::post('quiz-details' , [CourseEnrollController::class , 'getQuizDetails']);
Route::post('submit-quiz' , [CourseEnrollController::class , 'submitQuizAnswers']);











Route::post('course-purchuse' , [CourseEnrollController::class , 'coursePurchuse']);



Route::get('/get-video/{id}/{name}', [VideoController::class, 'streamVideo'])
    ->name('shared-video');

Route::post('/playground' , function(Request $request){
    $request->validate([
        'id' => 'required|exists:media,id',
        'name' => 'required'
    ]);

    $user = auth()->user();
    $media = Media::find($request->id);
    
    if ($media->file_name !== $request->name) {
        return response()->json(['error' => 'File name does not match'], 422);
    }
    
    $name = $media->file_name;
     if ($user) {
    $student = Student::findOrFail($user->userable_id);

    $courseId = $media->model_id;
    $isEnrolled = $student->courseTeachers()->where('course_teacher_id', $courseId)->exists() ? 1 : 0;

     if($isEnrolled || $media->collection_name === "courses-free-video")
    {
        $url =URL::temporarySignedRoute('shared-video' , now()->addMinutes(90) , [
        'id'=>$request->id ,
        'name'=>$name,
    ]);

    return $url;

    }



    }
    else
    {
     if($media->collection_name === "courses-free-video")
    {
        $url =URL::temporarySignedRoute('shared-video' , now()->addMinutes(90) , [
        'id'=>$request->id ,
        'name'=>$name,
    ]);

    return $url;

    }

    }





});
Route::get('/get-image/{id}/{name}' , function($id , $name){

$path = ("media/$id/$name");
$media = Media::find($id);

$mimeType = $media->mime_type;
if($mimeType == "video/mp4" )
return response()->json("unvalid mime type",422);
else
{
$file = Storage::path($path);
$response = response()->file($file);
$response->headers->set('Content-Type', $mimeType);
$response->headers->set('Accept-Ranges', 'bytes');
return $response;
}
}
)->name("get-image");
