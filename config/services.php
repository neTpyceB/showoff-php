<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Showoff\Core\Bootstrap\Factory\ConsoleApplicationFactory;
use Showoff\Core\Bootstrap\Factory\HttpKernelFactory;
use Showoff\Core\Bootstrap\Factory\TwigEnvironmentFactory;
use Showoff\Core\Domain\Clock\Clock;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionEventRepository;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Showoff\Core\Domain\Shared\TransactionBoundary;
use Showoff\Core\Health\DirectoryManager;
use Showoff\Core\Health\NativeDirectoryManager;
use Showoff\Core\Health\NativeRuntimeInspector;
use Showoff\Core\Health\RuntimeInspector;
use Showoff\Core\Http\Contact\HeaderSubmissionSourceStrategy;
use Showoff\Core\Http\Contact\SubmissionSourceStrategy;
use Showoff\Core\Http\HttpKernel;
use Showoff\Core\Persistence\Clock\SystemClock;
use Showoff\Core\Persistence\Connection\PdoTransactionManager;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionEventRepository;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionRepository;
use Showoff\Core\Persistence\Migration\PdoMigratorFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;

return static function (ContainerBuilder $container): void {
    $container->setAlias(PsrContainerInterface::class, 'service_container');

    $container->autowire(NativeRuntimeInspector::class)->setPublic(true);
    $container->autowire(NativeDirectoryManager::class)->setPublic(true);
    $container->setAlias(RuntimeInspector::class, NativeRuntimeInspector::class);
    $container->setAlias(DirectoryManager::class, NativeDirectoryManager::class);

    $container->autowire(PdoTransactionManager::class)->setPublic(true);
    $container->setAlias(TransactionBoundary::class, PdoTransactionManager::class);
    $container->autowire(PdoContactSubmissionRepository::class)->setPublic(true);
    $container->autowire(PdoContactSubmissionEventRepository::class)->setPublic(true);
    $container->setAlias(ContactSubmissionRepository::class, PdoContactSubmissionRepository::class);
    $container->setAlias(ContactSubmissionEventRepository::class, PdoContactSubmissionEventRepository::class);
    $container->autowire(SystemClock::class)->setPublic(true);
    $container->setAlias(Clock::class, SystemClock::class);

    $container->autowire(PdoMigratorFactory::class)->setPublic(true);
    $container->register('showoff.persistence.migrator')
        ->setClass(\Showoff\Core\Persistence\Migration\PdoMigrator::class)
        ->setFactory([new Reference(PdoMigratorFactory::class), 'create'])
        ->setPublic(true);
    $container->setAlias(\Showoff\Core\Persistence\Migration\PdoMigrator::class, 'showoff.persistence.migrator');

    $container->autowire(HeaderSubmissionSourceStrategy::class)->setPublic(true);
    $container->setAlias(SubmissionSourceStrategy::class, HeaderSubmissionSourceStrategy::class);

    $container->autowire(TwigEnvironmentFactory::class)->setPublic(true);
    $container->register(Environment::class)
        ->setFactory([new Reference(TwigEnvironmentFactory::class), 'create'])
        ->setArguments(['%project_root%'])
        ->setPublic(true);

    $container->autowire(HttpKernelFactory::class)->setPublic(true);
    $container->register(HttpKernel::class)
        ->setFactory([new Reference(HttpKernelFactory::class), 'create'])
        ->setPublic(true);

    $container->autowire(ConsoleApplicationFactory::class)->setPublic(true);
    $container->register(Application::class)
        ->setFactory([new Reference(ConsoleApplicationFactory::class), 'create'])
        ->setPublic(true);

    $container->autowire(\Showoff\Core\Config\ConfigRedactor::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Health\SystemHealthChecker::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Console\Command\AboutCommand::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Console\Command\ConfigDumpCommand::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Console\Command\HealthCheckCommand::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Console\Command\DatabaseMigrateCommand::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Console\Command\DatabaseStatusCommand::class)->setPublic(true);

    $container->autowire(\Showoff\Core\Http\View\TwigViewRenderer::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Session\NativeSessionFactory::class)->setPublic(true);
    $container->setAlias(
        \Showoff\Core\Http\Session\SessionFactory::class,
        \Showoff\Core\Http\Session\NativeSessionFactory::class,
    );
    $container->autowire(\Showoff\Core\Http\Session\WebSessionManager::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Form\FormTokenManager::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Form\ContactFormHandler::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Form\PreferencesFormHandler::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Domain\Contact\SubmitContactSubmission::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Controller\HomeController::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Controller\ContactController::class)->setPublic(true);
    $container->autowire(\Showoff\Core\Http\Controller\PreferencesController::class)->setPublic(true);
};
