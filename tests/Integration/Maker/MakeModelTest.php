<?php

namespace GeekCell\DddBundle\Tests\Integration\Maker;

use GeekCell\DddBundle\Maker\MakeModel;
use Symfony\Bundle\MakerBundle\Test\MakerTestCase;
use Symfony\Bundle\MakerBundle\Test\MakerTestRunner;
use Symfony\Component\HttpKernel\KernelInterface;

class MakeModelTest extends MakerTestCase
{
    protected function createKernel(): KernelInterface
    {
        return new GeekCellMakerTestKernel('dev', true);
    }

    protected function getMakerClass(): string
    {
        return MakeModel::class;
    }

    public function getTestDetails(): \Generator
    {
        $this->markTestSkipped('just, no....');

        yield 'it_creates_a_new_class_basic' => [$this->createMakerTest()
            ->run(function (MakerTestRunner $runner) {
                $runner->runMaker([
                    // entity class name
                    'TestModel',
                ]);

                $this->assertTrue(true);
            }),
        ];
    }
}
