<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/pumoodle")
 */
class MoodleController extends Controller
{
    /**
     * @Route("/index")
     */
    public function indexAction(Request $request)
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
            foreach ($series as $oneseries) {
                $seriesTitle = $oneseries->getTitle($locale);
                $multimediaObjectsArray[$seriesTitle] = array();
                $multimediaObjects = $mmobjRepo->findBySeriesAndPersonIdWithRoleCod($oneseries, $professor->getId(), $roleCode);
                foreach ($multimediaObjects as $multimediaObject) {
                    $multimediaObjectTitle = $multimediaObject->getRecordDate()->format('Y-m-d').' '.$multimediaObject->getTitle($locale);
                    if ('' != $multimediaObject->getSubtitle($locale)) {
                        $multimediaObjectTitle .= ' - '.$multimediaObject->getSubtitle($locale);
                    }
                    $multistream = ($multimediaObject->isMultistream() ? '1' : '0');
                    $multimediaObjectsArray[$seriesTitle][$multimediaObjectTitle] = $this->generateUrl('pumukit_moodle_moodle_embed', array('id' => $multimediaObject->getId(), 'lang' => $locale, 'multistream' => $multistream), UrlGeneratorInterface::ABSOLUTE_URL);
                    ++$numberMultimediaObjects;
                }
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
     * @Route("/repository")
     */
    public function repositoryAction(Request $request)
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
                $oneSeriesArray['url'] = $this->generateUrl('pumukit_webtv_series_index', array('id' => $oneseries->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
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
     * @Route("/embed", name="pumukit_moodle_moodle_embed")
     */
    public function embedAction(Request $request)
    {
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');
        $id = $request->get('id');
        $locale = $this->getLocale($request->get('lang'));
        $multimediaObject = $mmobjRepo->find($id);
        $email = $request->get('professor_email');
        $ticket = $request->get('ticket');
        if ($multimediaObject) {
            if ($multimediaObject->containsTagWithCod('PUCHWEBTV') || $this->checkFieldTicket($email, $ticket, $id)) {
                return $this->renderIframe($multimediaObject, $request);
            } else {
                $contactEmail = $this->container->getParameter('pumukit.info')['email'];
                $response = new Response($this->renderView('PumukitMoodleBundle:Moodle:403forbidden.html.twig', array('email' => $contactEmail, 'moodle_locale' => $locale)), 403);

                return $response;
            }
        }
        $response = new Response($this->renderView('PumukitMoodleBundle:Moodle:404notfound.html.twig', array('id' => $id, 'moodle_locale' => $locale)), 404);

        return $response;
    }

    /**
     * @Route("/embed/playlist", name="pumukit_moodle_embed_playlist")
     */
    public function embedPlaylistAction(Request $request)
    {
        $seriesId = $request->get('id');

        return $this->redirect($this->generateUrl('pumukit_playlistplayer_index', array('id' => $seriesId)));
    }

    /**
     * @Route("/metadata", name="pumukit_moodle_moodle_metadata")
     */
    public function metadataAction(Request $request)
    {
        $mmobjRepo = $this->get('doctrine_mongodb.odm.document_manager')
                          ->getRepository('PumukitSchemaBundle:MultimediaObject');
        $id = $request->get('id');
        $locale = $this->getLocale($request->get('lang'));
        $multimediaObject = $mmobjRepo->find($id);
        $email = $request->get('professor_email');
        $ticket = $request->get('ticket');

        if ($multimediaObject) {
            if ($multimediaObject->containsTagWithCod('PUCHWEBTV') || $this->checkFieldTicket($email, $ticket, $id)) {
                $out['status'] = 'OK';
                $out['out'] = $this->mmobjToArray($multimediaObject, $locale);

                return new JsonResponse($out, 200);
            } else {
                $contactEmail = $this->container->getParameter('pumukit.info')['email'];
                $response = new Response($this->renderView('PumukitMoodleBundle:Moodle:403forbidden.html.twig', array('email' => $contactEmail, 'moodle_locale' => $locale)), 403);

                return $response;
            }
        }
        $response = new Response($this->renderView('PumukitMoodleBundle:Moodle:404notfound.html.twig', array('id' => $id, 'moodle_locale' => $locale)), 404);

        return $response;
    }

    /**
     * Render iframe.
     */
    private function renderIframe(MultimediaObject $multimediaObject, Request $request)
    {
        return $this->forward('PumukitBasePlayerBundle:BasePlayer:index', array('request' => $request, 'multimediaObject' => $multimediaObject));
    }

    private function checkFieldTicket($email, $ticket, $id = '')
    {
        $check = '';
        $password = $this->container->getParameter('pumukit_moodle.password');
        $check = md5($password.date('Y-m-d').$id.$email);

        return $check === $ticket;
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
        $pumukitLocales = $this->container->getParameter('pumukit.locales');
        if ((!$locale) || (!in_array($locale, $pumukitLocales))) {
            $locale = $defaultLocale;
        }

        return $locale;
    }

    private function getIsOldBrowser($userAgent)
    {
        $isOldBrowser = false;
        $webExplorer = $this->getWebExplorer($userAgent);
        $version = $this->getVersion($userAgent, $webExplorer);
        if (('IE' == $webExplorer) || ('MSIE' == $webExplorer) || 'Firefox' == $webExplorer || 'Opera' == $webExplorer || ('Safari' == $webExplorer && $version < 4)) {
            $isOldBrowser = true;
        }

        return $isOldBrowser;
    }

    private function getWebExplorer($userAgent)
    {
        if (preg_match('/MSIE/i', $userAgent)) {
            $webExplorer = 'MSIE';
        }
        if (preg_match('/Opera/i', $userAgent)) {
            $webExplorer = 'Opera';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            $webExplorer = 'Firefox';
        }
        if (preg_match('/Safari/i', $userAgent)) {
            $webExplorer = 'Safari';
        }
        if (preg_match('/Chrome/i', $userAgent)) {
            $webExplorer = 'Chrome';
        }

        return $webExplorer;
    }

    private function getVersion($userAgent, $webExplorer)
    {
        $version = null;

        if ('Opera' !== $webExplorer && preg_match('#('.strtolower($webExplorer).')[/ ]?([0-9.]*)#', $userAgent, $match)) {
            $version = floor($match[2]);
        }
        if ('Opera' == $webExplorer || 'Safari' == $webExplorer && preg_match('#(version)[/ ]?([0-9.]*)#', $userAgent, $match)) {
            $version = floor($match[2]);
        }

        return $version;
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
        $mmArray['url'] = $this->generateUrl('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        $mmArray['pic'] = $picService->getFirstUrlPic($multimediaObject, true, false);
        $mmArray['embed'] = $this->generateUrl('pumukit_moodle_moodle_embed',
                                               array(
                                                   'id' => $multimediaObject->getId(),
                                                   'lang' => $locale,
                                                   'multistream' => ($multimediaObject->isMultistream() ? '1' : '0'),
                                                   'autostart' => false,
                                               ),
                                               true);

        return $mmArray;
    }
}
