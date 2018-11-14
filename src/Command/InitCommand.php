<?php

namespace App\Command;

use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\AbstractModule;
use App\Entity\Core\Location;
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\Ticketing\TicketingModule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InitCommand extends Command {
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ParameterBagInterface $params, $name = null) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:init")
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

        $output->writeln("Creating Location Data");
        $defaultLocations = [
            "_location_1",
            "_location_2",
            "_location_3",
            "_location_4",
            "_location_5",
        ];
        $locations = [];
        foreach ($defaultLocations as $defaultLocation) {
            $locations[] = $this->initLocation($defaultLocation);
        }

        $defaultModules = [
            SecondHandModule::class,
            HousingModule::class,
            TicketingModule::class
        ];
        $modules = [];
        foreach ($defaultModules as $defaultModule) {
            $modules[] = $this->initModule($locations, $defaultModule);
        }
        $this->entityManager->persist($adminGroup);
        $this->entityManager->persist($userGroup);
        $this->entityManager->persist($root);

        $this->entityManager->flush();
    }



    private function initLocation(string $name) {
        $repo = $this->entityManager->getRepository(Location::class);
        $location = $repo->findOneBy([
            "name" => $name
        ]);
        if (!$location) {
            $location = new Location();
            $location->setName($name);
            $this->entityManager->persist($location);
        }
        return $location;
    }

    private function initModule(array $locations, string $moduleName) {
        $modules = [];
        foreach ($locations as $location) {
            /* @var Location $location */
            $module = $location->getModules()->filter(function (AbstractModule $module) use ($moduleName) {
                return get_class($module) === $moduleName;
            })->first();
            if (!$module) {
                /* @var AbstractModule $module */
                $module = new $moduleName();
                $module->setLocation($location);
            }
            $this->entityManager->persist($module);
            $modules[] = $module;
        }
        return $modules;
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

    private function initUser(string $username, string $password = "password"): User {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy([
            "username" => $username
        ]);
        if (!$user) {
            $user = new User();
            $user->setUsername($username);
            $user->setFullName($username);
        }
        $user->setPlainPassword($password);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        return $user;
    }
}