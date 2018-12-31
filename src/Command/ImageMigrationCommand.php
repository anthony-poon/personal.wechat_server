<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 31/12/2018
 * Time: 2:14 PM
 */

namespace App\Command;

use App\Entity\Base\Asset;
use App\Entity\Core\StoreItemAsset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageMigrationCommand extends Command{
    private $em;
    private $path;
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $bag, $name = null) {
        $this->em = $em;
        $this->path = realpath($bag->get("upload_img_path"));
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:image:migrate")
            ->setDescription("Migrate base64 storage to file system storage");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $repo = $this->em->getRepository(Asset::class);
        $assets = $repo->findAll();
        foreach ($assets as $asset) {
            /* @var \App\Entity\Base\Asset $asset */
            $base64 = $asset->getBase64();
            $name = uniqid();
            $output->writeln($asset->getId(). " => ".$this->path."/".$name);
            file_put_contents($this->path."/".$name, base64_decode($base64));
            $asset->setImgPath($name);
            if ($asset instanceof StoreItemAsset) {
                /* @var \App\Entity\Core\StoreItemAsset $asset */
                $tBase64 = $asset->getThumbnailBase64();
                $tName = "thumbnail_".$name;
                file_put_contents($this->path."/".$tName, base64_decode($tBase64));
                $asset->setThumbnailPath($tName);
            }
            $this->em->persist($asset);
        }
        $this->em->flush();
    }


}