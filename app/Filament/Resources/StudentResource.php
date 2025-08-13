<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->default(null),
                //     Select::make('student_id')
                // ->relationship(
                // name: 'student',
                // titleAttribute: 'user.full_name'
                // )
                // ->preload()
                // ->options(
        // fn () => \App\Models\Teacher::query()
        //     ->with('userable')
        //     ->get()
        //     ->pluck('userable.full_name', 'id')
    // ),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('userable.full_name')
                    ->label('Full name')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('userable.email')
                    ->label('Email')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('userable.school_name')
                    ->label('School name')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('userable.Gender')
                    ->label('Gender')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('userable.mobile_number')
                    ->label('Mobile Number')
                    ->sortable(),

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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}