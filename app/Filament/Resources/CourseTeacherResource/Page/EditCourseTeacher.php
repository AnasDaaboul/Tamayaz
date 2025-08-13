<?php

namespace App\Filament\Resources\CourseTeacherResource\Pages;

use App\Filament\Resources\CourseTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseTeacher extends EditRecord
{
    protected static string $resource = CourseTeacherResource::class;





    protected function afterSave(): void
    {
        $courseTeacher = $this->record;

        // Handle media for chapters
        $courseTeacher->chapters->each(function ($chapter) use ($courseTeacher) {
            // Get all media for the chapter
            $chapterMedia = $chapter->getMedia();
            foreach ($chapterMedia as $media) {
                \App\Models\CourseMedia::updateOrCreate(
                    ['media_id' => $media->id, 'course_teacher_id' => $courseTeacher->id, 'chapter_id' => $chapter->id],
                    ['file_name' => $media->name]
                );
            }
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
