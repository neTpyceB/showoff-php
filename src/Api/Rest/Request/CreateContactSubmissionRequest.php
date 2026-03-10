<?php

declare(strict_types=1);

namespace App\Api\Rest\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateContactSubmissionRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 5000)]
    public string $message = '';
}
