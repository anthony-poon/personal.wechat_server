<?php

namespace App\Command;

use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\AbstractModule;
use App\Entity\Core\Location;
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\GlobalValue;
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
            "伦敦",
            "利兹",
            "谢菲尔德",
            "南安普顿",
            "拉夫堡",
            "伯明翰",
            "诺丁汉",
            "利物浦",
            "剑桥",
            "牛津,",
            "曼彻斯特",
            "纽卡斯尔",
            "格拉斯哥",
            "爱丁堡",
            "考文垂",
            "莱斯特",
            "巴斯",
            "杜伦",
            "卡迪夫",
            "圣安德鲁斯",
            "华威",
            "约克",
            "布里斯托"
        ];
        $defaultShortStrings = [
            'LON',
            'LEE',
            'SHE',
            'SOU',
            'LOU',
            'BIR',
            'NOT',
            'LIV',
            'CAM',
            'OXF',
            'MAN',
            'NEW',
            'GLA',
            'EDI',
            'COV',
            'LEI',
            'BAT',
            'DUR',
            'CAR',
            'AND',
            'WAR',
            'YOR',
            'BRI',
        ];
        $locations = [];
        for ($i = 0; $i < count($defaultLocations); $i ++) {
            $locations[] = $this->initLocation($defaultLocations[$i], $defaultShortStrings[$i]);
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

        $defaultGlobals = [
            "visitorCount" => "0",
        ];
        foreach ($defaultGlobals as $key => $value) {
            $this->initGlobalValue($key, $value);
        }
        $this->entityManager->persist($adminGroup);
        $this->entityManager->persist($userGroup);
        $this->entityManager->persist($root);

        $this->entityManager->flush();
    }

    private function initGlobalValue(string $key, string $value) {
        $repo = $this->entityManager->getRepository(GlobalValue::class);
        $gv = $repo->findOneBy([
            "key" => $key
        ]);
        if (!$gv) {
            $gv = new GlobalValue();
            $gv->setKey($key);
        }
        $gv->setValue($value);
        $this->entityManager->persist($gv);
        return $gv;
    }

    private function initLocation(string $name, string $shortString) {
        $repo = $this->entityManager->getRepository(Location::class);
        $location = $repo->findOneBy([
            "name" => $name
        ]);
        if (!$location) {
            $location = new Location();
            $location->setName($name);

        }
        $location->setShortString($shortString);
        $this->entityManager->persist($location);
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