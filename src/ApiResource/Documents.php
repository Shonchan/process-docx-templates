<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use App\Controller\DocumentsController;
use App\Dto\DocumentsInputDto;

#[Post(
    controller: DocumentsController::class,
    input: DocumentsInputDto::class,
)]
class Documents
{

}