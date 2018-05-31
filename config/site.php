<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | This value is site's name.
    */
    'name'      => 'Site Name',
    'copyright' => 'Text',

    /*
    |--------------------------------------------------------------------------
    | Prefix Name
    |--------------------------------------------------------------------------
    |
    | This value is the prefix of URL.
    */
    'frontend_prefix'   => 'frontend',
    'backend_prefix'    => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Page size number
    |--------------------------------------------------------------------------
    |
    | This value is the number records per page.
    */
    'grid_page_size'   => 20,

    /*
    |--------------------------------------------------------------------------
    | Default sort
    |--------------------------------------------------------------------------
    |
    | This value is default order by and sort field.
    */
    'order_by'         => 'updated_at',
    'sort_type'        => 'desc',

    /*
    |--------------------------------------------------------------------------
    | Horizontal or vertical menu setting
    |--------------------------------------------------------------------------
    |
    | Value "true" is horizontal menu. Value "false" is vertical menu
    */
    'horizontal_menu'   => false,

    /*
    |--------------------------------------------------------------------------
    | Api token expire time
    |--------------------------------------------------------------------------
    |
    | Value is number of day
    */
    'token_expire_time' => 7, // day

    /*
    |--------------------------------------------------------------------------
    | Action type
    |--------------------------------------------------------------------------
    |
    | Value is action type
    */
    'action_draft'      => 'draft_save',
    'action_confirm'    => 'confirm',

    /*
    |--------------------------------------------------------------------------
    | API media domain URL
    |--------------------------------------------------------------------------
    |
    | Value is API media domain URL
    */
    'media_domain' => '',
];
