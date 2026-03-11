<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Application\Security;

use App\Showcase\Application\Security\ShowcaseAccessDecider;
use App\Showcase\Application\Security\ShowcaseCapabilityVoter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShowcaseAccessDecider::class)]
#[CoversClass(ShowcaseCapabilityVoter::class)]
final class ShowcaseAccessDeciderTest extends TestCase
{
    public function testItGrantsDiagnosticsToAdminsWhenEnforced(): void
    {
        $decider = new ShowcaseAccessDecider(new ShowcaseCapabilityVoter(), true);

        self::assertTrue($decider->canViewDiagnostics(['ROLE_ADMIN']));
        self::assertFalse($decider->canViewDiagnostics(['ROLE_USER']));
    }

    public function testItBypassesChecksWhenEnforcementIsDisabled(): void
    {
        $decider = new ShowcaseAccessDecider(new ShowcaseCapabilityVoter(), false);

        self::assertTrue($decider->canViewDiagnostics([]));
    }
}
