<?php

namespace Pumukit\SchemaBundle\Tests\Other;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\Track;
use Pumukit\SchemaBundle\Document\Pic;
use Pumukit\SchemaBundle\Document\Material;
use Pumukit\SchemaBundle\Document\Link;
use Pumukit\SchemaBundle\Document\Person;
use Pumukit\SchemaBundle\Document\Role;
use Pumukit\SchemaBundle\Document\SeriesType;
use Pumukit\SchemaBundle\Document\Broadcast;
use Pumukit\SchemaBundle\Document\Tag;

class MultimediaObjectRankTest extends WebTestCase
{
    private $dm;
    private $repo;
    private $qb;
    private $factoryService;

    public function __construct()
    {
        $options = array('environment' => 'test');
        $kernel = static::createKernel($options);
        $kernel->boot();
        $this->dm = $kernel->getContainer()
            ->get('doctrine_mongodb')->getManager();
        $this->repo = $this->dm
            ->getRepository('PumukitSchemaBundle:MultimediaObject');
        $this->factoryService = $kernel->getContainer()
            ->get('pumukitschema.factory');
    }

    public function setUp()
    {
        //DELETE DATABASE
        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')
            ->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Series')
            ->remove(array());
        $this->dm->flush();
    }

    public function testRank()
    {
        $series = $this->createSeries("Stark's growing pains");
        $otherSeries = $this->createSeries("Stark's growing pains");
        $this->dm->persist($series);
        $this->dm->persist($otherSeries);
        $this->dm->flush();

        $mm1 = $this->createMultimediaObjectAssignedToSeries('MmObject 1', $series);
        $mm2 = $this->createMultimediaObjectAssignedToSeries('MmObject 2', $series);
        $mm3 = $this->createMultimediaObjectAssignedToSeries('MmObject 3', $series);
        $mm4 = $this->createMultimediaObjectAssignedToSeries('MmObject 4', $series);
        $otherMm = $this->createMultimediaObjectAssignedToSeries('MmObject', $otherSeries);

        $this->dm->persist($mm1);
        $this->dm->persist($mm2);
        $this->dm->persist($mm3);
        $this->dm->persist($mm4);
        $this->dm->persist($otherMm);
        $this->dm->flush();

        $this->assertEquals(1, $mm1->getRank());
        $this->assertEquals(2, $mm2->getRank());
        $this->assertEquals(3, $mm3->getRank());
        $this->assertEquals(4, $mm4->getRank());

        $mm1->setRank(2);

        $this->dm->persist($mm1);
        $this->dm->flush();

        $this->assertEquals(2, $mm1->getRank());
        $this->assertEquals(1, $mm2->getRank());
        $this->assertEquals(3, $mm3->getRank());
        $this->assertEquals(4, $mm4->getRank());

        $mm1->setRank(3);

        $this->dm->persist($mm1);
        $this->dm->flush();

        $this->assertEquals(3, $mm1->getRank());
        $this->assertEquals(1, $mm2->getRank());
        $this->assertEquals(2, $mm3->getRank());
        $this->assertEquals(4, $mm4->getRank());

        $mm1->setRank(4);

        $this->dm->persist($mm1);
        $this->dm->flush();

        $this->assertEquals(4, $mm1->getRank());
        $this->assertEquals(1, $mm2->getRank());
        $this->assertEquals(2, $mm3->getRank());
        $this->assertEquals(3, $mm4->getRank());

        $mm1->setRank(1);

        $this->dm->persist($mm1);
        $this->dm->flush();

        $this->assertEquals(1, $mm1->getRank());
        $this->assertEquals(2, $mm2->getRank());
        $this->assertEquals(3, $mm3->getRank());
        $this->assertEquals(4, $mm4->getRank());

        $mm1->setRank(-1);

        $this->dm->persist($mm1);
        $this->dm->flush();

        $this->assertEquals(4, $mm1->getRank());
        $this->assertEquals(1, $mm2->getRank());
        $this->assertEquals(2, $mm3->getRank());
        $this->assertEquals(3, $mm4->getRank());
        
    }

    private function createMultimediaObjectAssignedToSeries($title, Series $series)
    {
        $rank = 1;
        $status = MultimediaObject::STATUS_NEW;
        $record_date = new \DateTime();
        $public_date = new \DateTime();
        $subtitle = 'Subtitle';
        $description = "Description";
        $duration = 123;

        $mm = $this->factoryService->createMultimediaObject($series);

        $mm->setStatus($status);
        $mm->setRecordDate($record_date);
        $mm->setPublicDate($public_date);
        $mm->setTitle($title);
        $mm->setSubtitle($subtitle);
        $mm->setDescription($description);
        $mm->setDuration($duration);

        $this->dm->persist($mm);
        $this->dm->persist($series);
        $this->dm->flush();

        return $mm;
    }

    private function createSeries($title)
    {
        $subtitle = 'subtitle';
        $description = 'description';
        $test_date = new \DateTime("now");

        $series = $this->factoryService->createSeries();

        $series->setTitle($title);
        $series->setSubtitle($subtitle);
        $series->setDescription($description);
        $series->setPublicDate($test_date);

        $this->dm->persist($series);
        $this->dm->flush();

        return $series;
    }

}
