<?php

namespace Pumukit\MoodleBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
            throw $this->createNotFoundException('Naked backoffice domain not conf');
        }

        $repo = $this
            ->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PumukitSchemaBundle:User');

        $email = $request->get('email');
        $password = $this->container->getParameter('pumukit_moodle.password');
        $domain = $this->container->getParameter('pumukit2.naked_backoffice_domain');

        //Check domain
        if ($domain != $request->getHost()) {
            throw $this->createNotFoundException('invalid domain!');
        }

        /*
        //Check referer //TODO
        var_dump($request->headers->get('referer'));exit;
        */

        //Check hash
        if ($request->get('hash') != $this->getHash($email, $password, $domain)) {
            throw $this->createNotFoundException('hash not valid!');
        }

        //Only HTTPs
        if (!$request->isSecure()) {
            throw $this->createNotFoundException('Only HTTPs');
        }

        //Find User
        $user = $repo->findOneBy(array('email' => $email));
        if (!$user) {
            try {
                $user = $this->createUser($email);
            } catch (\Exception $e) {
                throw $this->createNotFoundException($e->getMessage());
            }
        }

        /*
        //Only PERSONAL_SCOPE //TODO
        if(!$user->getPermissionProfile() || $user->getPermissionProfile()->getScope() != PermissionProfile::SCOPE_PERSONAL) {
            throw $this->createNotFoundException('Only valid for users with personal scope');
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
        $loginService = $this->get('pumukit.security.login');
        $info = $ldapSerive->getInfoFromEmail($email);

        if (!$info) {
            throw new \RuntimeException('user not found!');
        }

        if (!isset($info["edupersonprimaryaffiliation"][0]) ||
            !in_array($info["edupersonprimaryaffiliation"][0], array('PAS', 'PDI'))) {

            throw new \RuntimeException('user not valid');
        }
        $username = $info["cn"][0];
        $email = $info["mail"][0];
        $origin = 'moodle';
        $group = $loginService->getGroup($info["edupersonprimaryaffiliation"][0], $origin);
        return $loginService->createDefaultUser($username, $email, $origin, $group);
    }
}
