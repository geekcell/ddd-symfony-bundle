<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> extends OrmRepository implements <?= $interface_class_name ?>
{
    public function findById(<?= $identity_class_name ?> $id): ?<?= $model_class_name ?>
    {
        // TODO: Implement me!

        return null;
    }

    // public function findByExampleField($value): self
    // {
    //     return $this->filter(function(QueryBuilder $queryBuilder) use ($value) {
    //         $queryBuilder
    //             ->andWhere('t.exampleField = :val')
    //             ->setParameter('val', $value)
    //             ->orderBy('t.id', 'ASC')
    //         ;
    //     });
    // }
}
