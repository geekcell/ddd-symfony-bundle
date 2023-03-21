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
    public function __construct(
<?php if ($configure_with_attributes): ?>
        #[ApiProperty(identifier: true)]
<?php endif; ?>
<?php if ($configure_with_uuid): ?>
        public string $uuid,
<?php else: ?>
        public int $id,
<?php endif; ?>
        // TODO: Add more properties ...
    ) {}

    /**
    * Convenience factory method to create the resource from an instance of the <?= $entity_class_name ?> model
    *
    * @param <?= $entity_class_name ?> $model
    *
    * @return static
    */
    public static function create(<?= $entity_class_name ?> $model): static
    {
        return new static(
<?php if ($configure_with_uuid): ?>
            strval($model->getUuid()),
<?php else: ?>
            intval($model->getId()),
<?php endif; ?>
            // TODO: Initialize further ...
        );
    }
}
