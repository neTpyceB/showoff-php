<?php

declare(strict_types=1);

namespace App\Showcase\Application\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

final readonly class ShowcaseAccessDecider
{
    public function __construct(
        private ShowcaseCapabilityVoter $voter,
        private bool $enforceDiagnosticsAccess,
    ) {}

    /**
     * @param list<string> $roles
     */
    public function canViewDiagnostics(array $roles): bool
    {
        if (!$this->enforceDiagnosticsAccess) {
            return true;
        }

        $token = new UsernamePasswordToken(
            user: new InMemoryUser('showcase', null, $roles),
            firewallName: 'main',
            roles: $roles,
        );
        $result = $this->voter->vote(
            $token,
            new ShowcaseCapability(diagnosticsEnabled: true),
            [ShowcaseCapabilityVoter::VIEW_DIAGNOSTICS],
        );

        return $result === VoterInterface::ACCESS_GRANTED;
    }
}
