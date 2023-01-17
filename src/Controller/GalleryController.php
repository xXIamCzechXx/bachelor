<?php

namespace App\Controller;

use App\Entity\GalleryCategories;
use App\Entity\GalleryImages;
use App\Entity\Pages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class GalleryController extends BaseController
{
    /**
     * @Route("/galerie", name="gallery")
     */
    public function index(Request $request): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'gallery'])) {
            throw $this->createNotFoundException();
        }

        $gallery = $this->em->getRepository(GalleryImages::class)->findVisible();
        $categories = $this->em->getRepository(GalleryCategories::class)->findVisible();

        return $this->render('default/gallery/gallery.html.twig', [
            'page' => $page,
            'gallery' => $gallery,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/galerie/{slug}", name="gallery_detail")
     */
    public function galleryDetail(GalleryCategories $galleryCategory, Request $request)
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'gallery'])) {
            throw $this->createNotFoundException();
        }
        $images = $galleryCategory->getGalleryImages();
        if (!empty($galleryCategory->getTitle())) {$page->setTitle($galleryCategory->getTitle());}
        if (!empty($galleryCategory->getMetaDescription())) {$page->setMetaDescription($galleryCategory->getMetaDescription());}
        if (!empty($galleryCategory->getKeywords())) {$page->setKeywords($galleryCategory->getKeywords());}
        if (!empty($galleryCategory->getHeading())) {$page->setHeading($galleryCategory->getHeading());}

        return $this->render('default/gallery/gallery_detail.html.twig', [
            'page' => $page,
            'gallery' => $galleryCategory,
            'images' => $images,
        ]);
    }
}