<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Application\Validation;

use App\Showcase\Application\Form\ShowcaseSettingsInput;
use App\Showcase\Application\Validation\LowercaseCodeValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(LowercaseCodeValidator::class)]
final class LowercaseCodeValidatorTest extends TestCase
{
    public function testItRejectsUppercaseCharacters(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $input = new ShowcaseSettingsInput();
        $input->code = 'Invalid-Code';
        $violations = $validator->validate($input);

        self::assertGreaterThan(0, count($violations));
    }

    public function testItAcceptsLowercaseCode(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $input = new ShowcaseSettingsInput();
        $input->code = 'valid-code-101';
        $violations = $validator->validate($input);

        self::assertCount(0, $violations);
    }
}
