<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> <?php if ($aggregate_root) { ?>extends AggregateRoot<?php } ?><?= "\n" ?>
{
<?php if ($with_identity): ?>
    /**
     * @var <?= $class_name . ucfirst($with_identity) . "\n" ?>
     */
    private <?= $class_name . ucfirst($with_identity) ?> $<?= $with_identity ?>;

<?php endif ?>
    public function __construct()
    {
<?php if ('id' === $with_identity): ?>
        $this->id = new <?= $class_name ?>Id(1);
<?php elseif ('uuid' === $with_identity): ?>
        $this->uuid = <?= $class_name ?>Uuid::random();
<?php endif; ?>
    }
<?php if ($with_identity): ?>

    /**
     * Get <?= ucfirst($with_identity) . "\n" ?>
     *
     * @return <?= $class_name . ucfirst($with_identity) . "\n" ?>
     */
    public function get<?= ucfirst($with_identity) ?>(): <?= $class_name . ucfirst($with_identity) . "\n" ?>
    {
        return $this-><?= $with_identity ?>;
    }
<?php endif; ?>
}
