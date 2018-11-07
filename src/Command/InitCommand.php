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
		$output->writeln("Creating root users");
		$root = $this->initUser("root", random_bytes(10));
		$output->writeln("Username: root");
		$output->writeln("Password: ".$root->getPlainPassword());

        $output->writeln("Creating Admin Group");
        $adminGroup = $this->initGroup("Admin Group", "ROLE_ADMIN");
        if (!$adminGroup->getChildren()->contains($root)) {
            $adminGroup->getChildren()->add($root);
        }

        $output->writeln("Creating User Group");
        $userGroup = $this->initGroup("User Group", "ROLE_USER");

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

    private function initUser(string $username, string $password = "password"): User {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy([
            "username" => $username
        ]);
        if (!$user) {
            $user = new User();
            $user->setPlainPassword($password);
            $user->setUsername($username);
            $user->setFullName($username);
        }
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        return $user;
    }
}