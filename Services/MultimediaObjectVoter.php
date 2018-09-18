<?php

namespace Pumukit\MoodleBundle\Services;

use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Services\MultimediaObjectService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpFoundation\RequestStack;

class MultimediaObjectVoter extends Voter
{
    const PLAY = 'play';

    private $mmobjService;
    private $requestStack;
    private $password;

    public function __construct(MultimediaObjectService $mmobjService, RequestStack $requestStack, $password)
    {
        $this->mmobjService = $mmobjService;
        $this->requestStack = $requestStack;
        $this->password = $password;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::PLAY))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof MultimediaObject) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $multimediaObject, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
        case self::PLAY:
            return $this->canPlay($multimediaObject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    protected function canPlay($multimediaObject, $user = null)
    {
        $req = $this->requestStack->getMasterRequest();

        if (!$this->mmobjService->isHidden($multimediaObject, 'PUCHMOODLE')) {
            return false;
        }

        $refererUrl = $req->headers->get('referer');
        if (!$refererUrl) {
            return false;
        }

        $refererQuery = parse_url($refererUrl, PHP_URL_QUERY);
        if (!$refererQuery) {
            return false;
        }

        parse_str($refererQuery, $query);
        if (!isset($query['ticket'])) {
            return false;
        }

        $ticket = $query['ticket'];
        if (!$this->checkFieldTicket('', $ticket, $multimediaObject->getId())) {
            return false;
        }

        return true;
    }

    private function checkFieldTicket($email, $ticket, $id = '')
    {
        $check = '';
        $check = md5($this->password.date('Y-m-d').$id.$email);

        return $check === $ticket;
    }
}
