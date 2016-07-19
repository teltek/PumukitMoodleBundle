<?php

namespace Pumukit\MoodleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Person;
use Pumukit\SchemaBundle\Document\Role;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\Track;

class RepositoryPMKSearchControllerTest extends WebTestCase
{
    private $dm;
    private $mmobjRepo;
    private $mmobjService;
    private $factory;
    private $client;
    private $router;
    private $roleCode;
    private $series;

    public function __construct()
    {
        $options = array('environment' => 'test');
        static::bootKernel($options);

        $this->dm = static::$kernel->getContainer()
                                   ->get('doctrine_mongodb')
                                   ->getManager();
        $this->mmobjRepo = $this->dm
                                ->getRepository('PumukitSchemaBundle:MultimediaObject');
        $this->factory = static::$kernel->getContainer()
                                        ->get('pumukitschema.factory');
        $this->mmsService = static::$kernel->getContainer()
                                           ->get('pumukitschema.multimedia_object');
        $this->personService = static::$kernel->getContainer()
                                              ->get('pumukitschema.person');
        $this->picService = static::$kernel->getContainer()
                                              ->get('pumukitschema.pic');
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
        $this->roleCode = $this->client->getContainer()->getParameter('pumukit_moodle.role');
    }

    public function testSearchRepository()
    {
        $password = 'ThisIsASecretPasswordChangeMe';
        $email = 'tester@pumukit.es';
        $ticket = md5($password.date('Y-m-d').$email);
        $locale = 'en';
        //When no professor email is provided, returns only public videos.
        $crawler = $this->client->request('GET', '/pumoodle/search_repository');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //Test that the content of the response is correct.
        $this->assertEquals("OK", $content['status']);
        $this->assertCount(2, $content['out']);
        $this->assertEquals('series', $content['out'][0]['title']);
        $this->assertEquals('playlists', $content['out'][1]['title']);
        $this->assertEquals(1, count($content['out'][0]['children']));
        $this->assertEquals(true, isset($content['out'][0]['children'][$this->series['id']]));
        $outSeries = $content['out'][0]['children'][$this->series['id']];
        $this->assertEquals("New", $outSeries['title']);
        $this->assertEquals(1, count($outSeries['children']));
        $webtvMmobj = $this->mmobjToArray($this->series['mms']['webtvpub'], $locale);
        $this->assertEquals($webtvMmobj, $outSeries['children'][0]);
        //We now include the ticket, now we should get objects as return.
        $url = '/pumoodle/search_repository?professor_email='.urlencode($email);
        $url = $url .'&ticket='.$ticket;
        $crawler = $this->client->request('GET', $url);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("OK", $content['status']);
        $responseSeries = $content['out'][0]['children'][$this->series['id']];
        $responseMmobjs = $responseSeries['children'];
        ///Check that series are correct.
        $this->assertCount(2, $content['out']);
        $this->assertEquals($this->series['title'], $responseSeries['title']);
        $this->assertEquals($this->series['url'], $responseSeries['url']);
        $this->assertEquals($this->series['pic'], $responseSeries['thumbnail']);
        //Check that mms are correct.
        $this->assertCount(2,  $responseMmobjs);
        $returnedMmobjs = array(
            $this->mmobjToArray($this->series['mms']['webtvpub'], $locale),
            $this->mmobjToArray($this->series['mms']['moodlepubowned'], $locale),
        );
        $this->assertEquals($returnedMmobjs, $responseMmobjs);
    }

