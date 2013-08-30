<?php

namespace CDE\ContentBundle\Tests\Controller;

use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\Response;

class RestControllerTest extends BaseUserTest
{
    protected $comment;

    public function __construct() {
        parent::__construct();
        $this->logIn($this->getUser('ROLE_ADMIN'), new Response());

    }

    public function getGalleryManager() {
        return $this->container->get('cde_content.manager.gallery');
    }

    public function getAWSManager() {
        return $this->container->get('cde_utility.manager.aws');
    }

    public function createGallery()
    {
        //        Copy test.jpeg into the web folder
        $filename = 'gallery/user-test.jpg';
        copy(__DIR__.'/../Mock/test.jpeg', __DIR__.'/../../../../../web/'.$filename);
        $this->getAWSManager()->copyGalleryFile($filename);


        $gallery = $this->getGalleryManager()->create();
        $gallery->setUser($this->getUser());
        $gallery->setFilename($filename);
        $gallery->setTitle('test gallery');
        $gallery->setDescription('test gallery description');
        $gallery->setMarked(false);
        $this->getGalleryManager()->add($gallery);
        $this->assertEquals($gallery->getMarked(), false);
    }

    public function createComment()
    {
        $galleries = $this->getGalleryManager()->findByUser($this->getUser());
        $gallery = $galleries[0];

        $client = static::createClient();
        $client->getCookieJar()->set($this->cookie);
//        $client = static::createClient(array(), new History(), $cookieJar);

        $crawler = $client->request('POST', 'api/createComment/'.$gallery->getId(), array(
            'comment' => 'testing testing 123',
            'marked' => 'false'
        ));

        $response = $client->getResponse();
        $this->comment = json_decode($response->getContent());
        $this->assertEquals($this->comment->comment, 'testing testing 123');
        $this->assertFalse($this->comment->marked);
        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function getComment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/getComment/'.$this->comment->id);

        $response = $client->getResponse();
        $comment = json_decode($response->getContent());
        $this->assertEquals($comment->comment, 'testing testing 123');
        $this->assertFalse($comment->marked);
        $this->assertEquals($response->getStatusCode(), 200);

    }

    public function updateComment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/updateComment');
    }

    public function deleteComment()
    {
        $client = static::createClient();

        $crawler = $client->request('DELETE', 'api/deleteComment');
    }

    public function getComments()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/getComments');
    }

    public function getGalleries()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/getGalleries');
    }

    public function removeGallery() {
        $galleries = $this->getGalleryManager()->findByUser($this->getUser());
        foreach ($galleries as $gallery) {
            $this->getGalleryManager()->remove($gallery);
        }

    }

    public function testComments() {
        $this->createGallery();
        $this->createComment();
        $this->getComment();
        $this->updateComment();
        $this->deleteComment();
        $this->getComments();
        $this->getGalleries();
        $this->removeGallery();
    }

}
