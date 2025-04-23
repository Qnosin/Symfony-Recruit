<?php

namespace App\Command;

use App\Entity\StockItem;
use App\Processor\LorotomProcessor;
use App\Processor\TrahProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class CreateDataFromCsvCommand extends  Command
{
     protected static  $defaultName = "app:import-csv";
     private SerializerInterface $serializer;
     private EntityManagerInterface $em;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->serializer = $serializer;
        $this->em = $em;
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Imports CSV to stock items.')
            ->addArgument('path', InputArgument::REQUIRED, 'Absolute CSV file path')
            ->addArgument('supplier', InputArgument::REQUIRED, 'Supplier name');
    }

    public function execute(InputInterface $input, OutputInterface $output){
        $path = $input->getArgument('path');
        $supplier = $input->getArgument('supplier');
        $persisted = false;

        if (!file_exists($path)) {
            $output->writeln("<error>File not found at $path</error>");
            return  Command::FAILURE;
        }

        $csvContent = file_get_contents($path);

        $delimiter = match (strtolower($supplier)) {
            'lorotom' => "\t",
            'trah' => ";",
            default => throw new \InvalidArgumentException("Unknown supplier: $supplier"),
        };

        $flag = match (strtolower($supplier)) {
            'lorotom' => false,
            'trah' => true,
            default => false,
        };



        $data = $this->serializer->decode($csvContent, 'csv', [
            CsvEncoder::NO_HEADERS_KEY=> $flag,
            CsvEncoder::DELIMITER_KEY => $delimiter,
        ]);


        $processor = match (strtolower($supplier)) {
            'lorotom' => new LorotomProcessor(),
            'trah' => new TrahProcessor(),
            default => null,
        };


        if (!$processor) {
            $output->writeln("<error>Unknown supplier: $supplier</error>");
            return Command::FAILURE;
        }

        foreach ($data as $row) {
            $processed = $processor->transform($row);

            if($processed === null){
                continue;
            }

            $item = $this->em->getRepository(StockItem::class)
                ->findOneBy(['mpn' => $processed['mpn']]);

            if(!$item){
                $item = new StockItem();
            }


            $item->setMpn($processed['mpn']);
            $item->setEan($processed['ean']);
            $item->setExternalId($processed['externalId']);
            $item->setProducerName($processed['producerName']);
            $item->setQuantity($processed['quantity']);
            $item->setPrice($processed['price']);
            $this->em->persist($item);
            $persisted = true;
        }

        if($persisted){
            $this->em->flush();
        }
        $output->writeln('<info>Import completed</info>');
        return Command::SUCCESS;

    }


    public function getCommandName():string{
         return  self::$defaultName;
    }

}