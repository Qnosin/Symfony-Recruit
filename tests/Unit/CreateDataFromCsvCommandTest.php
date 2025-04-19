<?php

namespace App\Tests\Unit;

use App\Command\CreateDataFromCsvCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;


class CreateDataFromCsvCommandTest extends TestCase
{
    private CreateDataFromCsvCommand $dataManager;
    private $serializer;
    private $entityManager;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
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
        $this->serializer->method('decode')->willReturn([]);

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

        $csvRow = [
            ['123', 'MPN123', 'Test Product', 'TestProducer', '5', '99.99', '1234567890123']
        ];


        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('decode')->willReturn($csvRow);


        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');


        $command = new CreateDataFromCsvCommand($serializer, $entityManager);


        $csvPath = sys_get_temp_dir() . '/one_item.csv';
        file_put_contents($csvPath, "123\tMPN123\tTest Product\tTestProducer\t5\t99.99\t1234567890123");


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

    public function testFailsWithUnknownSupplier(): void
    {
        $command = new CreateDataFromCsvCommand($this->serializer, $this->entityManager);

        $testCsvPath = sys_get_temp_dir() . '/unknown_supplier.csv';
        file_put_contents($testCsvPath, "some,data,to,test");

        $input = new ArrayInput([
            'path' => $testCsvPath,
            'supplier' => 'unknown_supplier',
        ]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString('Unknown supplier', $output->fetch());

        unlink($testCsvPath);
    }
}
