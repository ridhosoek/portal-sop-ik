<?php

return [
    'allowed_document_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('SOP_ALLOWED_DOCUMENT_HOSTS', '*.sharepoint.com,drive.google.com,docs.google.com,dms.internal,files.internal'))
    ))),

    'allowed_intranet_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('SOP_ALLOWED_INTRANET_HOSTS', '*.internal,*.local,localhost'))
    ))),

    'admin_idle_timeout_minutes' => (int) env('SOP_ADMIN_IDLE_TIMEOUT_MINUTES', 30),
];
