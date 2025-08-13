<?php

namespace App\Filament\Resources\CourseResource\Pages;

use Filament\Forms;
use Filament\Actions;
use App\Models\Course;
use Filament\Forms\Form;
use App\Models\CourseMedia;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Filament\Resources\CourseResource;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
    protected function getRedirectUrl(): string
    {
    return $this->getResource()::getUrl('index');
    }
    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema(CourseResource::formSchema())
            ->columns(null);
    }

    protected function afterCreate(): void
    {
        $course = $this->record; // This should be the created course record
        sleep(5);
        if ($course) {
            $courseFreeVideos = Media::where('model_id' , $course->id)->where('collection_name' , 'courses-free-video')->get();
            $CourseVideos = Media::where('model_id' , $course->id)->where('collection_name' , 'courses-videos')->get();
            foreach ($CourseVideos as $media) {
                $courseMedia = CourseMedia::create([
                    'course_id' => $course->id,
                    'media_id' => $media->id,
                    'file_name' => $media->name,
                ]);

            }
            foreach ($courseFreeVideos as $media) {
                $courseMedia = CourseMedia::create([
                    'course_id' => $course->id,
                    'media_id' => $media->id,
                    'file_name' => $media->name,
                ]);

            }
        }
    }


    protected function afterSave(): void
    {
        parent::afterSave(); // Call the parent method if needed


    }



}