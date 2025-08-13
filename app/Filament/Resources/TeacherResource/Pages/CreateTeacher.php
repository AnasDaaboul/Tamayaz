<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use Filament\Actions;
use App\Models\Teacher;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TeacherResource;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;
    protected function getRedirectUrl(): string
    {
    return $this->getResource()::getUrl('index');
    }



    // protected function mutateFormDataBeforeCreate(array $data): array
    // {

    //         $teacher = Teacher::create([
    //             'university_name'=>$data['university_name'],
    //             'percentage'=>$data['percentage'],
    //             'balance'=>$data['balance'],
    //         ]);
    //         $data['userable_id'] = $teacher->id;

    //         return $data;
    // }



}
