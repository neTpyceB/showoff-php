<?php

declare(strict_types=1);

namespace App\Showcase\Application\Form;

use App\Showcase\Application\Validation\LowercaseCode;
use Symfony\Component\Validator\Constraints as Assert;

final class ShowcaseSettingsInput
{
    #[Assert\NotBlank]
    #[LowercaseCode]
    public string $code = '';

    #[Assert\Length(max: 280)]
    public string $notes = '';
}
