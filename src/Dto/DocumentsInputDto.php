<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DocumentsInputDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $template,
        #[Assert\NotBlank]
        #[Assert\Type('array')]
        public array $data,
        #[Assert\Type('string')]
        #[Assert\IsNull]
        public ?string $format
    ) {
    }
}
