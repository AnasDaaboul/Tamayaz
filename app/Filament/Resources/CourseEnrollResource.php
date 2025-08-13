<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseEnrollResource\Pages;
use App\Filament\Resources\CourseEnrollResource\RelationManagers;
use App\Models\CourseEnroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseEnrollResource extends Resource
{
    protected static ?string $model = CourseEnroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cobon_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('student_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('course_teacher_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courseTeacher.course.name')
                ->label('course name'),
                Tables\Columns\TextColumn::make('courseTeacher.teacher.userable.full_name')
                ->label('Teacher Name')
                ,
                    // ->numeric()
                    // ->sortable(),÷
                Tables\Columns\TextColumn::make('student.userable.full_name')
                    ->label('Student Name')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('course_teacher_id')
                //     ->numeric()
                //     ->sortable(),
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
            'index' => Pages\ListCourseEnrolls::route('/'),
            'create' => Pages\CreateCourseEnroll::route('/create'),
            'edit' => Pages\EditCourseEnroll::route('/{record}/edit'),
        ];
    }
}