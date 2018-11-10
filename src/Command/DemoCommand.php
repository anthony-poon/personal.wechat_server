<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 11:21 AM
 */

namespace App\Command;

use App\Entity\Base\Asset;
use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DemoCommand extends Command {
    private $em;
    private $passwordEncoder;
    private $placeholderPic;
    private const DEMO_USER_COUNT = 5;
    private const DEMO_STORE_ITEM_COUNT = 5;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, ParameterBagInterface $params, $name = null) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $baseUrl = $params->get("kernel.project_dir");
        $this->placeholderPic = [];
        foreach (scandir($baseUrl."/assets/images/demo") as $img) {
            if (preg_match("/^\w.*\.(png|jpg|jpeg)$/", $img)) {
                $path = $baseUrl."/assets/images/demo/".$img;
                $this->placeholderPic[] = base64_encode(file_get_contents($path));
            }
        }
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:demo")
            ->setDescription("Create demo data for testing");
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        // Base 1
        $users = [];
        for($i = 1; $i <= self::DEMO_USER_COUNT; $i ++) {
            $output->writeln("Creating Demo User user_$i");
            $users[] = $this->initUser("user_$i");
        }
        $storeFronts = [];
        foreach ($users as $user) {
            $storeFronts = array_merge($storeFronts, $this->initStoreFront($user));
        }
        $storeItems = [];
        foreach ($storeFronts as $storeFront) {
            $storeItems = array_merge($storeItems, $this->initStoreItem($storeFront));
        }
        $this->em->flush();
    }

    private function initUser(string $username, string $password = "password"): User {
        $user = $this->em->getRepository(User::class)->findOneBy([
            "username" => $username
        ]);
        $userGroup = $this->em->getRepository(SecurityGroup::class)->findOneBy([
            "siteToken" => "ROLE_USER"
        ]);
        if (!$user) {
            $user = new User();
            $user->setUsername($username);
            $user->setFullName($username);
            $userGroup->getChildren()->add($user);
        }
        $user->setPlainPassword($password);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->em->persist($user);
        $this->em->persist($userGroup);
        return $user;
    }

    private function initStoreFront(User $user) {
        $modules = $this->em->getRepository(AbstractModule::class)->findAll();
        $storeFronts = [];
        foreach ($modules as $module) {
            switch (get_class($module)) {
                case SecondHandModule::class:
                    /* @var \App\Entity\Core\SecondHand\SecondHandModule $module */
                    $storeFront = $module->getStoreFronts()->filter(function(AbstractStoreFront $storeFront) use ($user){
                        return $storeFront->getOwner() === $user;
                    })->first();
                    if (!$storeFront) {
                        $storeFront = new SecondHandStoreFront();
                        $storeFront->setOwner($user);
                        $storeFront->setName($user->getFullName()."'s Store");
                        $storeFront->setModule($module);
                    }
                    $storeFronts[] = $storeFront;
                    $this->em->persist($storeFront);
                    break;
                case HousingModule::class:
                    /* @var \App\Entity\Core\Housing\HousingModule $module */
                    $storeFront = $module->getStoreFronts()->filter(function(AbstractStoreFront $storeFront) use ($user){
                        return $storeFront->getOwner() === $user;
                    })->first();
                    if (!$storeFront) {
                        $storeFront = new HousingStoreFront();
                        $storeFront->setOwner($user);
                        $storeFront->setName($user->getFullName()."'s Store");
                        $storeFront->setModule($module);
                    }
                    $storeFronts[] = $storeFront;
                    $this->em->persist($storeFront);
                    break;
            }

        }
        return $storeFronts;
    }

    private function initStoreItem(AbstractStoreFront $storeFront) {
        $items = [];
        switch (get_class($storeFront)) {
            case SecondHandStoreFront::class:
                /* @var SecondHandStoreFront $storeFront */
                for ($i = 1; $i <= self::DEMO_STORE_ITEM_COUNT; $i ++) {
                    $name = "_second_hand_item_$i";
                    $item = $storeFront->getStoreItems()->filter(function(SecondHandItem $item) use ($name) {
                        return $item->getName() === $name;
                    })->first();
                    if (!$item) {
                        $item = new SecondHandItem();
                        $item->setName($name);
                        $item->setStoreFront($storeFront);
                        $item->setDescription(get_class($item));
                        $asset = new Asset();
                        $asset->setNamespace(SecondHandItem::class);
                        $asset->setMimeType("image/jpeg");
                        $asset->setBase64($this->placeholderPic[array_rand($this->placeholderPic)]);
                        $item->getAssets()->add($asset);
                        $this->em->persist($item);
                    }
                    $items[] = $item;
                }
                break;
            case HousingStoreFront::class:
                /* @var HousingStoreFront $storeFront */
                for ($i = 1; $i <= self::DEMO_STORE_ITEM_COUNT; $i ++) {
                    $name = "_housing_item_$i";
                    $item = $storeFront->getStoreItems()->filter(function(HousingItem $item) use ($name) {
                        return $item->getName() === $name;
                    })->first();
                    if (!$item) {
                        $item = new HousingItem();
                        $item->setName($name);
                        $item->setStoreFront($storeFront);
                        $item->setDescription(get_class($item));
                        $asset = new Asset();
                        $asset->setNamespace(HousingItem::class);
                        $asset->setMimeType("image/jpeg");
                        $asset->setBase64($this->placeholderPic[array_rand($this->placeholderPic)]);
                        $item->getAssets()->add($asset);
                        $this->em->persist($item);
                    }
                    $items[] = $item;
                }
                break;
        }
        return $items;
    }
}