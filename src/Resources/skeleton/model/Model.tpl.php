<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

<?php if (isset($as_entity) && true === $as_entity): ?>
#[ORM\Entity]
<?php endif; ?>
class <?= $class_name ?> <?php if ($extends_aggregate_root) { ?>extends AggregateRoot<?php } ?><?= "\n" ?>
{
<?php if (isset($identity_type)): ?>
    /**
     * @var <?= $identity_class . "\n" ?>
     */
<?php if (isset($as_entity) && true === $as_entity): ?>
    #[ORM\Id]
    #[ORM\Column(type: <?= $type_class ?>::NAME)]
<?php endif; ?>
    private <?= $identity_class ?> $<?= $identity_type ?>;

<?php endif ?>
    public function __construct()
    {
<?php if (isset($identity_type) && 'id' === $identity_type): ?>
        $this->id = new <?= $identity_class ?>(1);
<?php elseif (isset($identity_type) && 'uuid' === $identity_type): ?>
        $this->uuid = <?= $identity_class ?>::random();
<?php endif; ?>
    }
<?php if (isset($identity_type)): ?>

    /**
     * Get <?= ucfirst($identity_type) . "\n" ?>
     *
     * @return <?= $identity_class . "\n" ?>
     */
    public function get<?= ucfirst($identity_type) ?>(): <?= $identity_class . "\n" ?>
    {
        return $this-><?= $identity_type ?>;
    }
<?php endif; ?>
}
