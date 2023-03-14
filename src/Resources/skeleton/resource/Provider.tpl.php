<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> implements ProviderInterface
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {}

    /**
    * @inheritDoc
    */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return null;
    }
}
