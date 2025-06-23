<?php

namespace RalphJSmit\Filament\AutoTranslator\Enums;

enum TableGroup: string
{
    case Actions = 'actions';
    case BulkActions = 'bulk_actions';
    case Columns = 'columns';
    case Filters = 'filters';
    case Groups = 'groups';
}
