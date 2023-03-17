<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

interface <?= $class_name ?> extends Repository
{
    public function findById(<?= $identity_class_name ?> $id): ?<?= $model_class_name ?>;
}
