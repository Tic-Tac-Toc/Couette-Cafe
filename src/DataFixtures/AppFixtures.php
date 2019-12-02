<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Region;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->loadRegions($manager);

        $manager->flush();
    }

    private function loadRegions(ObjectManager $manager)
    {
        foreach ($this->getRegionDatas() as [$country,$name,$desc]) {
            $region = new Region();
            $region->setCountry($country);
            $region->setName($name);
            $region->setPresentation($desc);
            $manager->persist($region);
        }
    }

    private function getRegionDatas()
    {
        yield ['FR','Ile de France','La région française capitale.'];
        yield ['FR','Franche Comté','Jura <3'];
    }
}
