<?php

namespace App\Command;

use App\Entity\Base\Asset;
use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\Catalog;
use App\Entity\Core\CatalogItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use GuzzleHttp\Client;

class InitCommand extends Command {
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, $name = null) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:init")
            ->setDescription("Create root user and role");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
		$userRepo = $this->entityManager->getRepository(User::class);
        $root = $userRepo->findOneBy(["username" => "root"]);
        if (empty($root)) {
            $output->writeln("Creating root users");
            $root = new User();
        }
		$root->setUsername("root");
		$root->setFullName("root");
		$password = md5(random_bytes(10));
		$passwordHash = $this->passwordEncoder->encodePassword($root, $password);
		$root->setPassword($passwordHash);
		$output->writeln("Username: root");
		$output->writeln("Password: ".$password);

		$grpRepo = $this->entityManager->getRepository(SecurityGroup::class);
		$adminGroup = $grpRepo->findOneBy(["siteToken" => "ROLE_ADMIN"]);
		if (empty($adminGroup)) {
			$output->writeln("Creating Admin Group");
			$adminGroup = new SecurityGroup();
		}
		$adminGroup->setName("Administrator Group");
		$adminGroup->setSiteToken("ROLE_ADMIN");
        if (!$adminGroup->getChildren()->contains($root)) {
            $adminGroup->getChildren()->add($root);
        }

        $testUser = $userRepo->findOneBy(["username" => "test.user"]);
        if (empty($testUser)) {
            $output->writeln("Creating test users");
            $testUser = new User();
            $testUser->setUsername("test.user");
            $testUser->setFullName("Test User");
            $password = md5("password");
            $passwordHash = $this->passwordEncoder->encodePassword($testUser, $password);
            $testUser->setPassword($passwordHash);
            $output->writeln("Username: root");
            $output->writeln("Password: password");
        }

        $userGroup = $grpRepo->findOneBy(["siteToken" => "ROLE_USER"]);
        if (empty($userGroup)) {
            $output->writeln("Creating User Group");
            $userGroup = new SecurityGroup();
        }
        $userGroup->setName("User Group");
        $userGroup->setSiteToken("ROLE_USER");
        if (!$userGroup->getChildren()->contains($testUser)) {
            $userGroup->getChildren()->add($testUser);
        }

        $defaultCatalogs = [
            [
                "shortString" => "default_1",
                "friendlyName" => "分類一",
                "items" => [
                    [
                        "name" => "Testing item 1",
                        "region" => "region_1",
                    ],
                    [
                        "name" => "Testing item 2",
                        "region" => "region_1",
                    ],
                    [
                        "name" => "Testing item 3",
                        "region" => "region_2",
                    ],
                    [
                        "name" => "Testing item 4",
                        "region" => "region_3",
                    ],
                ]
            ], [
                "shortString" => "default_2",
                "friendlyName" => "分類二"
            ], [
                "shortString" => "default_3",
                "friendlyName" => "Type 3"
            ],
        ];
        $defaultItems = [
            [
                "name" => "Testing item 1",
                "region" => "region_1",
            ],
            [
                "name" => "Testing item 2",
                "region" => "region_1",
            ],
            [
                "name" => "Testing item 3",
                "region" => "region_2",
            ],
            [
                "name" => "Testing item 4",
                "region" => "region_3",
            ],
        ];

        $pics = [];
        $client = new Client([
            "base_uri" => "http://picsum.photos"
        ]);
        while (count($pics) < 10) {
            try {
                $output->writeln("Trying to get random image...");
                $response = $client->request("GET", "/150/150?random");
                $output->writeln("Got HTTP status code ". $response->getStatusCode());
                $pics[] = base64_encode($response->getBody());
            } catch(\Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        }
        foreach ($defaultCatalogs as $defaultCatalog) {
            $catalog = $this->entityManager->getRepository(Catalog::class)->findOneBy([
                "shortString" => $defaultCatalog["shortString"]
            ]);
            if (empty($catalog)) {
                $output->writeln("Creating catalog: ". $defaultCatalog["shortString"]);
                $catalog = new Catalog();
                $catalog->setShortString($defaultCatalog["shortString"]);
                $catalog->setFriendlyName($defaultCatalog["friendlyName"]);
                foreach ($defaultItems as $defaultItem) {
                    $output->writeln("Creating item: ". $defaultItem["name"]);
                    $catalogItem = new CatalogItem();
                    $catalogItem->setCatalog($catalog);
                    $catalogItem->setOwner($testUser);
                    $catalogItem->setName($defaultItem["name"]);
                    $catalogItem->setRegion($defaultItem["region"]);
                    for ($i = 0; $i < 5; $i++) {
                        $asset = new Asset();
                        $asset->setNamespace("catalog_item_pic");
                        $asset->setMimeType("image/png");
                        $asset->setBase64($pics[rand(0, 9)]);
                        $catalogItem->getAssets()->add($asset);
                        $this->entityManager->persist($asset);
                    }
                    $this->entityManager->persist($catalogItem);
                }
            }
            $this->entityManager->persist($catalog);
        }
		$this->entityManager->persist($adminGroup);
        $this->entityManager->persist($userGroup);
		$this->entityManager->persist($root);
        $this->entityManager->persist($testUser);
		$this->entityManager->flush();
    }

}