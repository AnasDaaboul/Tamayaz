<?php
namespace App\Services;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use App\Models\Student;

class AuthService
{

    public function createStudent($validatedData)
    {
        $student =Student::create([
            'description'=>$validatedData['description'],
            ]);
        return $student;
    }

    public function createUserStudent($validatedData)
    {
        $student = $this->createStudent($validatedData);
        $user = User::create([
        'full_name'=> $validatedData['full_name'],
        'email'=> $validatedData['email'],
        'school_name'=> $validatedData['school_name'],
        'Gender'=> $validatedData['Gender'],
        'mobile_number' =>$validatedData['mobile_number'],
        'password'=> bcrypt($validatedData['password']),
        'userable_type'=> "App\Models\Student",
        'userable_id' =>$student->id,
        ]);
        return $user;
    }


    public function createTokenForLogin($credentials , $deviceInfo)
    {


        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

}