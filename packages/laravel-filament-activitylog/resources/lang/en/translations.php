<?php

return [
    'data' => [
        'timeline-configuration-data' => [
            'empty-state-heading' => 'No activities yet',
            'empty-state-description' => 'This :modelLabel has currently not logged any activity yet.',
        ],
    ],
    'components' => [
        'timeline' => [
            'search' => [
                'placeholder' => 'Type to search',
            ],
        ],
    ],
    'actions' => [
        'timeline-action' => [
            'label' => 'Activities',
            'modal_cancel_action_label' => 'Close',
        ],
    ],
    'activity-timeline-item' => [
        'event-descriptions' => [
            'created' => [
                'causer' => '**:causerName** created the :modelLabel.',
                'causer-relationship' => '**:causerName** added a related :relationshipName.',
                'causer-relationship-related-record-title' => '**:causerName** added related :relationshipName **:relatedRecordTitle**.',
                'without-causer' => 'The :modelLabel was created.',
                'without-causer-relationship' => 'A related :relationshipName was created.',
                'without-causer-relationship-related-record-title' => 'A related :relationshipName **:relatedRecordTitle** was created.',
            ],
            'updated' => [
                'causer-changes-summary' => '**:causerName** updated :changesSummary.',
                'causer-changes-summary-relationship' => '**:causerName** updated related :relationshipName :changesSummary.',
                'causer-changes-summary-relationship-related-record-title' => '**:causerName** updated related :relationshipName **:relatedRecordTitle** :changesSummary.',
                'causer-without-changes-summary' => '**:causerName** updated the :modelLabel.',
                'causer-without-changes-summary-relationship' => '**:causerName** updated related :relationshipName.',
                'causer-without-changes-summary-relationship-related-record-title' => '**:causerName** updated related :relationshipName **:relatedRecordTitle**.',
                'without-causer-changes-summary' => 'The :modelLabel was updated by setting :changesSummary.',
                'without-causer-changes-summary-relationship' => 'A related :relationshipName was updated by setting :changesSummary.',
                'without-causer-changes-summary-relationship-related-record-title' => 'The related :relationshipName **:relatedRecordTitle** was updated by setting :changesSummary.',
                'without-causer-without-changes-summary' => 'The :modelLabel was updated.',
                'without-causer-without-changes-summary-relationship' => 'A related :relationshipName was updated.',
                'without-causer-without-changes-summary-relationship-related-record-title' => 'The related :relationshipName **:relatedRecordTitle** was updated.',
            ],
            'deleted' => [
                'causer' => '**:causerName** deleted the :modelLabel.',
                'causer-relationship' => '**:causerName** deleted a related :relationshipName.',
                'causer-relationship-related-record-title' => '**:causerName** deleted the related :relationshipName **:relatedRecordTitle**.',
                'without-causer' => 'The :modelLabel was deleted.',
                'without-causer-relationship' => 'A related :relationshipName was deleted.',
                'without-causer-relationship-related-record-title' => 'The related :relationshipName **:relatedRecordTitle** was deleted.',
            ],
            'restored' => [
                'causer' => '**:causerName** restored the :modelLabel.',
                'causer-relationship' => '**:causerName** restored a related :relationshipName.',
                'causer-relationship-related-record-title' => '**:causerName** restored the related :relationshipName **:relatedRecordTitle**.',
                'without-causer' => 'The :modelLabel was restored.',
                'without-causer-relationship' => 'A related :relationshipName was restored.',
                'without-causer-relationship-related-record-title' => 'The related :relationshipName **:relatedRecordTitle** was restored.',
            ],
            'custom' => [
                'causer' => '**:causerName** :event the :modelLabel.',
                'causer-relationship' => '**:causerName** :event a related :relationshipName.',
                'causer-relationship-related-record-title' => '**:causerName** :event the related :relationshipName **:relatedRecordTitle**.',
                'without-causer' => 'The :modelLabel was :event.',
                'without-causer-relationship' => 'A related :relationshipName was :event.',
                'without-causer-relationship-related-record-title' => 'The related :relationshipName **:relatedRecordTitle** was :event.',
            ],
            'no-subject' => [
                'causer' => '**:causerName** :event.',
                'causer-relationship' => '**:causerName** :event related :relationshipName.',
                'causer-relationship-related-record-title' => '**:causerName** :event related :relationshipName **:relatedRecordTitle**.',
                'without-causer' => ':event.',
                'without-causer-relationship' => 'Related :relationshipName :event.',
                'without-causer-relationship-related-record-title' => 'Related :relationshipName **:relatedRecordTitle** :event.',
            ],
            'changesSummary' => [
                'attribute' => '**:attributeLabel** to **:newAttributeValue**',
                'attributeWithOld' => '**:attributeLabel** from :oldAttributeValue to **:newAttributeValue**',
                'attributeFromBlankWithOld' => '**:attributeLabel** from an empty field to **:newAttributeValue**',
                'attributeFromBlankToBlankWithOld' => '**:attributeLabel** from an empty field to an empty field',
                'attributeToBlank' => '**:attributeLabel** to an empty field',
                'attributeToBlankWithOld' => '**:attributeLabel** from :oldAttributeValue to an empty field',
                'finalGlue' => ' and ',
                'values' => [
                    'boolean-1' => 'true',
                    'boolean-0' => 'false',
                ],
            ],
        ],
        'actions' => [
            'view_batch' => [
                'label' => 'Related history',
                'modal_heading' => 'View related history',
                'modal_description' => 'This event is part of a batch of events. Below you can see which other events have been part of this batch, including the event you selected.',
            ],
        ],
    ],
];
