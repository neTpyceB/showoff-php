<?php

declare(strict_types=1);

namespace App\Http\Form;

use Symfony\Component\Validator\Constraints as Assert;

final class PreferencesRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['system', 'light', 'dark'])]
    public string $theme = 'system';
}
