<?php

namespace App\DataFixtures;

use App\Entity\FormAnswers;
use App\Entity\News;
use App\Entity\Product;
use App\Entity\User;
use App\Factory\AnswerTypesFactory;
use App\Factory\CategoryFactory;
use App\Factory\CountriesFactory;
use App\Factory\FormAnswersFactory;
use App\Factory\GalleryCategoriesFactory;
use App\Factory\GalleryImagesFactory;
use App\Factory\LogFactory;
use App\Factory\NewsCategoriesFactory;
use App\Factory\NewsFactory;
use App\Factory\PagesFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        CountriesFactory::new()->createMany(5);
        PagesFactory::new()->createMany(8);
        GalleryImagesFactory::new()->createMany(4);
        GalleryCategoriesFactory::new()->createMany(5);
        NewsCategoriesFactory::new()->createMany(5);

        if(UserFactory::new()->create()) {
            if(AnswerTypesFactory::new()->createMany(3)) {
                $this->loadAfter($manager);
            }
        }

    }

    public function loadAfter(ObjectManager $manager)
    {
        UserFactory::new()->createMany(9);
        LogFactory::new()->createMany(6);
        NewsFactory::new()->createMany(10);
        FormAnswersFactory::new()->createMany(4);
    }

}
