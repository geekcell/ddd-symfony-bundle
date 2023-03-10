<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

<?= $use_statements ?>

class <?= $class_name ?> extends ServiceEntityRepository
{
    /**
    * @extends ServiceEntityRepository<<?= $entity_class_name ?>>
    *
    * @method <?= $entity_class_name ?>|null find($id, $lockMode = null, $lockVersion = null)
    * @method <?= $entity_class_name ?>|null findOneBy(array $criteria, array $orderBy = null)
    * @method <?= $entity_class_name ?>[]    findAll()
    * @method <?= $entity_class_name ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $entity_class_name ?>::class);
    }

    public function save(<?= $entity_class_name ?> $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(<?= $entity_class_name ?> $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//    * @return <?= $entity_class_name ?>[] Returns an array of <?= $entity_class_name ?> objects
//    */
//    public function findByExampleField($value): array
//    {
//        return $this->filter(function(QueryBuilder $queryBuilder) use ($value) {
//            $queryBuilder
//                ->andWhere('t.exampleField = :val')
//                ->setParamter('val', $value)
//                ->orderBy('t.id', 'ASC')
//            ;
//        });
//    }

//    public function findOneBySomeField($value): ?<?= $entity_class_name ?><?= "\n" ?>
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
