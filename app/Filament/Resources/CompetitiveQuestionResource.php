<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompetitiveQuestionResource\Pages;
use App\Filament\Resources\CompetitiveQuestionResource\RelationManagers;
use App\Models\CompetitiveQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Select;

class CompetitiveQuestionResource extends Resource
{
    protected static ?string $model = CompetitiveQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('unit_id')
                ->relationship('unit' , 'name')
                ->preload(),

Select::make('difficulty')
    ->options([
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard'
    ])
    ->required()
    ->default('medium'),

                

                Forms\Components\TextInput::make('question_text')
                ->required(),
            Forms\Components\Repeater::make('options')
    ->schema([
        Forms\Components\TextInput::make('option')
            ->required()
    ]),
Select::make('correct_answer')
    ->options(function ($get) {
        return collect($get('options') ?? [])
            ->filter(fn($item) => !empty($item['option']))
            ->pluck('option', 'option');
    })
    ->required()
                ->required()
                // ->minItems(2)
                ->columns(1),

                
            
                SpatieMediaLibraryFileUpload::make('Questions-images')
                ->collection('Questions-images')
                ->disk('media')
                ->maxSize(20000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListCompetitiveQuestions::route('/'),
            'create' => Pages\CreateCompetitiveQuestion::route('/create'),
            'edit' => Pages\EditCompetitiveQuestion::route('/{record}/edit'),
        ];
    }
}
