<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use App\Module\Analytics\Api\AnalyticsPublicApi;
use App\Module\Contact\Api\ContactPublicApi;
use App\Module\Contact\Api\ContactSubmissionInput;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

final class GraphqlSchemaProvider
{
    private ?Schema $schema = null;

    public function __construct(
        private readonly ContactPublicApi $contactApi,
        private readonly AnalyticsPublicApi $analyticsApi,
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

        $processingType = new ObjectType([
            'name' => 'ContactSubmissionProcessingStats',
            'fields' => [
                'processed' => Type::nonNull(Type::int()),
                'lastEmail' => Type::string(),
                'lastOccurredAt' => Type::string(),
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
                    'resolve' => fn(): array => $this->contactApi->stats()->toArray(),
                ],
                'contactSubmissionProcessing' => [
                    'type' => Type::nonNull($processingType),
                    'resolve' => fn(): array => $this->analyticsApi->contactSubmissionProcessing()->toArray(),
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
                            $submission = $this->contactApi->submit(new ContactSubmissionInput(
                                name: $name,
                                email: $email,
                                message: $message,
                                source: 'graphql_api',
                            ));
                        } catch (\Throwable $exception) {
                            throw new UserError($exception->getMessage());
                        }

                        return [
                            'submission' => $submission->toArray(),
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
     * @param array<mixed, mixed> $input
     */
    private function stringValue(array $input, string $key): string
    {
        $value = $input[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}
