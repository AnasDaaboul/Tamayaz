<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CourseTeacher;
use Filament\Resources\Resource;
use App\Models\ChapterContentOrder;
use Filament\Forms\Components\Select;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Filament\Resources\CourseTeacherResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class CourseTeacherResource extends Resource
{
    protected static ?string $model = CourseTeacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Select::make('course_id')
    //                 ->relationship(name: 'course', titleAttribute: 'name')
    //                 ->preload(),

    //             Select::make('teacher_id')
    //             ->relationship(
    //             name: 'teacher',
    //             titleAttribute: 'user.full_name'
    //             )
    //             ->preload()
    //             ->options(
    //     fn () => \App\Models\Teacher::query()
    //         ->with('userable')
    //         ->get()
    //         ->pluck('userable.full_name', 'id')
    // ),

    //             Forms\Components\Section::make('Chapters')
    //                 ->schema([
    //                     Forms\Components\Repeater::make('chapters')
    //                         ->relationship()
    //                         ->schema([
    //                             Forms\Components\TextInput::make('title')
    //                                 ->required()
    //                                 ->maxLength(255),
    //                             Forms\Components\Section::make('Content')
    //                                 ->schema([
    //                                     Forms\Components\Repeater::make('content')
    //                                         ->schema([
    //                                             Forms\Components\Select::make('content_type')
    //                                                 ->options([
    //                                                     'free_video' => 'Free Video',
    //                                                     'premium_video' => 'Premium Video',
    //                                                     'pdf' => 'PDF Document',
    //                                                     'quiz' => 'Quiz'
    //                                                 ])
    //                                                 ->required()
    //                                                 ->reactive()
    //                                                 ->afterStateUpdated(fn ($state, callable $set) => $set('content_id', null))
    //                                                 ->default(function ($record) {
    //                                                     if (!$record) return null;
    //                                                     $contentOrder = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                         ->orderBy('order')
    //                                                         ->first();
    //                                                     return $contentOrder ? $contentOrder->content_type : null;
    //                                                 }),

    //                                             Forms\Components\Select::make('content_id')
    //                                                 ->label('Content')
    //                                                 ->options(function (callable $get) {
    //                                                     $type = $get('content_type');
    //                                                     if (!$type) return [];

    //                                                     switch($type) {
    //                                                         case 'free_video':
    //                                                             return Media::where('collection_name', 'courses-free-video')->pluck('name', 'id');
    //                                                         case 'premium_video':
    //                                                             return Media::where('collection_name', 'courses-videos')->pluck('name', 'id');
    //                                                         case 'pdf':
    //                                                             return Media::where('collection_name', 'courses-pdfs')->pluck('name', 'id');
    //                                                         case 'quiz':
    //                                                             return \App\Models\Quiz::pluck('title', 'id');
    //                                                         default:
    //                                                             return [];
    //                                                     }
    //                                                 })
    //                                                 ->required()
    //                                                 ->default(function ($record) {
    //                                                     if (!$record) return null;
    //                                                     $contentOrder = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                         ->orderBy('order')
    //                                                         ->first();
    //                                                     return $contentOrder ? $contentOrder->content_id : null;
    //                                                 }),
    //                                         ])
    //                                         ->reorderable()
    //                                         ->afterStateUpdated(function ($state, $record) {
    //                                             if (!$record || !$state) return;

    //                                             $existingOrders = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                 ->orderBy('order')
    //                                                 ->get();

    //                                             $nextOrder = $existingOrders->isEmpty() ? 1 : $existingOrders->max('order') + 1;

    //                                             foreach ($state as $content) {
    //                                                 if (!isset($content['content_type']) || !isset($content['content_id'])) continue;

    //                                                 \App\Models\ChapterContentOrder::updateOrCreate(
    //                                                     [
    //                                                         'chapter_id' => $record->id,
    //                                                         'content_type' => $content['content_type'],
    //                                                         'content_id' => $content['content_id'],
    //                                                     ],
    //                                                     ['order' => $nextOrder++]
    //                                                 );
    //                                             }
    //                                         })
    //                                         ->orderColumn('order')
    //                                         ->defaultItems(0),

    //                                     SpatieMediaLibraryFileUpload::make('courses-free-video')
    //                                         ->label('Free Videos')
    //                                         ->multiple()
    //                                         ->reorderable()
    //                                         ->afterStateUpdated(function ($state, $record) {
    //                                             if (!$record || !$state) return;

    //                                             $existingOrders = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                 ->orderBy('order')
    //                                                 ->get();

    //                                             $nextOrder = $existingOrders->isEmpty() ? 1 : $existingOrders->max('order') + 1;

    //                                             foreach ($state as $media) {
    //                                                 if ($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
    //                                                     \App\Models\ChapterContentOrder::updateOrCreate(
    //                                                         [
    //                                                             'chapter_id' => $record->id,
    //                                                             'content_type' => 'free_video',
    //                                                             'content_id' => $media->id,
    //                                                         ],
    //                                                         ['order' => $nextOrder++]
    //                                                     );
    //                                                 }
    //                                             }
    //                                         })
    //                                         ->collection('courses-free-video')
    //                                         ->disk('media')
    //                                         ->storeFileNamesIn('media.name')
    //                                         ->acceptedFileTypes(['video/avi', 'video/mov', 'video/webm', 'video/mp4']),

    //                                     SpatieMediaLibraryFileUpload::make('courses-videos')
    //                                         ->label('Premium Videos')
    //                                         ->collection('courses-videos')
    //                                         ->multiple()
    //                                         ->reorderable()
    //                                         ->afterStateUpdated(function ($state, $record) {
    //                                             if (!$record || !$state) return;

    //                                             $existingOrders = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                 ->orderBy('order')
    //                                                 ->get();

    //                                             $nextOrder = $existingOrders->isEmpty() ? 1 : $existingOrders->max('order') + 1;

    //                                             foreach ($state as $media) {
    //                                                 if ($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
    //                                                     \App\Models\ChapterContentOrder::updateOrCreate(
    //                                                         [
    //                                                             'chapter_id' => $record->id,
    //                                                             'content_type' => 'premium_video',
    //                                                             'content_id' => $media->id,
    //                                                         ],
    //                                                         ['order' => $nextOrder++]
    //                                                     );
    //                                                 }
    //                                             }
    //                                         })
    //                                         ->disk('media')
    //                                         ->acceptedFileTypes(['video/avi', 'video/mov', 'video/webm', 'video/mp4']),

    //                                     SpatieMediaLibraryFileUpload::make('courses-pdfs')
    //                                         ->label('PDF Documents')
    //                                         ->reorderable()
    //                                         ->afterStateUpdated(function ($state, $record) {
    //                                             if (!$record || !$state) return;

    //                                             $existingOrders = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
    //                                                 ->orderBy('order')
    //                                                 ->get();

    //                                             $nextOrder = $existingOrders->isEmpty() ? 1 : $existingOrders->max('order') + 1;

    //                                             foreach ($state as $media) {
    //                                                 if ($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
    //                                                     \App\Models\ChapterContentOrder::updateOrCreate(
    //                                                         [
    //                                                             'chapter_id' => $record->id,
    //                                                             'content_type' => 'pdf',
    //                                                             'content_id' => $media->id,
    //                                                         ],
    //                                                         ['order' => $nextOrder++]
    //                                                     );
    //                                                 }
    //                                             }
    //                                         })
    //                                         ->collection('courses-pdfs')
    //                                         ->multiple()
    //                                         ->disk('media')
    //                                         ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
    //                                 ])
    //                                 ->collapsible(),

    //                             Forms\Components\Section::make('Quizzes')
    //                                 ->schema([
    //                                     Forms\Components\Repeater::make('quizzes')
    //                                         ->relationship()
    //                                         // ->orderColumn('order')
    //                                         ->schema([
    //                                             Forms\Components\TextInput::make('title')
    //                                                 ->required()
    //                                                 ->maxLength(255),
    //                                             Forms\Components\Textarea::make('description')
    //                                                 ->maxLength(65535),
    //                                             Forms\Components\TextInput::make('time_limit')
    //                                                 ->numeric()
    //                                                 ->label('Time Limit (minutes)')
    //                                                 ->nullable(),
    //                                             Forms\Components\TextInput::make('passing_score')
    //                                                 ->numeric()
    //                                                 ->default(60)
    //                                                 ->required(),
    //                                             Forms\Components\TextInput::make('number_of_attempts')
    //                                                 ->numeric()
    //                                                 ->default(3)
    //                                                 ->required(),
    //                                             Forms\Components\Toggle::make('is_active')
    //                                                 ->default(true),
    //                                             Forms\Components\Repeater::make('questions')
    //                                                 ->relationship()
    //                                                 ->schema([
    //                                                     Forms\Components\Textarea::make('question_text')
    //                                                         ->required(),
    //                                                     Forms\Components\Repeater::make('options')
    //                                                         ->schema([
    //                                                             Forms\Components\TextInput::make('option')
    //                                                                 ->required()
    //                                                         ])
    //                                                         ->minItems(2)
    //                                                         ->maxItems(5)
    //                                                         ->required(),
    //                                                     Forms\Components\Select::make('correct_answer')
    //                                                         ->options(function (Forms\Get $get): array {
    //                                                             $options = $get('options');
    //                                                             if (!$options) return [];
    //                                                             return collect($options)
    //                                                                 ->pluck('option')
    //                                                                 ->filter()
    //                                                                 ->mapWithKeys(fn ($option) => [$option => $option])
    //                                                                 ->toArray();
    //                                                         })
    //                                                         ->required(),
    //                                                     Forms\Components\TextInput::make('points')
    //                                                         ->numeric()
    //                                                         ->default(1)
    //                                                         ->required()
    //                                                 ])
    //                                                 ->minItems(1)
    //                                                 ->required()
    //                                         ])
    //                                         ->defaultItems(0)
    //                                         ->reorderable()
    //                                         ->afterStateUpdated(function ($state, $record) {
    //                                             if (!$record || !$state) return;

    //                                             // Clear existing content orders
    //                                             \App\Models\ChapterContentOrder::where('chapter_id', $record->id)->delete();

    //                                             // Create new content orders
    //                                             foreach ($state as $index => $content) {
    //                                                 if (!isset($content['content_type']) || !isset($content['content_id'])) continue;

    //                                                 \App\Models\ChapterContentOrder::create([
    //                                                     'chapter_id' => $record->id,
    //                                                     'content_type' => $content['content_type'],
    //                                                     'content_id' => $content['content_id'],
    //                                                     'order' => $index + 1,
    //                                                 ]);
    //                                             }
    //                                         })
    //                                 ])
    //                         ])

    //                         // ->orderColumn('order')
    //                         ->defaultItems(1)
    //                         ->reorderable('order')
    //                         ]),
    //         ]);
    // }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('course_id')
                    ->relationship('course', 'name')
                    ->preload(),

                Select::make('teacher_id')
                    ->relationship('teacher', 'user.full_name')
                    ->preload()
                    ->options(
                        fn () => \App\Models\Teacher::with('userable')
                            ->get()
                            ->pluck('userable.full_name', 'id')
                    ),

                Forms\Components\Section::make('Chapters')
                    ->schema([
                        Forms\Components\Repeater::make('chapters')
                            ->relationship()
                            ->reorderable()
                            ->orderColumn('order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),

                                // Content Ordering Section
                                Forms\Components\Section::make('Content Order')
                                    ->schema([
                                        Forms\Components\Repeater::make('contentOrder')
                                            ->label('Content Items')
                                            ->relationship()
                                            ->reorderable()
                                            ->orderColumn('order')
                                            ->defaultItems(0)
                                            ->collapseAllAction(fn ($action) => $action->label('Collapse all'))
                                            ->schema([
                                                Forms\Components\Select::make('content_type')
                                                    ->options([
                                                        'free_video' => 'Free Video',
                                                        'premium_video' => 'Premium Video',
                                                        'pdf' => 'PDF Document',
                                                        'quiz' => 'Quiz'
                                                    ])
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn ($state, callable $set) => $set('content_id', null)),

                                                Forms\Components\Select::make('content_id')
                                                    ->label('Content')
                                                    ->options(function (callable $get) {
                                                        $type = $get('content_type');
                                                        if (!$type) return [];

                                                        switch($type) {
                                                            case 'free_video':
                                                                return Media::whereCollectionName('courses-free-video')
                                                                    ->pluck('name', 'id');
                                                            case 'premium_video':
                                                                return Media::whereCollectionName('courses-videos')
                                                                    ->pluck('name', 'id');
                                                            case 'pdf':
                                                                return Media::whereCollectionName('courses-pdfs')
                                                                    ->pluck('name', 'id');
                                                            case 'quiz':
                                                                return \App\Models\Quiz::pluck('title', 'id');
                                                            default:
                                                                return [];
                                                        }
                                                    })
                                                    ->required()
                                                    ->searchable(),
                                            ])
                                            // ->grid(2)
                                            ->itemLabel(fn (array $state): ?string =>
                                                match ($state['content_type'] ?? null) {
                                                    'free_video' => 'Free Video: ' . Media::find($state['content_id'])?->name,
                                                    'premium_video' => 'Premium Video: ' . Media::find($state['content_id'])?->name,
                                                    'pdf' => 'PDF: ' . Media::find($state['content_id'])?->name,
                                                    'quiz' => 'Quiz: ' . \App\Models\Quiz::find($state['content_id'])?->title,
                                                    default => 'New Content Item',
                                                })
                                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $record): array {
                                                    // Auto-set order based on current count for the chapter
                                                    $data['order'] = ChapterContentOrder::where('chapter_id', $record->id)
                                                        ->count() + 1;
                                                    $data['chapter_id'] = $record->id;
                                                    return $data;
                                                }),
                                    ]),

                                // Separate Media Upload Sections
                                Forms\Components\Section::make('Upload Content')
                                    ->columns(2)
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('free_videos')
                                            ->label('Free Videos')
                                            ->collection('courses-free-video')
                                            ->multiple()
                                            ->disk('media')
                                            ->reorderable()
                                            ->downloadable()
                                            ->responsiveImages()
                                            ->maxFiles(20),

                                        SpatieMediaLibraryFileUpload::make('premium_videos')
                                            ->label('Premium Videos')
                                            ->collection('courses-videos')
                                            ->multiple()
                                            ->reorderable()
                                            ->disk('media')
                                            ->downloadable()
                                            ->responsiveImages()
                                            ->maxFiles(20),

                                        SpatieMediaLibraryFileUpload::make('pdfs')
                                            ->label('PDF Documents')
                                            ->collection('courses-pdfs')
                                            ->multiple()
                                            ->disk('media')
                                            ->reorderable()
                                            ->downloadable()
                                            ->maxFiles(50),
                                    ]),

                                // Quiz Management
                                Forms\Components\Section::make('Quizzes')
                                                                ->schema([
                                                                    Forms\Components\Repeater::make('quizzes')
                                                                        ->relationship()
                                                                        // ->orderColumn('order')
                                                                        ->schema([
                                                                            Forms\Components\TextInput::make('title')
                                                                                ->required()
                                                                                ->maxLength(255),
                                                                            Forms\Components\Textarea::make('description')
                                                                                ->maxLength(65535),
                                                                            Forms\Components\TextInput::make('time_limit')
                                                                                ->numeric()
                                                                                ->label('Time Limit (minutes)')
                                                                                ->nullable(),
                                                                            Forms\Components\TextInput::make('passing_score')
                                                                                ->numeric()
                                                                                ->default(60)
                                                                                ->required(),
                                                                            Forms\Components\TextInput::make('number_of_attempts')
                                                                                ->numeric()
                                                                                ->default(3)
                                                                                ->required(),
                                                                            Forms\Components\Toggle::make('is_active')
                                                                                ->default(true),
                                                                            Forms\Components\Repeater::make('questions')
                                                                                ->relationship()
                                                                                ->schema([
                                                                                    Forms\Components\Textarea::make('question_text')
                                                                                        ->required(),
                                                                                    Forms\Components\Repeater::make('options')
                                                                                        ->schema([
                                                                                            Forms\Components\TextInput::make('option')
                                                                                                ->required()
                                                                                        ])
                                                                                        ->minItems(2)
                                                                                        ->maxItems(5)
                                                                                        ->required(),
                                                                                    Forms\Components\Select::make('correct_answer')
                                                                                        ->options(function (Forms\Get $get): array {
                                                                                            $options = $get('options');
                                                                                            if (!$options) return [];
                                                                                            return collect($options)
                                                                                                ->pluck('option')
                                                                                                ->filter()
                                                                                                ->mapWithKeys(fn ($option) => [$option => $option])
                                                                                                ->toArray();
                                                                                        })
                                                                                        ->required(),
                                                                                    Forms\Components\TextInput::make('points')
                                                                                        ->numeric()
                                                                                        ->default(1)
                                                                                        ->required()
                                                                                ])
                                                                                ->minItems(1)
                                                                                ->required()
                                                                        ])
                                                                        ->defaultItems(0)
                                                                        ->reorderable()
                                                                        ->afterStateUpdated(function ($state, $record) {
                                                                            if (!$record || !$state) return;

                                                                            $existingOrders = \App\Models\ChapterContentOrder::where('chapter_id', $record->id)
                                                                                ->where('content_type', 'quiz')
                                                                                ->orderBy('order')
                                                                                ->get();

                                                                            $nextOrder = $existingOrders->isEmpty() ? 1 : $existingOrders->max('order') + 1;

                                                                            foreach ($state as $quiz) {
                                                                                if (!isset($quiz['id'])) continue;

                                                                                \App\Models\ChapterContentOrder::updateOrCreate(
                                                                                    [
                                                                                        'chapter_id' => $record->id,
                                                                                        'content_type' => 'quiz',
                                                                                        'content_id' => $quiz['id']
                                                                                    ],
                                                                                    ['order' => $nextOrder++]
                                                                                );
                                                                            }
                                                                        })
                                                                ])
                                                        ])

                                                        // ->orderColumn('order')
                                                        ->defaultItems(1)
                                                        ->reorderable('order')
                                                        ]),
                                        ]);




    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.userable.full_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCourseTeachers::route('/'),
            'create' => Pages\CreateCourseTeacher::route('/create'),
            'edit' => Pages\EditCourseTeacher::route('/{record}/edit'),
        ];
    }


}
