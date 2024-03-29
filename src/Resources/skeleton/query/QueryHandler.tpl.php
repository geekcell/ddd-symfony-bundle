<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

#[AsMessageHandler]
class <?= $class_name ?> implements QueryHandler
{
    public function __invoke(<?= $query_class_name ?> $query): Collection
    {
    }
}
