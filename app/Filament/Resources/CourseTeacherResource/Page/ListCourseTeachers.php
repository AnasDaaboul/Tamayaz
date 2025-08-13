<?php

namespace App\Filament\Resources\CourseTeacherResource\Pages;

use App\Filament\Resources\CourseTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseTeachers extends ListRecords
{
    protected static string $resource = CourseTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
