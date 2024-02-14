<?php

namespace App\Command;

use App\Entity\Champion;
use App\Repository\ChampionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'pfm:update:champion',
    description: 'Updates data for all champions.',
    hidden: false
)]
class UpdateChampionCommand extends UpdateCommand
{
    const ENTITY_TYPE = "Champion";

    /**
     * @var ChampionRepository $championRepository
     */
    private ChampionRepository $championRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private EntityManagerInterface $entityManager;

    public function __construct(ChampionRepository $championRepository, EntityManagerInterface $entityManager)
    {
        $this->championRepository = $championRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommand($output, self::ENTITY_TYPE);
        $championsListEndpoint = 'https://ddragon.leagueoflegends.com/cdn/14.2.1/data/en_US/champion.json';
        $championDetailsEndpoint = 'https://ddragon.leagueoflegends.com/cdn/14.2.1/data/en_US/champion/';
        $championsList = $this->readEndpoint($championsListEndpoint);

        foreach (array_keys($championsList) as $championName) {
            $championDetailsPath = $championDetailsEndpoint . $championName . ".json";
            $championDetails = $this->readEndpoint($championDetailsPath, self::DETAILS_ENDPOINT)[$championName];

            $championEntity = $this->championRepository->findOneBy([
                'codename' => mb_strtolower($championDetails['id'])
            ]);

            if (!$championEntity) {
                $championEntity = new Champion();
                $this->incrementCurrentOutput(self::ACTION_CREATE, $championDetails['name']);
            } else {
                $this->incrementCurrentOutput(self::ACTION_UPDATE, $championDetails['name']);
            }

            $championEntity->setCodename(mb_strtolower($championDetails['id']));
            $championEntity->setName($championDetails['name']);
            $championEntity->setLastUpdate($this->getLastUpdateValue());
            $this->entityManager->persist($championEntity);
            $this->entityManager->flush();
        }

        return $this->finishCommand();
    }
}
