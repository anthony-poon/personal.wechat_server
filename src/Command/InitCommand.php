<?php

namespace App\Command;

use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\Catalog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $userGroup = $grpRepo->findOneBy(["siteToken" => "ROLE_USER"]);
        if (empty($userGroup)) {
            $output->writeln("Creating User Group");
            $userGroup = new SecurityGroup();
        }
        $userGroup->setName("User Group");
        $userGroup->setSiteToken("ROLE_USER");

        $defaults = [
            [
                "shortString" => "default_1",
                "friendlyName" => "分類一"
            ], [
                "shortString" => "default_2",
                "friendlyName" => "分類二"
            ], [
                "shortString" => "default_3",
                "friendlyName" => "Type 3"
            ],
        ];
        foreach ($defaults as $default) {
            $catalog = $this->entityManager->getRepository(Catalog::class)->findOneBy([
                "shortString" => $default["shortString"]
            ]);
            if (empty($catalog)) {
                $catalog = new Catalog();
                $catalog->setShortString($default["shortString"]);
                $catalog->setFriendlyName($default["friendlyName"]);
            }
            $this->entityManager->persist($catalog);
        }
		$this->entityManager->persist($adminGroup);
        $this->entityManager->persist($userGroup);
		$this->entityManager->persist($root);
		$this->entityManager->flush();
    }

}