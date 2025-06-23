<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager as BaseRelationManager;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasResourceRelationManagerTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class RelationManager extends BaseRelationManager implements HasTranslations
{
    use HasResourceRelationManagerTranslations;
}
