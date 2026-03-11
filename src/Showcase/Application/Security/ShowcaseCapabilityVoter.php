<?php

declare(strict_types=1);

namespace App\Showcase\Application\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, ShowcaseCapability>
 */
final class ShowcaseCapabilityVoter extends Voter
{
    public const VIEW_DIAGNOSTICS = 'SHOWCASE_VIEW_DIAGNOSTICS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW_DIAGNOSTICS && $subject instanceof ShowcaseCapability;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $subject->diagnosticsEnabled && in_array('ROLE_ADMIN', $token->getRoleNames(), true);
    }
}
