<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $type_class ?> extends <?= $extends_type_class . "\n" ?>
{
    public const NAME = '<?= $type_name ?>';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getIdType(): string
    {
        return <?= $identity_class ?>::class;
    }
}
