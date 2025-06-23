<?php

namespace RalphJSmit\Filament\AutoTranslator\Filament\Clusters;

use Filament\Clusters\Cluster as BaseCluster;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasClusterTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class Cluster extends BaseCluster implements HasTranslations
{
    use HasClusterTranslations;
}
