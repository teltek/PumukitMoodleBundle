<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Series;

/**
 * @Route("/pumoodle")
 */
class RepositoryPMKSearchController extends Controller
{
    /**
     * @Route("/search_repository", defaults={"filter":false})
     */
    public function searchRepositoryAction(Request $request)
    {
        //$this->enableFilter();
        $email = $request->get('professor_email');
        $username = $request->get('professor_username');
        $ticket = $request->get('ticket');
        $locale = $this->getLocale($request->get('lang'));
        $searchText = $request->get('search');

        //TODO check ticket

        $multimediaObjects = $this->getRepositoryMmobjs($searchText);
        return $this->getPlainResponse($multimediaObjects, $locale);
    }

    private function getPlainResponse($multimediaObjects, $locale)
    {
        $out = array();
        $out['out'] = array();

        foreach ($multimediaObjects as $multimediaObject) {
            $mmobjResult = $this->mmobjToArray($multimediaObject, $locale);
            $out['out'][] = $mmobjResult;
        }


        $out['status'] = 'OK';
        $out['status_txt'] = count($out['out']);

        return new JsonResponse($out, 200);
    }


    /**
     * -- AUXILIARY FUNCTIONS --
     * Note: Move these to a class.
     */
    private function checkFieldTicket($email, $ticket, $id = '')
    {
        $check = '';
        $password = $this->container->getParameter('pumukit_moodle.password');
        $check = md5($password.date('Y-m-d').$id.$email);

        return $check === $ticket;
    }

    private function findProfessorEmail($email, $ticket, $roleCode)
    {
        $repo = $this->get('doctrine_mongodb.odm.document_manager')
                     ->getRepository('PumukitSchemaBundle:Person');

        $professor = $repo->findByRoleCodAndEmail($roleCode, $email);

        return $professor;
    }

    private function findProfessorUsername($username, $ticket, $roleCode)
    {
        //Because we need a 'person', but the 'username' is part of the user
        $userRepo = $this->get('doctrine_mongodb.odm.document_manager')
                     ->getRepository('PumukitSchemaBundle:User');
        $user = $userRepo->findOneByUsername($username);

        if (!$user) {
            return null;
        }

        $professor = $user->getPerson();
        //Instead of directly using the person, we call the original function with its email to keep the functionality exactly the same.
        $email = $professor ? $professor->getEmail() : '';

        return $this->findProfessorEmail($email, $ticket, $roleCode);
    }

    private function getLocale($queryLocale)
    {
        $locale = strtolower($queryLocale);
        $defaultLocale = $this->container->getParameter('locale');
        $pumukitLocales = $this->container->getParameter('pumukit2.locales');
        if ((!$locale) || (!in_array($locale, $pumukitLocales))) {
            $locale = $defaultLocale;
        }

        return $locale;
    }

    /**
     * Returns a dictionary with multimedia object elements.
     */
    protected function mmobjToArray(MultimediaObject $multimediaObject, $locale = null)
    {
        $picService = $this->get('pumukitschema.pic');
        $width = 140;
        $height = 105;
        $url = $this->generateUrl('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), true);
        $thumbnail = $picService->getFirstUrlPic($multimediaObject, true, false);


        $source = $this->generateUrl(
            'pumukit_moodle_moodle_embed',
            array(
                'id' => $multimediaObject->getId(),
                'lang' => $locale,
                'opencast' => ($multimediaObject->getProperty('opencast') ? '1' : '0'),
                'autostart' => false,
            ),
            true
        );


        $mmArray = array(
            'title' => $multimediaObject->getTitle($locale).'.mp4',
            'shorttitle' => $multimediaObject->getTitle($locale),
            'url' => $url,
            'thumbnail' => $thumbnail,
            'thumbnail_width' => $width,
            'thumbnail_height' => $height,
            'icon' => $thumbnail,
            'source' => $source,
        );

        return $mmArray;
    }

    protected function seriesToArray(Series $series, $locale = null)
    {
        $picService = $this->get('pumukitschema.pic');
        $seriesArray = array();
        $seriesArray['title'] = $series->getTitle($locale);
        $seriesArray['url'] = $this->generateUrl('pumukit_webtv_series_index', array('id' => $series->getId()), true);
        $seriesArray['thumbnail'] = $picService->getDefaultUrlPicForObject($series, true, false);
        $seriesArray['icon'] = $picService->getDefaultUrlPicForObject($series, true, false);
        $seriesArray['children'] = array();

        return $seriesArray;
    }


    protected function getRepositoryMmobjs($searchText)
    {
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');

        $qb = $mmobjRepo->createStandardQueryBuilder();
        //The videos shown in Moodle should be:
        // * All public videos on webtv.
        //             (or)
        // * All videos belonging to the professor:
        //   - Owner of video.
        //   - Belongs to the video group (to edit? to view? both?)
        if ($searchText) {
            $qb = $qb->field('$text')->equals(array('$search' => $searchText));
        }
        $qb->addOr(
            $qb->expr()
               ->field('tags.cod')->equals('PUCHWEBTV')
               ->field('properties.redirect')->equals(false)
               ->field('status')->equals(MultimediaObject::STATUS_PUBLISHED)
        );

        return $qb->limit(60)->getQuery()->execute();
    }


    protected function enableFilter()
    {
        $filter = $this->get('doctrine_mongodb.odm.document_manager')->getFilterCollection()->enable('frontend');
        $filter->setParameter('status', MultimediaObject::STATUS_PUBLISHED);
        $filter->setParameter('display_track_tag', 'display');
    }
}
