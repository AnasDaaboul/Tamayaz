<?php

namespace App\Filament\Resources\CourseTeacherResource\Pages;

use App\Filament\Resources\CourseTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseTeacher extends CreateRecord
{
    protected static string $resource = CourseTeacherResource::class;

    protected function afterCreate(): void
    {
        $courseTeacher = $this->record;

        // Handle media for chapters
        $courseTeacher->load('chapters.media');
        $courseTeacher->chapters->each(function ($chapter) use ($courseTeacher) {
            // Get all media for the chapter
            $chapterMedia = $chapter->media;
            // dd($chapterMedia);
            // Iterate through each media item and create a CourseMedia record
            foreach ($chapterMedia as $media) {
                \App\Models\CourseMedia::updateOrCreate(
                    ['media_id' => $media->id, 'course_teacher_id' => $courseTeacher->id, 'chapter_id' => $chapter->id],
                    ['file_name' => $media->name]
                );
            }
        });
    }
}
