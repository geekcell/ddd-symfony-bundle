<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> implements ProcessorInterface
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {}

    /**
    * @inheritDoc
    */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
    }
}