    public function setUp()
    {
        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Series')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Broadcast')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Tag')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Person')->remove(array());
        $this->dm->flush();
        $this->series = $this->addContent();
        $this->dm->flush();
    }

    private function addContent()
    {
        //Get rolecode that marks a person as 'owner'
        $roleCode = $this->roleCode;
        //Create tags to assign to videos:
        $tagWebTV = new Tag();
        $tagWebTV->setCod('PUCHWEBTV');
        $this->dm->persist($tagWebTV);
        $tagMoodle = new Tag();
        $tagMoodle->setCod('PUCHMOODLE');
        $this->dm->persist($tagMoodle);
        //Create person to assign to videos:
        $owner = new Person();
        $owner = $this->personService->savePerson($owner);
        $owner->setEmail('tester@pumukit.es');
        $owner->setName('Tester');
        $owner = $this->personService->updatePerson($owner);
        $this->dm->persist($owner);
        $role = new Role();
        $role->setDisplay(true);
        $role->setCod($roleCode);
        $role->setXml($roleCode);
        $role->setName($roleCode);
        $this->dm->persist($role);
        //Create mmobjs to be assigned
        $track = new Track();
        $track->addTag('display');
        $series = $this->factory->createSeries();
        $mmobjs = array();
        $mmobjs['webtvpub'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['webtvpub']->setTitle('PUBLISHED ON WEBTV');
        $mmobjs['webtvpub']->setStatus(MultimediaObject::STATUS_PUBLISHED);
        $mmobjs['webtvpub']->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();
        $mmobjs['moodlepub'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['moodlepub']->setTitle('PUBLISHED ON MOODLE');
        $mmobjs['moodlepub']->setStatus(MultimediaObject::STATUS_PUBLISHED);
        $mmobjs['moodlepub']->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();
        $mmobjs['moodlepubowned'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['moodlepubowned'] = $this->personService->createRelationPerson($owner, $role, $mmobjs['moodlepubowned']);
        $mmobjs['moodlepubowned']->setTitle('PUBLISHED ON MOODLE AND OWNED');
        $mmobjs['moodlepubowned']->setStatus(MultimediaObject::STATUS_PUBLISHED);
        $mmobjs['moodlepubowned']->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();
        $mmobjs['blocked'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['blocked'] = $this->personService->createRelationPerson($owner, $role, $mmobjs['blocked']);
        $mmobjs['blocked']->setTitle('BLOCKED');
        $mmobjs['blocked']->setStatus(MultimediaObject::STATUS_BLOQ);
        $mmobjs['blocked']->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();
        $mmobjs['blocked']->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();
        $mmobjs['pub'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['pub'] = $this->personService->createRelationPerson($owner, $role, $mmobjs['pub']);
        $mmobjs['pub']->setTitle('PUBLISHED WITHOUT CHANNELS');
        $mmobjs['pub']->setStatus(MultimediaObject::STATUS_PUBLISHED);
        $mmobjs['webtvhidden'] = $this->factory->createMultimediaObject($series, true);
        $mmobjs['webtvhidden'] = $this->personService->createRelationPerson($owner, $role, $mmobjs['webtvhidden']);
        $mmobjs['webtvhidden']->setTitle('HIDDEN ON WEBTV');
        $mmobjs['webtvhidden']->setStatus(MultimediaObject::STATUS_HIDE);
        $mmobjs['webtvhidden']->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();
        foreach ($mmobjs as $mmobj) {
            $mmobj->addTrack($track);
            $this->dm->persist($mmobj);
        }
        $series2 = $this->factory->createSeries();
        $mmobjExtra  = $this->factory->createMultimediaObject($series2, true);
        $this->dm->persist($mmobjExtra);
        return array(
            'title' => $series->getTitle(),
            'url' => $this->router->generate('pumukit_webtv_series_index', array('id' => $series->getId()), true),
            'pic' => $this->picService->getFirstUrlPic($series, true, false),
            'mms' => $mmobjs,
            'id' => $series->getId(),
        );
    }

    private function mmobjToArray(MultimediaObject $multimediaObject, $locale = null)
    {
        $picService = $this->picService;
        $width  = 140;
        $height = 105;
        $url = $this->router->generate('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), true);
        $thumbnail = $picService->getFirstUrlPic($multimediaObject, true, false);
        $mmArray = array(
            'title' => $multimediaObject->getTitle($locale) . ".mp4",
            'shorttitle'=> $multimediaObject->getTitle($locale),
            'url' => $url,
            'thumbnail' => $thumbnail,
            'thumbnail_width' => $width,
            'thumbnail_height' => $height,
            'icon' => $thumbnail,
            'source' => $this->router->generate(
                'pumukit_moodle_moodle_embed',
                array(
                    'id' => $multimediaObject->getId(),
                    'lang' => $locale,
                    'opencast' => ($multimediaObject->getProperty('opencast') ? '1' : '0'),
            'autostart' => false,
                ),
                true
            ),
        );
        return $mmArray;
    }
}
