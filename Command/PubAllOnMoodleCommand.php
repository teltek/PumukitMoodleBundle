<?php

namespace Pumukit\MoodleBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\MultimediaObject;

class PubAllOnMoodleCommand extends ContainerAwareCommand
{
    private $dm = null;
    private $tagRepo = null;
    private $mmobjRepo = null;
    private $puchTagCode = 'PUCHMOODLE';
    private $webTVTagCode = 'PUCHWEBTV';

    protected function configure()
    {
        $this
          ->setName('moodle:pubvideos:all')
          ->setDescription('Publishes all existing videos in the database into moodle')
          ->setHelp(<<<EOT
Command to activate the 'Moodle PubChannel' on all existing videos in the database. This command does not actually 'publish' the videos, just enables them to be added into your Moodle courses.

Ascii graphic of the logic behind this command:

| pub_channel |  status   |      |   final_pub_channels    | final_status |
|-------------|-----------|      |-------------------------|--------------|
| PUCHWEBTV   | PUBLISHED |      | PUCHMOOODLE & PUCHWEBTV |   PUBLISHED  |
| PUCHWEBTV   |  HIDDEN   |  =>  |      PUCHMOOODLE        |   PUBLISHED  |
| PUCHWEBTV   |  BLOCKED  |  =>  |      PUCHMOOODLE        |   PUBLISHED  |
|    None     | PUBLISHED |      |          None           |   PUBLISHED  |
|    None     |  HIDDEN   |      |          None           |    HIDDEN    |
|    None     |  BLOCKED  |      |          None           |    BLOCKED   |



EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $this->mmobjRepo = $this->dm->getRepository('PumukitSchemaBundle:MultimediaObject');
        $this->tagRepo = $this->dm->getRepository('PumukitSchemaBundle:Tag');
        $this->tagService = $this->getContainer()->get('pumukitschema.tag');
        $moodlePubTag = $this->tagRepo->findOneByCod($this->puchTagCode);
        $webTVPubTag = $this->tagRepo->findOneByCod($this->webTVTagCode);
        if(!$moodlePubTag) {
            $output->writeln(sprintf('<error>The tag with code %s does not exist.</error>', $this->puchTagCode));
            $output->writeln(sprintf('<comment>Did you initialize the Moodle Publication Channel? (moodle:init:pubchannel)</comment>'));
            return 0;
        }
        $output->writeln(sprintf('<info>Adding %s tag to mmobjs...</info>', $this->puchTagCode));
        $qb = $this->mmobjRepo->createStandardQueryBuilder();
        $allMmobjs = $qb->addOr($qb->expr()->field('tags.cod')->notEqual($this->puchTagCode))
                        ->addOr($qb->expr()->field('status')->notEqual(MultimediaObject::STATUS_PUBLISHED))
                        ->field('tags.cod')->equals($webTVPubTag->getCod())
                        ->getQuery()->execute();
        $counter = 0;
        foreach ($allMmobjs as $mmobj) {
            $counter++;
            $logLine = sprintf('Added PUCHMOODLE tag to %s ', $mmobj->getId());
            $this->tagService->addTagToMultimediaObject($mmobj, $moodlePubTag->getId(), false);
            if($mmobj->getStatus() != MultimediaObject::STATUS_PUBLISHED) {
                $logLine .= sprintf('| Changed status from %s to STATUS_PUBLISHED ', ($mmobj->getStatus() == 1)?'STATUS_BLOQ':'STATUS_HIDE');
                $mmobj->setStatus(MultimediaObject::STATUS_PUBLISHED);
                $this->tagService->removeTagFromMultimediaObject($mmobj, $webTVPubTag->getId(), false);
                $logLine .= sprintf('| Removed %s channel from Multimedia Object', $webTVPubTag->getCod());
            }
            $output->writeln(sprintf('<comment>%s</comment>', $logLine));
            $this->dm->persist($mmobj);
            $this->dm->flush();
        }
        $output->writeln(sprintf('<info>%s mmobj published.</info>', $counter));
        return 0;
    }
}
