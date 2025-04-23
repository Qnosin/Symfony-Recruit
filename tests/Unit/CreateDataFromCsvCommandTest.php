<?php

namespace App\Tests\Unit;

use App\Command\CreateDataFromCsvCommand;
use App\Processor\LorotomProcessor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;



class CreateDataFromCsvCommandTest extends TestCase
{
    private CreateDataFromCsvCommand $dataManager;
    private $serializer;
    private $entityManager;

    protected function setUp(): void
    {
        $this->serializer = new Serializer([], [new CsvEncoder()]);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->dataManager = new CreateDataFromCsvCommand(
            $this->serializer,
            $this->entityManager
        );
    }

    public function testShouldHaveANameForCommand(): void
    {
        $expected = "app:import-csv";
        $this->assertEquals($expected, $this->dataManager->getCommandName());
    }

    public function testShouldReturnEmptyDataInArray(): void
    {

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock->method('findOneBy')->willReturn(null);
        $this->entityManager->method('getRepository')->willReturn($repositoryMock);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $command = new CreateDataFromCsvCommand($this->serializer, $this->entityManager);

        $csvPath = sys_get_temp_dir() . '/empty.csv';
        file_put_contents($csvPath, '');

        $input = new ArrayInput([
            'path' => $csvPath,
            'supplier' => 'lorotom',
        ]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Import completed', $output->fetch());

        unlink($csvPath);
    }

    public function testShouldReturnOneDataInArray(): void
    {

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock->method('findOneBy')->willReturn(null);


        $this->entityManager->method('getRepository')->willReturn($repositoryMock);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $command = new CreateDataFromCsvCommand($this->serializer, $this->entityManager);

        $csvPath = sys_get_temp_dir() . '/one_item.csv';
        file_put_contents($csvPath, "123;MPN123;Test Product;TestProducer;5;99.99;1234567890123");

        $input = new ArrayInput([
            'path' => $csvPath,
            'supplier' => 'trah',
        ]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Import completed', $output->fetch());

        unlink($csvPath);
    }

    public function testFailsWithUnknownSupplier(): void
    {
        $command = new CreateDataFromCsvCommand($this->serializer, $this->entityManager);

        $testCsvPath = sys_get_temp_dir() . '/unknown_supplier.csv';
        file_put_contents($testCsvPath, "dummy,data,to,test");

        $input = new ArrayInput([
            'path' => $testCsvPath,
            'supplier' => 'unknown_supplier',
        ]);
        $output = new BufferedOutput();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown supplier: unknown_supplier');
        $command->run($input, $output);


        unlink($testCsvPath);
    }
}
