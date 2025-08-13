<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\CourseTeacher;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;


class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    public static function formSchema(): array
    {
        return [
            SpatieMediaLibraryFileUpload::make('courses-images')
                ->required()
                ->collection('courses-images')
                ->disk('media')
                ->maxSize(20000),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('$'),
            Forms\Components\TextInput::make('total_subs')
                ->required()
                ->numeric(),
            Forms\Components\Textarea::make('description')
                ->required()
                ->label('number of questions')
                //  ->numeric()
                ->columnSpanFull(),

                ];

    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ])
            ;
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('courses-images')
                ->label('Image')
                ->circular()
                ->defaultImageUrl(function($record) {
                    $media = $record->media()->where('collection_name', 'courses-images')->first();
                    if ($media) {
                        $id = $media->id;
                        $file_name = $media->file_name;
                        return route('get-image', [$id,$file_name ]);
                    }


                }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_subs')
                    ->numeric()
                    ->sortable(),



            ])
            ->filters([
                //
            ])
            ->actions([
                   Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }



    public static function infolist(Infolist $infolist ): Infolist
    {

        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Course Name'),

                    TextEntry::make('price')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Course Price'),

                    TextEntry::make('total_subs')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Total Subs'),

                    TextEntry::make('description')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Description'),

                //     ImageEntry::make('courses-images')
                //     ->label('Image')
                //     ->width(600)
                //     ->height(300)
                //     ->defaultImageUrl(function($record) {
                //         dd($record);
                //     $media = $media->getFirstMedia();

                //     return route('get-image', [$media->id, $media->file_name]);

                // }),

        ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();


    $user = auth()->user();
    // If the user has a morph relationship with a teacher
    if ($user->userable_type === "App\Models\Teacher") {
        // Filter courses by teacher
        $query->whereHas('teachers', function ($query) use ($user) {
            $query->where('teacher_id', $user->userable_id);
        });

    }

    return $query;
}

}
