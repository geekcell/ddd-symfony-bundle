<doctrine-mapping xmlns="https://doctrine-project.org/schemas/orm/doctrine-mapping"
      xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="https://doctrine-project.org/schemas/orm/doctrine-mapping
                          https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <entity name="<?= $model_class ?>" table="<?= $table_name ?>">
<?php if ($has_identity): ?>
        <id name="<?= $identity_column_name ?>" type="<?= $type_name ?>" column="<?= $identity_column_name ?>"/>

<?php endif; ?>
        <!-- Add your fields here -->
    </entity>
</doctrine-mapping>
