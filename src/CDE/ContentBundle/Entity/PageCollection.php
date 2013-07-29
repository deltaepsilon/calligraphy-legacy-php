<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\ContentBundle\Entity\Page;
use CDE\ContentBundle\Model\PageInterface;

class PageCollection
{
    private $pages;
    
    public function setPages($pages)
    {
        $this->pages = $pages;
    }
    
    public function getPages()
    {
        return $this->pages;
    }
}