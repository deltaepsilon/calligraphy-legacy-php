<?php

namespace CDE\OAuthBundle\Tests\Controller;

use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\Response;

class OAuthControllerTest extends BaseUserTest
{

    public function __construct() {
        parent::__construct();
        $this->logIn($this->getUser('ROLE_ADMIN'), new Response());

    }

    public function create() {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/admin/oauth/create');

        $metaTag = $crawler->filter('a');
        $href = $metaTag->attr('href');

        $this->assertEquals(preg_match("/\/oauth\/v2\/auth\?client_id=/", $href), 1);
        $this->assertEquals($client->getResponse()->getStatusCode(), 302);

        $crawler = $client->followRedirect();

//        $content = $client->getResponse()->getContent();


        $buttonCrawlerNode = $crawler->selectButton("Allow");
        $form = $buttonCrawlerNode->form();

        //TODO form submission is not causing a successful submission of the form.
        //TODO Either bail at this point. or fight for the code.
        $crawler = $client->submit($form);

        $content = $client->getResponse()->getContent();
        var_dump($content);

        $code = $crawler->filter('a')->attr('href');

        return $code;

    }

    public function index()
    {

    }

    public function delete() {

    }

    public function testAll() {
        $this->create();
        $this->index();
        $this->delete();

    }
}
