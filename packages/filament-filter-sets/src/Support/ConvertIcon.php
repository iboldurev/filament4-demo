<?php

namespace Archilex\AdvancedTables\Support;

class ConvertIcon
{
    public static function convert(?string $icon): ?string
    {
        if (! $icon) {
            return null;
        }

        if (! Config::convertsIcons()) {
            return $icon;
        }

        $lookup = [
            'adjustments' => 'adjustments-vertical',
            'annotation' => 'chat-bubble-bottom-center-text',
            'archive' => 'archive-box',
            'arrow-circle-down' => 'arrow-down-circle',
            'arrow-circle-left' => 'arrow-left-circle',
            'arrow-circle-right' => 'arrow-right-circle',
            'arrow-circle-up' => 'arrow-up-circle',
            'arrow-narrow-down' => 'arrow-long-down',
            'arrow-narrow-left' => 'arrow-long-left',
            'arrow-narrow-right' => 'arrow-long-right',
            'arrow-narrow-up' => 'arrow-long-up',
            'arrow-sm-left' => 'arrow-small-left',
            'arrow-sm-right' => 'arrow-small-right',
            'arrow-sm-up' => 'arrow-small-up',
            'arrow-sm-down' => 'arrow-small-down',
            'arrows-expand' => 'arrows-pointing-out',
            'badge-check' => 'check-badge',
            'ban' => 'no-symbol',
            'bookmark-alt' => 'bookmark-square',
            'cash' => 'banknotes',
            'chart-square-bar' => 'chart-bar-square',
            'chat-alt-2' => 'chat-bubble-left-right',
            'chat-alt' => 'chat-bubble-left-ellipsis',
            'chat' => 'chat-bubble-oval-left-ellipsis',
            'chip' => 'cpu-chip',
            'clipboard-check' => 'clipboard-document-check',
            'clipboard-copy' => 'clipboard-document',
            'clipboard-list' => 'clipboard-document-list',
            'cloud-download' => 'cloud-arrow-down',
            'cloud-upload' => 'cloud-arrow-up',
            'code' => 'code-bracket',
            'collection' => 'rectangle-stack',
            'color-swatch' => 'swatch',
            'cursor-click' => 'cursor-arrow-rays',
            'database' => 'circle-stack',
            'desktop-computer' => 'computer-desktop',
            'device-mobile' => 'device-phone-mobile',
            'document-add' => 'document-plus',
            'document-download' => 'document-arrow-down',
            'document-remove' => 'document-minus',
            'document-report' => 'document-chart-bar',
            'document-search' => 'document-magnifying-glass',
            'dots-circle-horizontal' => 'ellipsis-horizontal-circle',
            'dots-horizontal' => 'ellipsis-horizontal',
            'dots-vertical' => 'ellipsis-vertical',
            'download' => 'arrow-down-tray',
            'duplicate' => 'square-2-stack',
            'emoji-happy' => 'face-smile',
            'emoji-sad' => 'face-frown',
            'external-link' => 'arrow-top-right-on-square',
            'exclamation' => 'exclamation-triangle',
            'eye-off' => 'eye-slash',
            'fast-forward' => 'forward',
            'filter' => 'funnel',
            'folder-add' => 'folder-plus',
            'folder-download' => 'folder-arrow-down',
            'folder-remove' => 'folder-minus',
            'globe' => 'globe-americas',
            'hand' => 'hand-raised',
            'inbox-in' => 'inbox-arrow-down',
            'library' => 'building-library',
            'lightning-bolt' => 'bolt',
            'location-marker' => 'map-pin',
            'login' => 'arrow-left-on-rectangle',
            'logout' => 'arrow-right-on-rectangle',
            'mail-open' => 'envelope-open',
            'mail' => 'envelope',
            'menu-alt-1' => 'bars-3-center-left',
            'menu-alt-2' => 'bars-3-bottom-left',
            'menu-alt-3' => 'bars-3-bottom-right',
            'menu-alt-4' => 'bars-2',
            'menu' => 'bars-3',
            'minus-sm' => 'minus-small',
            'music-note' => 'musical-note',
            'office-building' => 'building-office',
            'pencil-alt' => 'pencil-square',
            'phone-incoming' => 'phone-arrow-down-left',
            'phone-missed-call' => 'phone-x-mark',
            'phone-outgoing' => 'phone-arrow-up-right',
            'photograph' => 'photo',
            'plus-sm' => 'plus-small',
            'puzzle' => 'puzzle-piece',
            'qrcode' => 'qr-code',
            'receipt-tax' => 'receipt-percent',
            'refresh' => 'arrow-path',
            'reply' => 'arrow-uturn-left',
            'rewind' => 'backward',
            'save-as' => 'arrow-down-on-square-stack',
            'save' => 'arrow-down-on-square',
            'search-circle' => 'magnifying-glass-circle',
            'search' => 'magnifying-glass',
            'selector' => 'chevron-up-down',
            'sort-ascending' => 'bars-arrow-up',
            'sort-descending' => 'bars-arrow-down',
            'speakerphone' => 'megaphone',
            'status-offline' => 'signal-slash',
            'status-online' => 'signal',
            'support' => 'lifebuoy',
            'switch-horizontal' => 'arrows-right-left',
            'switch-vertical' => 'arrows-up-down',
            'table' => 'table-cells',
            'template' => 'rectangle-group',
            'terminal' => 'command-line',
            'thumb-down' => 'hand-thumb-down',
            'thumb-up' => 'hand-thumb-up',
            'translate' => 'language',
            'trending-down' => 'arrow-trending-down',
            'trending-up' => 'arrow-trending-up',
            'upload' => 'arrow-up-tray',
            'user-add' => 'user-plus',
            'user-remove' => 'user-minus',
            'view-boards' => 'view-columns',
            'view-grid-add' => 'squares-plus',
            'view-grid' => 'squares-2x2',
            'view-list' => 'bars-4',
            'volume-off' => 'speaker-x-mark',
            'volume-up' => 'speaker-wave',
            'x' => 'x-mark',
            'zoom-in' => 'magnifying-glass-plus',
            'zoom-out' => 'magnifying-glass-minus',
        ];

        $prefix = substr($icon, 0, 11);
        $icon = substr($icon, 11);

        if (! array_key_exists($icon, $lookup)) {
            return null;
        }

        return $prefix . $lookup[$icon];
    }
}
