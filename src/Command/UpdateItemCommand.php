<?php

namespace App\Command;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'pfm:update:item',
    description: 'Updates data for all items.',
    hidden: false
)]
class UpdateItemCommand extends UpdateCommand
{
    const ENTITY_TYPE = "Item";

    /**
     * @var ItemRepository $itemRepository
     */
    private ItemRepository $itemRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private EntityManagerInterface $entityManager;

    public function __construct(ItemRepository $itemRepository, EntityManagerInterface $entityManager)
    {
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommand($output, self::ENTITY_TYPE);
        $itemListEndpoint = 'https://ddragon.leagueoflegends.com/cdn/14.2.1/data/en_US/item.json';
        $itemList = $this->readEndpoint($itemListEndpoint);

        foreach ($itemList as $itemId => $itemDetails) {
            $itemEntity = $this->itemRepository->findOneBy([
                'itemId' => $itemId
            ]);

            if (!$itemEntity) {
                $itemEntity = new Item();
                $this->incrementCurrentOutput(self::ACTION_CREATE, $itemDetails['name']);
            } else {
                $this->incrementCurrentOutput(self::ACTION_UPDATE, $itemDetails['name']);
            }

            $itemEntity->setItemId($itemId);
            $itemEntity->setName($itemDetails['name']);
            $itemEntity->setLastUpdate($this->getLastUpdateValue());
            $this->entityManager->persist($itemEntity);
            $this->entityManager->flush();
        }

        return $this->finishCommand();
    }
}
