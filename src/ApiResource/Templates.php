<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use App\Controller\GetTemplatesController;

#[Get(
    defaults: [],
    controller: GetTemplatesController::class,
)]
class Templates
{
    #[ApiProperty(
        openapiContext: [
            'type' => 'array',
            'items' => ['type' => 'string']
        ]
    )]
    public array $templates;
    public function __construct($templates)
    {
        $this->templates = $templates;
    }
}