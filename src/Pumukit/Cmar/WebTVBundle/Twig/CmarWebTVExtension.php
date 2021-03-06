<?php

namespace Pumukit\Cmar\WebTVBundle\Twig;

use Symfony\Component\Intl\Intl;
use Pumukit\SchemaBundle\Document\Broadcast;

class CmarWebTVExtension extends \Twig_Extension
{
    private $languages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->languages = Intl::getLanguageBundle()->getLanguageNames();
    }
  
    /**
     * Get name
     */
    public function getName()
    {
        return 'pumukitcmarwebtv_extension';
    }

    /**
     * Get filters
     */
    public function getFilters()
    {
        return array(
                     new \Twig_SimpleFilter('language_name', array($this, 'getLanguageName')),
                     );
    }

    /**
     * Get functions
     */
    function getFunctions()
    {
      return array(
                   new \Twig_SimpleFunction('iframeurl', array($this, 'getIframeUrl')),
                   );
    }

    /**
     * Get language name
     *
     * @param string $code
     * @return string
     */
    public function getLanguageName($code)
    {
        return ucfirst($this->languages[$code]);
    }

    /**
     * Get Iframe URL
     *
     * @return string
     */
    public function getIframeUrl($multimediaObject, $isHTML5=false, $isDownloadable=false)
    {
        $opencastTrack = $multimediaObject->getTrackWithTag('opencast');

        $url = str_replace('%id%', $multimediaObject->getProperty('opencast'), $multimediaObject->getProperty('opencasturl'));

        $broadcast_type = $multimediaObject->getBroadcast()->getBroadcastTypeId();
        if (Broadcast::BROADCAST_TYPE_PUB == $broadcast_type) {
            $url_player = 'cmarwatch.html';
        } else {
            $url_player = 'securitywatch.html';
        }
        $url = str_replace('cmarwatch.html', $url_player, $url);

        if ($isHTML5) {
            $url = str_replace('/engage/ui/', '/paellaengage/ui/', $url);
        }

        if ($isDownloadable) {
          $url = $url . "&videomode=progressive";
        }

        $invert = $multimediaObject->getProperty('opencastinvert');
        if ($invert && $isHTML5) {
            $url = $url . "&display=invert";
        }

        return $url;
    }
}