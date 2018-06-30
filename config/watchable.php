<?php

return [
    'audit_columns' => [
        'creator_column' => 'creator',
        'editor_column' => 'editor'
    ],
    'activity_log' => [
        'model' => \Shipu\Watchable\Models\Activity::class
    ]
];
