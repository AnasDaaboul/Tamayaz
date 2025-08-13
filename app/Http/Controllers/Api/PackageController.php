<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackageController extends Controller
{
    private function paginate($items, $perPage, $page)
{
    $offset = ($page - 1) * $perPage;
    return $items->slice($offset, $perPage);
}
    public function index(Request $request)
    {
        $perPage = 5;
        $page = $request->input('page', 1);

        // $packages = Package::with('media' , 'courses')->get();
        $user = auth()->user();
        try{
            $student = Student::with('university', 'section')->findOrFail($user['userable_id']);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $section = $student->section;
        $sectionId = $section->id;

        $university = $student->university;
        $universityId = $university->id;
        $packages = Package::whereHas('sections' , function ($query) use ($sectionId) {
            $query->where('sections.id', $sectionId);
        })->whereHas('universities', function ($query) use ($universityId) {
                      $query->where('universities.id', $universityId);

        })->get();


          if (count($packages) == 0) {
            return response()->json(['message' => 'No packages found'], 404);
        }
        $paginatedCourses = $this->paginate($packages, $perPage, $page);
        $imageUrls = [];

        foreach($paginatedCourses as $package)
        {
            $media = $package->media->first();
            if(!($media) === 0)

            return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
            $imageUrls[] = [
                'id' => $package?->id,
                'package_name' => $package?->name,
                'description' => $package?->description,
                'price' => $package?->price,
                'media_id'=>$media?->id,
                'media_name'=>$media?->file_name,
                'courses_count'=>$package->courses->count(),
                'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->id ."/" .$media?->file_name,
            ];
        }
        // if (empty($imageUrls)) {
        //     return response()->json(['message' => 'No packages found'], 404);
        // }
        return response()->json([
            'data' => $imageUrls,
            'pagination' => [
                'total' => count($packages),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil(count($packages) / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, count($packages)),
            ],
        ]);
    }



    public function getAllPackagesSection(Request $request)
    {

        if ( auth()->check())
       {
         $user = auth()->user();

         try{
            $student = Student::with('university', 'section')->findOrFail($user['userable_id']);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Student not found'], 404);
        }


        $section = $student->section;
        $sectionId = $section->id;

        $university = $student->university;
        $universityId = $university->id;
        $packages = Package::whereHas('sections' , function ($query) use ($sectionId) {
            $query->where('sections.id', $sectionId);
        })->whereHas('universities', function ($query) use ($universityId) {
                      $query->where('universities.id', $universityId);

        })->get();
        if (count($packages) == 0) {
            return response()->json(['message' => 'No packages found'], 404);
        }
    }
        $imageUrls = [];
        foreach($packages as $package)
        {

            $media = $package->media->first();
            if(count($media) === 0)
            return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
            $imageUrls[] = [
                'id' => $package->id,
                'package_name' => $package->name,
                'description' => $package->description,
                'price' => $package->price,
                'media_id'=>$media->id,
                'media_name'=>$media->file_name,
                'courses_count'=>$package->courses->count(),
               'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->id ."/" .$media?->file_name,
            ];
        }
        if (empty($imageUrls)) {
            return response()->json(['message' => 'No packages found'], 404);
        }
        return response()->json($imageUrls);

        // else return response()->json("No packages for this user");
    }


public function allCourses(Request $request)
{

    $user = auth()->user();

    try{
        $student = Student::findOrFail($user['userable_id']);
    }
    catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Student not found'], 404);
    }



    $packageId = $request->package_id;

    $package = Package::findOrFail($packageId);
    $coursesEnrolled = $student->courses()->with('media')->get();

        $packageCourses = $package->courses->map(function ($course)use ($coursesEnrolled)  {
            $isPurchased = $coursesEnrolled->contains($course->id);
            $teacherId = $course->teachers->select("id");
            $teacher = User::where('userable_type' , 'App\Models\Teacher')->where('userable_id' , $teacherId)->get();
            $teacherName = $teacher->first()->first_name . " " . $teacher->first()->last_name;
            $media = $course->media->where('collection_name' , 'courses-images')->select('id' , 'file_name');
            if(count($media) === 0)
            return response()->json(['message' => 'حدث خطأ ما يرجى المحاولة لاحقا'], 404);
        return [
             'is_purchased' => $isPurchased ? 1 : 0,
                "teacherName"   => $teacherName,
                'id' => $course->id,
                'course_name' => $course->name,
                'description' => $course->description,
                'price' => $course->price,
                'total_subs'=>$course->total_subs,
                'media_id'=>$media?->first()['id'],
                'media_name'=>$media?->first()['file_name'],
                'image_url'=> "https://ac20cf.com/api/get-image/" . $media?->first()['id'] ."/" .$media?->first()['file_name'],
        ];
    });

    return response($packageCourses, 200);
}






// public function create(PackageRequest $request)
// {
//     $validatedData = $request->validated();
//     $package = Package::create([
//         'name'=>$validatedData['name'],
//         'price'=>$validatedData['price'],
//         'description'=>$validatedData['description'],
//     ]);
//     $package->courses()->attach($validatedData['courses']);
//     $package->sections()->attach($validatedData['sections']);
//     $package->addMedia($validatedData['image'])
//     ->toMediaCollection('package-images' , 'media');
//         return response()->json(['package has been added'],201);
// }

}
