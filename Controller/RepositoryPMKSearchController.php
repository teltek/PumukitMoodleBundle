<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        $this->enableFilter();
        $email = $request->get('professor_email');
        $ticket = $request->get('ticket');
        $locale = $this->getLocale($request->get('lang'));
        $searchText = $request->get('search');

        $roleCode = $this->container->getParameter('pumukit_moodle.role');
        $professor = $this->findProfessorEmailTicket($email, $ticket, $roleCode);
        $picService = $this->get('pumukitschema.pic');

        $seriesResult = array();
        $numberMultimediaObjects = 0;
        $multimediaObjects = $this->getRepositoryMmobjs($professor, $roleCode, $searchText);
        foreach ($multimediaObjects as $multimediaObject) {
            $seriesId = $multimediaObject->getSeries()->getId();
            if(!isset($seriesResult[$seriesId])) {
                $series = $multimediaObject->getSeries();
                $seriesResult[$seriesId] = $this->seriesToArray($series, $locale);
            }
            $mmobjResult = $this->mmobjToArray($multimediaObject, $locale);
            $seriesResult[$seriesId]['children'][] = $mmobjResult;
            ++$numberMultimediaObjects;
        }

        $playlists =  $this->getRepositoryPlaylists($multimediaObjects);
        $playlistsResult = array();
        foreach($playlists as $playlist) {
            $playlistId = $playlist->getId();
            if(!isset($playlistResult[$playlistId])) {
                $playlistsResult[$playlistId] = $this->playlistToArray($playlist, $locale);
            }
        }

        $out['status'] = 'OK';
        $out['status_txt'] = $numberMultimediaObjects;
        //Dividing the results in 'series' and 'playlists'.
        $out['out'] = array(
            array(
                'title' => 'series',
                'children' => $seriesResult,
            ),
            array(
                'title' => 'playlists',
                'children' => $playlistsResult,
            ),
        );

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

        return ($check === $ticket);
    }

    private function findProfessorEmailTicket($email, $ticket, $roleCode)
    {
        $repo = $this->get('doctrine_mongodb.odm.document_manager')
                     ->getRepository('PumukitSchemaBundle:Person');

        $professor = $repo->findByRoleCodAndEmail($roleCode, $email);
        if ($this->checkFieldTicket($email, $ticket)) {
            return $professor;
        }

        return;
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
        $width  = 140;
        $height = 105;
        $url = $this->generateUrl('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), true);
        $thumbnail = $picService->getFirstUrlPic($multimediaObject, true, false);
        $mmArray = array(
            'title' => $multimediaObject->getTitle($locale) . ".mp4",
            'shorttitle'=> $multimediaObject->getTitle($locale),
            'url' => $url,
            'thumbnail' => $thumbnail,
            'thumbnail_width' => $width,
            'thumbnail_height' => $height,
            'icon' => $thumbnail,
            'source' => $this->generateUrl(
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

    protected function playlistToArray(Series $playlist, $locale = null)
    {
        $picService = $this->get('pumukitschema.pic');
        $width  = 140;
        $height = 105;
        $thumbnail = $picService->getDefaultUrlPicForObject($playlist, true, false);
        $source = $this->generateUrl('pumukit_moodle_embed_playlist', array('id' => $playlist->getId()), true);
        return array(
            'title' => $playlist->getTitle(),
            'thumbnail' => $thumbnail,
            'thumbnail_width' => $width,
            'thumbnail_height' => $height,
            'icon' => $thumbnail,
            'source' => $source,
            'children' => $this->playlistMmobjsToArray($playlist),
        );
    }
    protected function playlistMmobjsToArray(Series $playlist, $locale = null)
    {
        $picService = $this->get('pumukitschema.pic');
        $playlistService = $this->get('pumukit_baseplayer.seriesplaylist');
        $playlistMmobjs = $playlistService->getPlaylistMmobjs($playlist);
        $mmobjsArray = array();
        $mmobjsArray[] = array(
            'title' => 'Insert playlist "'.$playlist->getTitle().'".avi',
            'shorttitle' => 'Insert playlist "'.$playlist->getTitle().'".',
            'source' => $this->generateUrl('pumukit_moodle_embed_playlist', array('id' => $playlist->getId()), true),
        );
        foreach($playlistMmobjs as $mmobj) {
            $mmobjsArray[] = $this->mmobjToArray($mmobj, $locale);
        }
        if(count($mmobjsArray) <= 1)
            $mmobjsArray = array();
        return $mmobjsArray;
    }

    protected function getRepositoryMmobjs($professor, $roleCode, $searchText)
    {
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');

        $qb = $mmobjRepo->createStandardQueryBuilder();
        //TODO: The videos shown in Moodle should be:
        // * All public videos (on webtv? on moodle? just published?)
        // * All videos belonging to the professor:
        //   - Owner of video.
        //   - Belongs to the video group (to edit? to view? both?)
        if($searchText)
            $qb = $qb->field('$text')->equals(array('$search' => $searchText));
        $qb->addOr(
            $qb->expr()
               ->field('tags.cod')->equals('PUCHWEBTV')
        );
        if(!$professor) {
            return $qb->getQuery()->execute();
        }
        $filterOwnerExpr = $qb->expr()
                              ->field('tags.cod')->equals('PUCHMOODLE')
                              ->addOr(
                                  $qb->expr()
                                     ->field('people')
                                     ->elemMatch(
                                         $qb->expr()->field('people._id')->equals(new \MongoId($professor->getId()))
                                            ->field('cod')->equals($roleCode)
                                     )
                              );
        $user = $professor->getUser();
        if($user) {
            $filterOwnerExpr->addOr($qb->expr()->field('groups')->in($user->getGroupsIds()));
        }
        $qb->addOr($filterOwnerExpr);
        return $qb->getQuery()->execute();
    }

    protected function getRepositoryPlaylists($mmobjs)
    {
        $seriesRepo = $this->get('doctrine_mongodb.odm.document_manager')
                           ->getRepository('PumukitSchemaBundle:Series');
        $mmobjIds = array();
        foreach($mmobjs as $q) {
            $mmobjIds[] = new \MongoId($q->getId());
        };
        $qb = $seriesRepo->createQueryBuilder()->field('playlist.multimedia_objects')->in($mmobjIds);
        return $qb->getQuery()->execute();
    }

    protected function enableFilter()
    {
        $filter = $this->get('doctrine_mongodb.odm.document_manager')->getFilterCollection()->enable('frontend');
        $filter->setParameter('status', MultimediaObject::STATUS_PUBLISHED);
        $filter->setParameter('display_track_tag', 'display');
    }
}
