<?php

namespace App\Filament\Resources;
use Filament\Forms;
use Filament\Tables;

use App\Models\Teacher;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\TeacherResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Filament\Resources\TeacherResource\Pages\CreateTeacher;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TeacherResource extends Resource
{
    public static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
      $user =auth()->user();

        if ($user->userable_type == "App\Models\Teacher") {
            return $query->where('id', $user->userable_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('teacher-profile')
                ->required()
                 ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif']) // Allowed image formats
                ->imageResizeTargetWidth('420')
                ->imageResizeTargetHeight('480')
     
                ->collection('teacher-profile')
                ->disk('media')
                ->maxSize(20000),
    //             SpatieMediaLibraryFileUpload::make('teacher-profile')
    // ->required()
    // ->imageEditor( 
    //     // [ // Enable cropping with required dimensions
    //     // 'crop' => [
    //     //     'aspect_ratio' => 420 / 480, // Set the required aspect ratio
    //     // ],
    // // ]
    // )
    // ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif']) // Allowed formats
    // ->imageResizeTargetWidth('420') // Target width
    // ->imageResizeTargetHeight('480') // Target height
    // ->collection('teacher-profile') // Media collection
    // ->disk('media') // Storage disk
    // ->maxSize(20000) // Maximum file size in KB
    // ->responsiveImages() // Generates responsive images for better UX
    // ->hint('يرجى اقتصاص الصورة لتتناسب مع الأبعاد المطلوبة (480 × 420).'),



                Fieldset::make('User')
                ->relationship('userable')
                ->schema([
                    Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                      Forms\Components\TextInput::make('school_name')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('Gender')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('mobile_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state) && $state !== auth()->user()->password)
                    ->required(fn (Page $livewire) => ($livewire instanceof CreateUser))
                    ->maxLength(255),
                ]),
                Forms\Components\TextInput::make('info')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('percentage')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user =auth()->user();
        return $table
            ->columns([
                ImageColumn::make('teacher-profile')
                ->label('Image')
                ->circular()
                ->defaultImageUrl(function($record) {
                    $media = $record->media()->where('collection_name', 'teacher-profile')->first();
                    if ($media) {
                        $id = $media->id;
                        $file_name = $media->file_name;
                        return route('get-image', [$id,$file_name ]);
                    }
                }),
                Tables\Columns\TextColumn::make('userable.full_name')->
                searchable()
                ->label('Full name')
                ->sortable(),
                // ->visible($user->userable_type == "App\Models\Teacher"),




                    Tables\Columns\TextColumn::make('userable.mobile_number')->label('Mobile Number')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
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

                RepeatableEntry::make('course')
                ->schema([
                    TextEntry::make('name')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Course Name'),
                    TextEntry::make('total_subs')
                    ->color('info')
                    ->size(TextEntry\TextEntrySize::Small)
                    ->weight(FontWeight::Bold)
                    ->fontFamily(FontFamily::Mono)
                    ->label('Total Subscribers'),
                    TextEntry::make('balance_earned')
                    ->label('Balance Earned')
                    ->state(function ($record) {
                        $teacher = $record->teachers()->first();

                        return $teacher->percentage * $record->price * $record->total_subs;
                    })
                ->color('info')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->label('Course Balance'),
            ])

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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            // 'view' => Pages\ViewTeacher::route('/{record}'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}