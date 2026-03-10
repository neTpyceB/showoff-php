<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use App\Security\PasswordHasher;
use App\Security\Role;
use App\Security\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:security:create-user', description: 'Create a local application user account.')]
final class SecurityCreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('role', InputArgument::OPTIONAL, 'admin|user', 'user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailRaw = $input->getArgument('email');
        $passwordRaw = $input->getArgument('password');
        $roleRaw = $input->getArgument('role');
        if (!is_string($emailRaw) || !is_string($passwordRaw) || !is_string($roleRaw)) {
            $io->error('Invalid command arguments.');

            return Command::INVALID;
        }

        $email = trim($emailRaw);
        $password = $passwordRaw;
        $roleInput = strtolower(trim($roleRaw));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email address.');

            return Command::INVALID;
        }

        if (strlen($password) < 12) {
            $io->error('Password must be at least 12 characters.');

            return Command::INVALID;
        }

        $role = match ($roleInput) {
            Role::ADMIN->value => Role::ADMIN,
            Role::USER->value => Role::USER,
            default => null,
        };
        if (!$role instanceof Role) {
            $io->error('Role must be "admin" or "user".');

            return Command::INVALID;
        }

        if ($this->users->findByEmail($email) !== null) {
            $io->error('User already exists.');

            return Command::FAILURE;
        }

        $user = $this->users->create($email, $this->passwordHasher->hash($password), $role);
        $io->success(sprintf('Created user #%d (%s).', $user->id, $user->role->value));

        return Command::SUCCESS;
    }
}
