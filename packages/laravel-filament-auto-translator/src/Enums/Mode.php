<?php

namespace RalphJSmit\Filament\AutoTranslator\Enums;

enum Mode: string
{
    case Strict = 'strict';
    case Balanced = 'balanced';
    case Loose = 'loose';
}
