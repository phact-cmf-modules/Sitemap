<?php

return [
    [
        'route' => '.xml',
        'target' => [\Modules\Sitemap\Controllers\SitemapController::class, 'xmlIndex'],
        'name' => 'xml'
    ],
    [
        'route' => '/{*:name}.xml',
        'target' => [\Modules\Sitemap\Controllers\SitemapController::class, 'xmlItem'],
        'name' => 'xml_item'
    ],
];