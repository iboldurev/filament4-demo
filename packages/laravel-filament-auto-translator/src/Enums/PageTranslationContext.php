<?php

namespace RalphJSmit\Filament\AutoTranslator\Enums;

enum PageTranslationContext: string
{
    case Actions = 'actions';
    case Form = 'form';
    case Infolist = 'infolist';
    case Table = 'table';
}
