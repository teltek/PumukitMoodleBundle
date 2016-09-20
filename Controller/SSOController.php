<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pumukit\SchemaBundle\Services\UserService;
use Pumukit\SchemaBundle\Document\User;
use Pumukit\SchemaBundle\Document\Group;

/**
 * @Route("/pumoodle")
 */
class SSOController extends Controller
{
    /**
     * Parametes:
     *   - email
     *   - hash.
     *
     * @Route("/sso")
     */
    public function ssoAction(Request $request)
    {
        //TODO Disable by default
        if (!$this->container->hasParameter('pumukit2.naked_backoffice_domain')) {
            $message = 'The domain "pumukit2.naked_backoffice_domain" is not configured.';
            $response = new Response(
                $this->renderView('PumukitMoodleBundle:SSO:error.html.twig', array('message' => $message)),
                404
            );

            return $response;
        }

        $repo = $this
            ->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PumukitSchemaBundle:User');

        $email = $request->get('email');
        $password = $this->container->getParameter('pumukit_moodle.password');
        $domain = $this->container->getParameter('pumukit2.naked_backoffice_domain');

        //Check domain
        if ($domain != $request->getHost()) {
            $message = 'Invalid Domain!';
            $response = new Response(
                $this->renderView('PumukitMoodleBundle:SSO:error.html.twig', array('message' => $message)),
                404
            );

            return $response;
        }

        /*
           //Check referer //TODO
           var_dump($request->headers->get('referer'));exit;
         */

        //Check hash
        if ($request->get('hash') != $this->getHash($email, $password, $domain)) {
            $message = 'The hash is not valid.';
            $response = new Response(
                $this->renderView('PumukitMoodleBundle:SSO:error.html.twig', array('message' => $message)),
                404
            );

            return $response;
        }

        //Only HTTPs
        if (!$request->isSecure()) {
            $message = 'Only HTTPS connections are allowed.';
            $response = new Response(
                $this->renderView('PumukitMoodleBundle:SSO:error.html.twig', array('message' => $message)),
                404
            );

            return $response;
        }

        //Find User
        try {
            $user = $repo->findOneBy(array('email' => $email));
            if (!$user) {
                $user = $this->createUser($email);
            } else {
                //Promote User from Viewer to Auto Publisher
                $this->promoteUser($user);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $response = new Response(
                $this->renderView('PumukitMoodleBundle:SSO:error.html.twig', array('message' => $message)),
                404
            );

            return $response;
        }

        /*
           //Only PERSONAL_SCOPE //TODO
           if(!$user->getPermissionProfile() || $user->getPermissionProfile()->getScope() != PermissionProfile::SCOPE_PERSONAL) {
           return new Response('Only valid for users with personal scope');
           }
         */

        $this->login($user, $request);

        return new RedirectResponse('/admin/series');
    }

    private function getHash($email, $password, $domain)
    {
        $date = date('d/m/Y');

        return md5($email.$password.$date.$domain);
    }

    private function login($user, Request $request)
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'public', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    private function createUser($email)
    {
        $ldapSerive = $this->get('pumukit_ldap.ldap');
        $permissionProfileService = $this->get('pumukitschema.permissionprofile');
        $userService = $this->container->get('pumukitschema.user');
        $personService = $this->container->get('pumukitschema.person');

        $info = $ldapSerive->getInfoFromEmail($email);

        if (!$info) {
            throw new \RuntimeException('User not found.');
        }
        //TODO Move to a service
        if (!isset($info['edupersonprimaryaffiliation'][0]) ||
            !in_array($info['edupersonprimaryaffiliation'][0], array('PAS', 'PDI'))) {
            throw new \RuntimeException('User invalid.');
        }

        //TODO create createDefaultUser in UserService.
        //$this->userService->createDefaultUser($user);
        $user = new User();
        $user->setUsername($info['cn'][0]);
        $user->setEmail($info['mail'][0]);

        $permissionProfile = $permissionProfileService->getByName('Auto Publisher');
        $user->setPermissionProfile($permissionProfile);
        $user->setOrigin('moodle');
        $user->setEnabled(true);

        $userService->create($user);
        $group = $this->getGroup($info['edupersonprimaryaffiliation'][0]);
        $userService->addGroup($group, $user, true, false);
        $personService->referencePersonIntoUser($user);

        return $user;
    }

    private function getGroup($key)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $repo = $dm->getRepository('PumukitSchemaBundle:Group');
        $groupService = $this->get('pumukitschema.group');

        $cleanKey = preg_replace('/\W/', '', $key);

        $group = $repo->findOneByKey($cleanKey);
        if ($group) {
            return $group;
        }

        $group = new Group();
        $group->setKey($cleanKey);
        $group->setName($key);
        $group->setOrigin('cas');
        $groupService->create($group);

        return $group;
    }

    //Promote User from Viewer to Auto Publisher
    private function promoteUser(User $user)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $permissionProfileService = $this->get('pumukitschema.permissionprofile');
        $ldapSerive = $this->get('pumukit_ldap.ldap');

        $permissionProfileViewer = $permissionProfileService->getByName('Viewer');
        $permissionProfileAutoPub = $permissionProfileService->getByName('Auto Publisher');

        if ($permissionProfileViewer == $user->getPermissionProfile()) {
            $info = $ldapSerive->getInfoFromEmail($user->getEmail());

            if (!$info) {
                throw new \RuntimeException('User not found.');
            }
            //TODO Move to a service
            if (!isset($info['edupersonprimaryaffiliation'][0]) ||
                !in_array($info['edupersonprimaryaffiliation'][0], array('PAS', 'PDI'))) {
                throw new \RuntimeException('User invalid.');
            }

            $user->setPermissionProfile($permissionProfileAutoPub);
            $dm->persist($user);
            $dm->flush();
        }
    }
}
