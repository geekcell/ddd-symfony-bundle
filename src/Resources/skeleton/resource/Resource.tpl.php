<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

<?php if ($configure_with_attributes): ?>
#[ApiResource(
    provider: <?= $provider_class_name ?>::class,
    processor: <?= $processor_class_name ?>::class,
)]
<?php endif; ?>
final class <?= $class_name ?><?= "\n" ?>
{
    /**
    * Convenience factory method to create the resource from an instance of the <?= $entity_class_name ?> model
    *
    * @param <?= $entity_class_name ?> $model
    *
    * @return static
    */
    public static function create(<?= $entity_class_name ?> $model): static
    {
        return new static();
    }
}
