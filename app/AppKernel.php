<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Pumukit\SchemaBundle\PumukitSchemaBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new Pumukit\AdminBundle\PumukitAdminBundle(),
            new Pumukit\DirectBundle\PumukitDirectBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Pumukit\EncoderBundle\PumukitEncoderBundle(),
            new Pumukit\InspectionBundle\PumukitInspectionBundle(),
            new Pumukit\NewAdminBundle\PumukitNewAdminBundle(),
            new Pumukit\LiveBundle\PumukitLiveBundle(),
            new Pumukit\OpencastBundle\PumukitOpencastBundle(),
            new Pumukit\WorkflowBundle\PumukitWorkflowBundle(),
            new Pumukit\WizardBundle\PumukitWizardBundle(),
            new Pumukit\ArcaBundle\PumukitArcaBundle(),
            new Pumukit\WebTVBundle\PumukitWebTVBundle(),
            //new Pumukit\Cmar\WebTVBundle\PumukitCmarWebTVBundle(),
            //new Pumukit\Cmar\SonarBundle\PumukitCmarSonarBundle(),
            new Pumukit\Cmar\PodcastBundle\PumukitCmarPodcastBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Pumukit\ExampleDataBundle\PumukitExampleDataBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
