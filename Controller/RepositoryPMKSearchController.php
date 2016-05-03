<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Pumukit\SchemaBundle\Document\MultimediaObject;

/**
 * @Route("/pumoodle")
 */
class RepositoryPMKSearchController extends Controller
{
    /**
     * @Route("/search_repository")
     */
    public function searchRepositoryAction(Request $request)
    {
        $email = $request->get('professor_email');
        $ticket = $request->get('ticket');
        $locale = $this->getLocale($request->get('lang'));
        $searchText = $request->get('search');

        $roleCode = $this->container->getParameter('pumukit_moodle.role');

        if (!$professor = $this->findProfessorEmailTicket($email, $ticket, $roleCode)) {
            $out['status'] = 'ERROR';
            $out['status_txt'] = 'Error: professor with email '.$email.' does not have any video on WebTV Channel in the Pumukit server.';
            $out['out'] = null;
            return new JsonResponse($out, 404);
        }
        $seriesResult = array();
        $numberMultimediaObjects = 0;
        $picService = $this->get('pumukitschema.pic');
        $multimediaObjects = $this->getRepositoryMmobjs($professor, $roleCode, $searchText);
        foreach ($multimediaObjects as $multimediaObject) {
            $seriesId = $multimediaObject->getSeries()->getId();
            if(!isset($seriesResult[$seriesId])) {
                $series = $multimediaObject->getSeries();
                $seriesResult[$seriesId] = array();
                $seriesResult[$seriesId]['title'] = $series->getTitle($locale);
                $seriesResult[$seriesId]['url'] = $this->generateUrl('pumukit_webtv_series_index', array('id' => $series->getId()), true);
                $seriesResult[$seriesId]['pic'] = $picService->getFirstUrlPic($series, true, false);
                $seriesResult[$seriesId]['mms'] = array();
            }
            $mmobjResult = $this->mmobjToArray($multimediaObject, $locale);
            $seriesResult[$seriesId]['mms'][] = $mmobjResult;
            ++$numberMultimediaObjects;
        }
        $out['status'] = 'OK';
        $out['status_txt'] = $numberMultimediaObjects;
        $out['out'] = $seriesResult;

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
        $mmArray = array();
        $mmArray['title'] = $multimediaObject->getTitle($locale);
        $mmArray['description'] = $multimediaObject->getDescription($locale);
        $mmArray['date'] = $multimediaObject->getRecordDate()->format('Y-m-d');
        $mmArray['url'] = $this->generateUrl('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), true);
        $mmArray['pic'] = $picService->getFirstUrlPic($multimediaObject, true, false);
        $mmArray['embed'] = $this->generateUrl('pumukit_moodle_moodle_embed',
                                               array(
                                                   'id' => $multimediaObject->getId(),
                                                   'lang' => $locale,
                                                   'opencast' => ($multimediaObject->getProperty('opencast') ? '1' : '0'),
						   'autostart' => false,
                                               ),
                                               true);
        return $mmArray;
    }

    protected function getRepositoryMmobjs($professor, $roleCode, $searchText)
    {
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');

        $qb = $mmobjRepo->createStandardQueryBuilder();
        if($searchText)
            $qb = $qb->field('$text')->equals(array('$search' => $searchText));
        $qb->addOr(
            $qb->expr()
               ->field('people')->elemMatch(
                   $qb->expr()->field('people._id')->equals(new \MongoId($professor->getId()))
                      ->field('cod')->equals($roleCode)
               )
        )->addOr(
            $qb->expr()
               ->field('tags.cod')->equals('PUCHWEBTV')
        );

        return $qb->getQuery()->execute();
    }
}
