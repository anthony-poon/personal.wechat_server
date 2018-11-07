<?php

namespace App\Command;

use App\Entity\Base\Asset;
use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\Housing;
use App\Entity\Core\Module;
use App\Entity\Core\SecondHandItem;
use App\Entity\Core\Store;
use App\Entity\Core\StoreItem;
use App\Entity\Core\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use GuzzleHttp\Client;

class InitCommand extends Command {
    private $entityManager;
    private $passwordEncoder;
    private $baseUrl;
    private const DEMO_USER_COUNT = 5;
    private const DEMO_STORE_ITEM_COUNT = 5;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ParameterBagInterface $params, $name = null) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->baseUrl = $params->get("kernel.project_dir");
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:init")
            ->addOption("with-demo-data", null, InputOption::VALUE_NONE)
            ->setDescription("Create root user and role");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln("Creating root users");
		$root = $this->initUser("root", md5(random_bytes(10)));
		$output->writeln("Username: root");
		$output->writeln("Password: ".$root->getPlainPassword());

        $output->writeln("Creating Admin Group");
        $adminGroup = $this->initGroup("Admin Group", "ROLE_ADMIN");
        if (!$adminGroup->getChildren()->contains($root)) {
            $adminGroup->getChildren()->add($root);
        }

        $output->writeln("Creating User Group");
        $userGroup = $this->initGroup("User Group", "ROLE_USER");

		if ($input->getOption("with-demo-data")) {
		    // Base 1
		    for($i = 1; $i <= self::DEMO_USER_COUNT; $i ++) {
                $output->writeln("Creating Demo User user_$i");
		        $user = $this->initUser("user_$i", "password", true);
		        if (!$userGroup->getChildren()->contains($user)) {
                    $userGroup->getChildren()->add($user);
                }
                $this->entityManager->persist($user);
            }
        }
        $this->entityManager->persist($adminGroup);
        $this->entityManager->persist($userGroup);
        $this->entityManager->persist($root);

        $this->entityManager->flush();
    }

    private function initGroup(string $groupName, string $siteToken): SecurityGroup {
        $repo = $this->entityManager->getRepository(SecurityGroup::class);
        $group = $repo->findOneBy(["siteToken" => $siteToken]);
        if (!$group) {
            $group = new SecurityGroup();
            $group->setSiteToken($siteToken);
        }
        $group->setName($groupName);
        return $group;
    }

    private function initUser(string $username, string $password = "password", bool $withDemoData = false): User {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy([
            "username" => $username
        ]);
        if (!$user) {
            $user = new User();
            $user->setUsername($username);
            $user->setFullName($username);
        }
        $store = $user->getStore();
        if (!$store) {
            $store = new Store();
            $store->setName($user->getFullName()."'s Store'");
            $store->setOwner($user);
        }
        if ($withDemoData) {
            $pics = [];
            foreach (scandir($this->baseUrl."/assets/images/demo") as $img) {
                if (preg_match("/^\w.*\.(png|jpg|jpeg)$/", $img)) {
                    $path = $this->baseUrl."/assets/images/demo/".$img;
                    $pics[] = base64_encode(file_get_contents($path));
                }
            }
            foreach ($store->getStoreItems() as $item) {
                /* @var \App\Entity\Core\AbstractStoreItem $item */
                if ($item->getCity() == "_demo") {
                    $this->entityManager->remove($item);
                }
            }
            for ($i = 1; $i <= self::DEMO_STORE_ITEM_COUNT; $i++) {
                $item = new SecondHandItem();
                $item->setName("Second Hand Item $i");
                $item->setCity("_demo");
                $item->setStore($store);
                $asset = new Asset();
                $asset->setMimeType("image/jpeg");
                $asset->setNamespace("store_item_pic");
                $asset->setBase64($pics[array_rand($pics)]);
                $this->entityManager->persist($asset);
                $item->getAssets()->add($asset);
                $this->entityManager->persist($item);
            }
            for ($i = 1; $i <= self::DEMO_STORE_ITEM_COUNT; $i++) {
                $item = new Housing();
                $item->setName("Housing $i");
                $item->setCity("_demo");
                $item->setStore($store);
                foreach (array_rand($pics, 5) as $index) {
                    $asset = new Asset();
                    $asset->setMimeType("image/jpeg");
                    $asset->setNamespace("store_item_pic");
                    $asset->setBase64($pics[$index]);
                    $item->getAssets()->add($asset);
                    $this->entityManager->persist($asset);
                }
                $this->entityManager->persist($item);
            }
            for ($i = 1; $i <= self::DEMO_STORE_ITEM_COUNT; $i++) {
                $item = new Ticket();
                $item->setName("Ticket $i");
                $item->setCity("_demo");
                $item->setStore($store);
                $asset = new Asset();
                $asset->setMimeType("image/jpeg");
                $asset->setNamespace("store_item_pic");
                $asset->setBase64($pics[array_rand($pics)]);
                $this->entityManager->persist($asset);
                $item->getAssets()->add($asset);
                $this->entityManager->persist($item);
            };
        }
        $this->entityManager->persist($store);
        $user->setPlainPassword($password);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        return $user;
    }
}