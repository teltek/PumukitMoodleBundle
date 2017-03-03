<?php

namespace Pumukit\MoodleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Person;
use Pumukit\SchemaBundle\Document\Role;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\Track;
use Pumukit\SchemaBundle\Document\Series;

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

    public function setUp()
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

        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Series')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Broadcast')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Tag')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Person')->remove(array());
        $this->dm->flush();
    }

    public function tearDown()
    {
        $this->dm->close();
        $this->dm = null;
        $this->mmobjRepo = null;
        $this->factory = null;
        $this->mmsService = null;
        $this->personService = null;
        $this->picService = null;
        $this->client = null;
        $this->router = null;
        $this->roleCode = null;
        $this->series = null;
        gc_collect_cycles();
        parent::tearDown();
    }

    public function testSearchRepository()
    {
        $this->series = $this->addContent();

        $password = 'ThisIsASecretPasswordChangeMe';
        $email = 'tester@pumukit.es';
        $ticket = md5($password.date('Y-m-d').$email);
        $locale = 'en';
        //When no professor email is provided, returns only public videos.
        $crawler = $this->client->request('GET', '/pumoodle/search_repository');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        //Test that the content of the response is correct.
        $this->assertEquals('OK', $content['status']);
        $this->assertCount(2, $content['out']);
        $this->assertEquals('Series', $content['out'][0]['title']);
        $this->assertEquals('Playlists', $content['out'][1]['title']);
        $this->assertEquals(2, count($content['out'][0]['children']));
        $this->assertEquals(1, count($content['out'][1]['children']));
        $this->assertEquals(true, isset($content['out'][0]['children'][0]['title']));
        $this->assertEquals('My Series', $content['out'][0]['children'][0]['title']);
        $this->assertEquals(true, isset($content['out'][0]['children'][1]['title']));
        $this->assertEquals('Public Series', $content['out'][0]['children'][1]['title']);
        $this->assertEquals(true, isset($content['out'][1]['children'][0]['title']));
        $this->assertEquals('My Playlists', $content['out'][1]['children'][0]['title']);
        $this->assertEquals(false, isset($content['out'][0]['children'][0]['children'][$this->series['id']]));
        $this->assertEquals(true, isset($content['out'][0]['children'][1]['children'][$this->series['id']]));
        $outSeries = $content['out'][0]['children'][1]['children'][$this->series['id']];
        $this->assertEquals('New', $outSeries['title']);
        $this->assertEquals(1, count($outSeries['children']));
        $webtvMmobj = $this->mmobjToArray($this->series['mms']['webtvpub'], $locale);
        $this->assertEquals($webtvMmobj, $outSeries['children'][0]);
        //We now include the ticket, now we should get objects as return.
        $url = '/pumoodle/search_repository?professor_email='.urlencode($email);
        $url = $url.'&ticket='.$ticket;
        $crawler = $this->client->request('GET', $url);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('OK', $content['status']);
        $responseSeries = $content['out'][0]['children'][1]['children'][$this->series['id']];
        $responseMmobjs = $responseSeries['children'];
        ///Check that series are correct.
        $this->assertCount(2, $content['out']);
        $this->assertEquals($this->series['title'], $responseSeries['title']);
        $this->assertEquals($this->series['url'], $responseSeries['url']);
        $this->assertEquals($this->series['pic'], $responseSeries['thumbnail']);
        //Check that mms are correct.
        $this->assertCount(1,  $responseMmobjs);
        $returnedMmobjs = array(
            $this->mmobjToArray($this->series['mms']['webtvpub'], $locale),
        );
        $this->assertEquals($returnedMmobjs, $responseMmobjs);
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

        $this->dm->flush();

        $series = new Series();
        $series->setTitle('New');
        $this->dm->persist($series);
        $this->dm->flush();

        $mmWebTVPub = new MultimediaObject();
        $mmWebTVPub->setTitle('PUBLISHED ON WEBTV');
        $mmWebTVPub->setStatus(MultimediaObject::STATUS_PUBLISHED);

        $mmMoodlePub = new MultimediaObject();
        $mmMoodlePub->setTitle('PUBLISHED ON MOODLE');
        $mmMoodlePub->setStatus(MultimediaObject::STATUS_PUBLISHED);

        $mmMoodlePubOwned = new MultimediaObject();
        $mmMoodlePubOwned->setTitle('PUBLISHED ON MOODLE AND OWNED');
        $mmMoodlePubOwned->setStatus(MultimediaObject::STATUS_PUBLISHED);

        $mmBlocked = new MultimediaObject();
        $mmBlocked->setTitle('BLOCKED');
        $mmBlocked->setStatus(MultimediaObject::STATUS_BLOQ);

        $mmPub = new MultimediaObject();
        $mmPub->setTitle('PUBLISHED WITHOUT CHANNELS');
        $mmPub->setStatus(MultimediaObject::STATUS_PUBLISHED);

        $mmWebTVHidden = new MultimediaObject();
        $mmWebTVHidden->setTitle('HIDDEN ON WEBTV');
        $mmWebTVHidden->setStatus(MultimediaObject::STATUS_HIDE);

        $this->dm->persist($mmWebTVPub);
        $this->dm->persist($mmMoodlePub);
        $this->dm->persist($mmMoodlePubOwned);
        $this->dm->persist($mmBlocked);
        $this->dm->persist($mmPub);
        $this->dm->persist($mmWebTVHidden);
        $this->dm->flush();

        $mmWebTVPub->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();

        $mmMoodlePub->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();

        $mmMoodlePubOwned = $this->personService->createRelationPerson($owner, $role, $mmMoodlePubOwned);
        $mmMoodlePubOwned->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();

        $mmBlocked = $this->personService->createRelationPerson($owner, $role, $mmBlocked);
        $mmBlocked->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();

        $mmBlocked->addTag($tagMoodle);
        $tagMoodle->increaseNumberMultimediaObjects();

        $mmPub = $this->personService->createRelationPerson($owner, $role, $mmPub);

        $mmWebTVHidden = $this->personService->createRelationPerson($owner, $role, $mmWebTVHidden);
        $mmWebTVHidden->addTag($tagWebTV);
        $tagWebTV->increaseNumberMultimediaObjects();

        $this->dm->persist($mmWebTVPub);
        $this->dm->persist($mmMoodlePub);
        $this->dm->persist($mmMoodlePubOwned);
        $this->dm->persist($mmBlocked);
        $this->dm->persist($mmPub);
        $this->dm->persist($mmWebTVHidden);
        $this->dm->persist($tagWebTV);
        $this->dm->persist($tagMoodle);
        $this->dm->flush();

        $mmobjs = array();
        $mmobjs['webtvpub'] = $mmWebTVPub;
        $mmobjs['moodlepub'] = $mmMoodlePub;
        $mmobjs['moodlepubowned'] = $mmMoodlePubOwned;
        $mmobjs['blocked'] = $mmBlocked;
        $mmobjs['pub'] = $mmPub;
        $mmobjs['webtvhidden'] = $mmWebTVHidden;

        //Create mmobjs to be assigned
        $track = new Track();
        $track->addTag('display');

        foreach ($mmobjs as $mmobj) {
            $mmobj->setSeries($series);
            $series->addMultimediaObject($mmobj);
            $mmobj->addTrack($track);
            $this->dm->persist($mmobj);
        }

        $series2 = new Series();
        $series2->setTitle('New');
        $this->dm->persist($series2);
        $this->dm->flush();
        $mmobjExtra = new MultimediaObject();
        $mmobjExtra->setTitle('New');
        $this->dm->persist($mmobjExtra);
        $this->dm->flush();
        $mmobjExtra->setSeries($series2);
        $series2->addMultimediaObject($mmobj);
        $this->dm->persist($series2);
        $this->dm->persist($mmobjExtra);
        $this->dm->flush();

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
        $width = 140;
        $height = 105;
        $url = $this->router->generate('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), true);
        $thumbnail = $picService->getFirstUrlPic($multimediaObject, true, false);
        $mmArray = array(
            'title' => $multimediaObject->getTitle($locale).'.mp4',
            'shorttitle' => $multimediaObject->getTitle($locale),
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
