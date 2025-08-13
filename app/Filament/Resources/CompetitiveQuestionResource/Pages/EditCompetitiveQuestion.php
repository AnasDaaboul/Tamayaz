<?php

namespace App\Filament\Resources\CompetitiveQuestionResource\Pages;

use App\Filament\Resources\CompetitiveQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetitiveQuestion extends EditRecord
{
    protected static string $resource = CompetitiveQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
