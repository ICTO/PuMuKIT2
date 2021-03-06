<?php

namespace Pumukit\SchemaBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\SchemaBundle\Document\Pic;
use Pumukit\SchemaBundle\Document\Broadcast;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pumukit\SchemaBundle\Services\MultimediaObjectPicService;

class MultimediaObjectPicServiceTest extends WebTestCase
{
    private $dm;
    private $repo;
    private $factoryService;
    private $mmsPicService;
    private $originalPicPath;
    private $uploadsPath;

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
        $this->mmsPicService = $kernel->getContainer()
          ->get('pumukitschema.mmspic');

        $this->originalPicPath = realpath(__DIR__.'/../Resources').DIRECTORY_SEPARATOR.'logo.png';
        $this->uploadsPath = realpath(__DIR__.'/../../../../../web/uploads/pic');
    }

    public function setUp()
    {
        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Series')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Broadcast')->remove(array());
        $this->dm->flush();
    }

    public function testGetRecommendedPics()
    {
        $pic1 = new Pic();
        $url1 = 'http://domain.com/pic1.png';
        $pic1->setUrl($url1);

        $pic2 = new Pic();
        $url2 = 'http://domain.com/pic2.png';
        $pic2->setUrl($url2);

        $pic3 = new Pic();
        $url3 = 'http://domain.com/pic3.png';
        $pic3->setUrl($url3);

        $pic4 = new Pic();
        $pic4->setUrl($url3);

        $pic5 = new Pic();
        $url5 = 'http://domain.com/pic5.png';
        $pic5->setUrl($url5);

        $this->dm->persist($pic1);
        $this->dm->persist($pic2);
        $this->dm->persist($pic3);
        $this->dm->persist($pic4);
        $this->dm->persist($pic5);
        $this->dm->flush();

        $broadcast = $this->createBroadcast(Broadcast::BROADCAST_TYPE_PRI);

        $series1 = $this->factoryService->createSeries();
        $series2 = $this->factoryService->createSeries();

        $series1 = $this->dm->find('PumukitSchemaBundle:Series', $series1->getId());
        $series2 = $this->dm->find('PumukitSchemaBundle:Series', $series2->getId());

        $mm11 = $this->factoryService->createMultimediaObject($series1);
        $mm12 = $this->factoryService->createMultimediaObject($series1);

        $mm21 = $this->factoryService->createMultimediaObject($series2);

        $mm11 = $this->mmsPicService->addPicUrl($mm11, $pic1);
        $mm11 = $this->mmsPicService->addPicUrl($mm11, $pic2);
        $mm11 = $this->mmsPicService->addPicUrl($mm11, $pic4);

        $mm12 = $this->mmsPicService->addPicUrl($mm12, $pic3);

        $mm21 = $this->mmsPicService->addPicUrl($mm21, $pic5);

        $this->dm->persist($mm11);
        $this->dm->persist($mm12);
        $this->dm->persist($mm21);
        $this->dm->flush();

        $this->assertEquals(4, count($this->mmsPicService->getRecommendedPics($series1)));
    }

    public function testAddPicUrl()
    {
        $broadcast = $this->createBroadcast(Broadcast::BROADCAST_TYPE_PUB);

        $series = $this->factoryService->createSeries();
        $mm = $this->factoryService->createMultimediaObject($series);

        $this->assertEquals(0, count($mm->getPics()));

        $url = 'http://domain.com/pic.png';

        $mm = $this->mmsPicService->addPicUrl($mm, $url);

        $this->assertEquals(1, count($mm->getPics()));
        $this->assertEquals(1, count($this->repo->find($mm->getId())->getPics()));
    }

    public function testAddPicFile()
    {
        $broadcast = $this->createBroadcast(Broadcast::BROADCAST_TYPE_PUB);

        $series = $this->factoryService->createSeries();
        $mm = $this->factoryService->createMultimediaObject($series);

        $this->assertEquals(0, count($mm->getPics()));

        $picPath = realpath(__DIR__.'/../Resources').DIRECTORY_SEPARATOR.'picCopy.png';
        if (copy($this->originalPicPath, $picPath)){
            $picFile = new UploadedFile($picPath, 'pic.png', null, null, null, true);
            $mm = $this->mmsPicService->addPicFile($mm, $picFile);
            $mm = $this->repo->find($mm->getId());

            $this->assertEquals(1, count($mm->getPics()));

            $pic = $mm->getPics()[0];
            $this->assertTrue($mm->containsPic($pic));

            $uploadedPic = '/uploads/pic/'.$mm->getId().DIRECTORY_SEPARATOR.$picFile->getClientOriginalName();
            $this->assertEquals($uploadedPic, $pic->getUrl());
        }

        $this->deleteCreatedFiles();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage for storing Pics does not exist
     */
    public function testInvalidTargetPath()
    {
        $mmspicService = new MultimediaObjectPicService($this->dm, "/non/existing/path", "/uploads/pic");
    }

    private function createBroadcast($broadcastTypeId)
    {
        $broadcast = new Broadcast();
        $broadcast->setName(ucfirst($broadcastTypeId));
        $broadcast->setBroadcastTypeId($broadcastTypeId);
        $broadcast->setPasswd('password');
        if (0 === strcmp(Broadcast::BROADCAST_TYPE_PRI, $broadcastTypeId)) {
            $broadcast->setDefaultSel(true);
        } else {
            $broadcast->setDefaultSel(false);
        }
        $broadcast->setDescription(ucfirst($broadcastTypeId).' broadcast');

        $this->dm->persist($broadcast);
        $this->dm->flush();

        return $broadcast;
    }

    private function deleteCreatedFiles()
    {
        $mmobjs = $this->repo->findAll();

        foreach($mmobjs as $mm){
            $mmDir = $this->uploadsPath.DIRECTORY_SEPARATOR.$mm->getId().DIRECTORY_SEPARATOR;

            if (is_dir($mmDir)){
                $files = glob($mmDir.'*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_writable($file)){
                      unlink($file);
                    }
                }

                rmdir($mmDir);
            }
        }
    }
}