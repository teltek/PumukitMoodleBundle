<?php

namespace Pumukit\MoodleBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Pumukit\SchemaBundle\Document\Tag;

class MoodleInitTagsCommand extends ContainerAwareCommand
{
    private $dm = null;
    private $tagRepo = null;

    protected function configure()
    {
        $this
          ->setName('moodle:init:pubchannel')
          ->setDescription('Loads the moodle pubchannel to your database')
          ->setHelp(<<<EOT
Command to load the PUCHMOODLE pubchannel to the db. Required to publish objects exclusively on the Moodle platform.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $this->tagRepo = $this->dm->getRepository('PumukitSchemaBundle:Tag');

        $moodlePublicationChannelTag = $this->createTagWithCode('PUCHMOODLE', 'Moodle', 'PUBCHANNELS', false);
        $moodlePublicationChannelTag->setProperty('modal_path', 'pumukitmoodle_modal_index');
        $this->dm->persist($moodlePublicationChannelTag);
        $this->dm->flush();

        $output->writeln('Tag persisted - new id: '.$moodlePublicationChannelTag->getId().' cod: '.$moodlePublicationChannelTag->getCod());

        return 0;
    }

    private function createTagWithCode($code, $title, $tagParentCode = null, $metatag = false)
    {
        if ($tag = $this->tagRepo->findOneByCod($code)) {
            throw new \Exception('Nothing done - Tag retrieved from DB id: '.$tag->getId().' cod: '.$tag->getCod());
        }
        $tag = new Tag();
        $tag->setCod($code);
        $tag->setMetatag($metatag);
        $tag->setDisplay(true);
        $tag->setTitle($title, 'es');
        $tag->setTitle($title, 'gl');
        $tag->setTitle($title, 'en');
        if ($tagParentCode) {
            if ($parent = $this->tagRepo->findOneByCod($tagParentCode)) {
                $tag->setParent($parent);
            } else {
                throw new \Exception('Nothing done - There is no tag in the database with code '.$tagParentCode.' to be the parent tag');
            }
        }
        $this->dm->persist($tag);
        $this->dm->flush();

        return $tag;
    }
}
