<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> extends AbstractController
{
<?php if (count($dependencies) > 0): ?>
    public function __construct(<?= implode(', ', $dependencies ) ?>)
    {}

<?php endif; ?>
    #[Route('/<?= $route_name_snake ?>', name: '<?= $route_name_snake ?>')]
    public function <?= $route_name ?>(): Response
    {
        return new Response();
    }
}
