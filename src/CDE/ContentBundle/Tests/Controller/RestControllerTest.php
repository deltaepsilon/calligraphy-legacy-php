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

    private function getJSONResponse($client) {
        try {
            $content = $client->getResponse()->getContent();
            $json = json_decode($content);
        } catch (\ErrorException $e) {
            return array();
        }
        return $json;
    }


    /**
     * Copy test.jpeg into web folder to simulate an upload
     * Prepare new gallery with dummy data and save to DB.
     */
    public function createGallery()
    {

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

    /**
     * Create comment
     * Set cookie on client so that createCommentAction can access a user.
     */
    public function createComment()
    {
        $galleries = $this->getGalleryManager()->findByUser($this->getUser());
        $gallery = $galleries[0];

        $client = $this->getClient();
        
        $crawler = $client->request('POST', 'api/createComment/'.$gallery->getId(), array(
            'comment' => 'testing testing 123',
            'marked' => 'false'
        ));

        $this->comment = $comment = $this->getJSONResponse($client);
        $this->assertEquals($comment->comment, 'testing testing 123');
        $this->assertFalse($comment->marked);
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }

    public function getComment()
    {
        $client = $this->getClient();
        $client->request('GET', 'api/getComment/'.$this->comment->id);

        $comment = $this->getJSONResponse($client);
        $this->assertEquals($comment->comment, 'testing testing 123');
        $this->assertFalse($comment->marked);
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

    }

    public function updateComment()
    {
        $client = $this->getClient();
        $client->request('POST', 'api/updateComment/'.$this->comment->id, array(
            'comment' => 'testing 456',
            'marked' => 'true'
        ));

        $comment = $this->getJSONResponse($client);
        $this->assertEquals($comment->comment, 'testing 456');
        $this->assertTrue($comment->marked);
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }

    public function getComments()
    {
        $client = $this->getClient();
        $client->request('GET', 'api/getComments');

        $comments = $this->getJSONResponse($client);
        $comment = $comments[0]; //Test first comment... it could be anything, so don't try to get specific
        $this->assertEquals(count($comments), 10);
        $this->assertTrue(isset($comment->comment));
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }

    public function getGalleries()
    {
        $client = $this->getClient();
        $client->request('GET', 'api/getGalleries');

        $galleries = $this->getJSONResponse($client);
        $gallery = $galleries[0];
        $this->assertEquals(count($galleries), 10);
        $this->assertTrue(isset($gallery->filename));
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
    }

    public function deleteComment()
    {
        $client = $this->getClient();
        $client->request('DELETE', 'api/deleteComment/'.$this->comment->id);

        $jsonResponse = $this->getJSONResponse($client);
        $this->assertEquals($jsonResponse->id, $this->comment->id);
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
