<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 19/12/2018
 * Time: 3:23 PM
 */

namespace App\Command;

use App\Entity\Core\StoreItemAsset;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Constraint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageCompressCommand extends Command {
    private $em;
    private $thumbnailWidth;
    private $thumbnailHeight;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $bag, $name = null) {
        $this->em = $em;
        $this->thumbnailWidth = $bag->get("thumbnail_max_width");
        $this->thumbnailHeight = $bag->get("thumbnail_max_height");
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName("app:image:compress")
            ->setDescription("Compress store item image.")
            ->addOption("width", null, InputOption::VALUE_REQUIRED, "Maximum width of thumbnail", $this->thumbnailWidth)
            ->addOption("height", null, InputOption::VALUE_REQUIRED, "Maximum height of thumbnail", $this->thumbnailHeight)
            ->addOption("all", null, InputOption::VALUE_NONE, "If specified, regenerate all thumbnail");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $repo = $this->em->getRepository(StoreItemAsset::class);

        $width = ((int) $input->getOption("width")) ?? 160;
        $height = ((int) $input->getOption("height")) ?? 160;
        $isAll = $input->getOption("all");

        if ($isAll) {
            $assets = $repo->findAll();
        } else {
            $assets = $repo->findBy([
                "thumbnailBase64" => null
            ]);
        }

        foreach ($assets as $asset) {
            /* @var StoreItemAsset $asset */
            $output->writeln("Image #".$asset->getId());
            $img = Image::make($asset->getBase64());
            $output->writeln("Original Dimension: ". $img->getWidth()." x ".$img->getHeight());
            if ($img->getWidth() > $img->getHeight()) {
                $img->resize($width,null, function(Constraint $constraint){
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                $img->resize(null, $height, function(Constraint $constraint){
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            $output->writeln("New Dimension: ". $img->getWidth()." x ".$img->getHeight());
            $output->writeln("");
            $url = $img->encode("data-url");
            preg_match("/^data:.+;base64,(.+)/", $url, $match);
            $asset->setThumbnailBase64($match[1]);
            $this->em->persist($asset);
        }
        $this->em->flush();
    }


}