<?php

namespace GeekCell\DddBundle\Tests\Integration\Maker;

use GeekCell\DddBundle\Maker\MakeModel;
use GeekCell\DddBundle\Tests\Integration\GeekCellMakerTestKernel;
use Symfony\Bundle\MakerBundle\Test\MakerTestCase;
use Symfony\Bundle\MakerBundle\Test\MakerTestRunner;
use Symfony\Component\HttpKernel\KernelInterface;


class MakeModelTest extends MakerTestCase
{
    protected function getMakerClass(): string
    {
        return MakeModel::class;
    }

    protected function createKernel(): KernelInterface
    {
        return new GeekCellMakerTestKernel('dev', true);
    }

    public function getTestDetails(): \Generator
    {
        yield 'it_makes_Horse_type' => [$this->createMakerTest()
            ->run(function (MakerTestRunner $runner) {
                $runner->runMaker(
                    [
                        'Horse',    // entity name
                        true,       // aggregate root
                        true,       // use as Doctrine entity
                        true,       // create identity value object
                        false,      // add "Model" suffix
                    ]
                );

                $runner->runTests();
            }),
        ];
    }
}
