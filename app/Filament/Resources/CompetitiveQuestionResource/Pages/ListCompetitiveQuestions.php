<?php

namespace App\Filament\Resources\CompetitiveQuestionResource\Pages;

use App\Filament\Resources\CompetitiveQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetitiveQuestions extends ListRecords
{
    protected static string $resource = CompetitiveQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
