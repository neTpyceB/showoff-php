<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use App\Application\Contact\ApiContactSubmissionService;
use App\Application\Contact\ContactSubmissionStatsService;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Showoff\Core\Domain\Contact\ContactSubmission;

final class GraphqlSchemaProvider
{
    private ?Schema $schema = null;

    public function __construct(
        private readonly ContactSubmissionStatsService $stats,
        private readonly ApiContactSubmissionService $submissionService,
    ) {}

    public function schema(): Schema
    {
        if ($this->schema instanceof Schema) {
            return $this->schema;
        }

        $submissionType = new ObjectType([
            'name' => 'ContactSubmission',
            'fields' => [
                'id' => Type::nonNull(Type::int()),
                'name' => Type::nonNull(Type::string()),
                'email' => Type::nonNull(Type::string()),
                'message' => Type::nonNull(Type::string()),
                'status' => Type::nonNull(Type::string()),
                'submittedAt' => Type::nonNull(Type::string()),
            ],
        ]);

        $statsType = new ObjectType([
            'name' => 'ContactSubmissionStats',
            'fields' => [
                'count' => Type::nonNull(Type::int()),
                'latest' => $submissionType,
            ],
        ]);

        $mutationPayloadType = new ObjectType([
            'name' => 'SubmitContactSubmissionPayload',
            'fields' => [
                'submission' => Type::nonNull($submissionType),
            ],
        ]);

        $mutationInputType = new InputObjectType([
            'name' => 'SubmitContactSubmissionInput',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'email' => Type::nonNull(Type::string()),
                'message' => Type::nonNull(Type::string()),
            ],
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'contactSubmissionStats' => [
                    'type' => Type::nonNull($statsType),
                    'resolve' => fn(): array => $this->stats->get(),
                ],
            ],
        ]);

        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'submitContactSubmission' => [
                    'type' => Type::nonNull($mutationPayloadType),
                    'args' => [
                        'input' => Type::nonNull($mutationInputType),
                    ],
                    'resolve' => function (mixed $rootValue, array $args): array {
                        $input = $args['input'] ?? null;
                        if (!is_array($input)) {
                            throw new UserError('input is required.');
                        }

                        $name = trim($this->stringValue($input, 'name'));
                        $email = trim($this->stringValue($input, 'email'));
                        $message = trim($this->stringValue($input, 'message'));

                        try {
                            $submission = $this->submissionService->submit(
                                name: $name,
                                email: $email,
                                message: $message,
                                source: 'graphql_api',
                            );
                        } catch (\Throwable $exception) {
                            throw new UserError($exception->getMessage());
                        }

                        return [
                            'submission' => $this->normalizeSubmission($submission),
                        ];
                    },
                ],
            ],
        ]);

        $this->schema = new Schema([
            'query' => $queryType,
            'mutation' => $mutationType,
        ]);

        return $this->schema;
    }

    /**
     * @return array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}|null
     */
    private function normalizeSubmission(?ContactSubmission $submission): ?array
    {
        if ($submission === null || $submission->id === null) {
            return null;
        }

        return [
            'id' => $submission->id->value,
            'name' => $submission->name->value,
            'email' => $submission->email->value,
            'message' => $submission->message->value,
            'status' => $submission->status->value,
            'submittedAt' => $submission->submittedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<mixed, mixed> $input
     */
    private function stringValue(array $input, string $key): string
    {
        $value = $input[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}
