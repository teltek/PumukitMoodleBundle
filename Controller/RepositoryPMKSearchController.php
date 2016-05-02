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

        $roleCode = $this->container->getParameter('pumukit_moodle.role');
        $seriesRepo = $this->get('doctrine_mongodb.odm.document_manager')
                           ->getRepository('PumukitSchemaBundle:Series');
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');

        if ($professor = $this->findProfessorEmailTicket($email, $ticket, $roleCode)) {
            $series = $seriesRepo->findByPersonIdAndRoleCod($professor->getId(), $roleCode);
            $numberMultimediaObjects = 0;
            $multimediaObjectsArray = array();
            $out = array();
            $picService = $this->get('pumukitschema.pic');
            foreach ($series as $oneseries) {
                $oneSeriesArray = array();
                $oneSeriesArray['title'] = $oneseries->getTitle($locale);
                $oneSeriesArray['url'] = $this->generateUrl('pumukit_webtv_series_index', array('id' => $oneseries->getId()), true);
                $oneSeriesArray['pic'] = $picService->getFirstUrlPic($oneseries, true, false);
                $oneSeriesArray['mms'] = array();
                $multimediaObjects = $mmobjRepo->findBySeriesAndPersonIdWithRoleCod($oneseries, $professor->getId(), $roleCode);
                foreach ($multimediaObjects as $multimediaObject) {
                    $mmArray = $this->mmobjToArray($multimediaObject, $locale);
                    $oneSeriesArray['mms'][] = $mmArray;
                    ++$numberMultimediaObjects;
                }
                $multimediaObjectsArray[] = $oneSeriesArray;
            }
            $out['status'] = 'OK';
            $out['status_txt'] = $numberMultimediaObjects;
            $out['out'] = $multimediaObjectsArray;

            return new JsonResponse($out, 200);
        }
        $out['status'] = 'ERROR';
        $out['status_txt'] = 'Error: professor with email '.$email.' does not have any video on WebTV Channel in the Pumukit server.';
        $out['out'] = null;

        return new JsonResponse($out, 404);
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
}
