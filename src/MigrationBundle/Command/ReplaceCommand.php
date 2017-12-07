<?php

namespace MigrationBundle\Command;

use RestBundle\Entity\Blog;
use RestBundle\Entity\Category;
use RestBundle\Entity\Page;
use RestBundle\Entity\TopCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('migration:replace')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

//        /** @var Blog[] $pages */
//        $pages = $em->getRepository('RestBundle:Blog')->findBy(['post_type' => 'page']);
//
//        foreach ($pages as $page) {
//            $pageObject = new Page();
//            $pageObject->setTitle($page->getTitle());
//            $pageObject->setSeoTitle($page->getSeoTitle());
//            $pageObject->setDescription($page->getDescription());
//            $pageObject->setImage($page->getImage());
//            $pageObject->setImageName($page->getImageName());
//            $pageObject->setContent($page->getContent());
//            $pageObject->setSlug($page->getUrl());
//            $pageObject->setPostDate($page->getPostDate());
//
//            $em->persist($pageObject);
//            $em->remove($page);
//        }
//
//        $em->flush();

        /** @var Category[] $categories */
        $categories = $em->getRepository('RestBundle:Category')->findAll();

        foreach ($categories as $category) {
            $topCategory = new TopCategory();
            $topCategory->setTitle($category->getTitle());
            $topCategory->setCategory($category->getCategory());

            $em->persist($topCategory);
        }

        $em->flush();

//        /** @var Blog[] $articles */
//        $articles = $em->getRepository('RestBundle:Blog')->findAll();
//
//        foreach ($articles as $article) {
//            /** @var Category[] $categories */
//            $categories = $article->getCategory();
//            $tops = [];
//            foreach ($categories as $category) {
//                $tops[] = $em->getRepository('RestBundle:TopCategory')->findOneBy(['category' => $category->getCategory()]);
//            }
//
//            $article->setTopCategory($tops);
//            $em->persist($article);
//        }
//
//        $em->flush();
    }
}
