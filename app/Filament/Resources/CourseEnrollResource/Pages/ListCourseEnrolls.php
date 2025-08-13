<?php

namespace App\Filament\Resources\CourseEnrollResource\Pages;

use App\Filament\Resources\CourseEnrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseEnrolls extends ListRecords
{
    protected static string $resource = CourseEnrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
