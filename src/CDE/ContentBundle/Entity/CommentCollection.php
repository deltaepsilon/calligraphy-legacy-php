<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class CommentCollection
{
    private $comments;
    
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
    
    public function getComments()
    {
        return $this->comments;
    }
}