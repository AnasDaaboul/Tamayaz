<?php

namespace App\Filament\Resources\CourseResource\Pages;

use Filament\Forms;
use Filament\Actions;
use Filament\Forms\Form;
use App\Models\CourseMedia;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CourseResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Model;


class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema(array_merge(
                CourseResource::formSchema(),
                [
                    Repeater::make('course_media') // The repeater should map to the relationship, not just 'media_id'
                        ->relationship('course_media') // Establish the correct relationship
                        ->schema([
                             TextInput::make('file_name')->disabled(),
                        ])
                        ->default([]) // Provide an empty array as default
                        ->orderColumn('order'),
                ]
            ));
    }


  protected function handleRecordUpdate(Model $record, array $data): Model
{
    // Update the course with the provided data

    $record->update($data);

    // Get the currently associated videos (media) for the course
    $CourseVideos = Media::where('model_id', $record->id)
    ->where(function($query) {
        $query->where('collection_name', 'courses-videos')
              ->orWhere('collection_name', 'courses-free-video');
    })
    ->get();

    foreach ($CourseVideos as $media) {
        // Check if the media is already associated with the course
        $existingMedia = CourseMedia::query()
                                    ->where('course_id', $record->id)
                                    ->where('media_id', $media->id)
                                    ->first();

        // If the media is not already associated, create a new CourseMedia record
        if (!$existingMedia) {
            CourseMedia::create([
                'course_id' => $record->id,
                'media_id' => $media->id,
                'file_name' => $media->name,
            ]);
        }
    }

    return $record;
}


    // protected function afterSave(): void
    // {
    //     parent::afterSave(); // Call the parent method if needed


    // }
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
    return $this->getResource()::getUrl('index');
    }
}