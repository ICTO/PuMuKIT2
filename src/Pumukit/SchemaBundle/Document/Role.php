<?php

namespace Pumukit\SchemaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(repositoryClass="Pumukit\SchemaBundle\Repository\RoleRepository")
 */
class Role
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string $cod
     *
     * @MongoDB\String
     * @MongoDB\UniqueIndex(safe=1)
     */
    private $cod = '0';

    /**
     * @var integer $rank
     *
     * @MongoDB\Int
     * @Gedmo\SortablePosition
     */
    private $rank;

    /**
     * @var int $number_people_in_multimedia_object
     *
     * @MongoDB\Int
     * @MongoDB\Increment
     */
    private $number_people_in_multimedia_object = 0;

    /**
     * See European Broadcasting Union Role Codes
     * @var string $xml
     *
     * @MongoDB\String
     */
    private $xml;

    /**
     * @var boolean $display
     *
     * @MongoDB\Boolean
     */
    private $display = true;

    /**
     * @var string $name
     *
     * @MongoDB\Raw
     */
    private $name = array('en' => '');

    /**
     * @var string $text
     *
     * @MongoDB\Raw
     */
    private $text = array('en' => '');

    /**
     * @var locale $locale
     */
    private $locale = 'en';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cod
     *
     * @param string $cod
     */
    public function setCod($cod)
    {
        $this->cod = $cod;
    }

    /**
     * Get cod
     *
     * @return string
     */
    public function getCod()
    {
        return $this->cod;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set xml
     *
     * @param string $xml
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
    }

    /**
     * Get xml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Set display
     *
     * @param boolean $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

    /**
     * Get display
     *
     * @return boolean
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set name
     *
     * @param string $name
     * @param string|null $locale
     */
    public function setName($name, $locale = null)
    {
        if ($locale == null) {
            $locale = $this->locale;
        }
        $this->name[$locale] = $name;
    }

    /**
     * Get name
     *
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        if ($locale == null) {
            $locale = $this->locale;
        }
        if (!isset($this->name[$locale])) {
            return;
        }

        return $this->name[$locale];
    }

    /**
     * Set I18n name
     *
     * @param array $name
     */
    public function setI18nName(array $name)
    {
        $this->name = $name;
    }

    /**
     * Get i18n name
     *
     * @return array
     */
    public function getI18nName()
    {
        return $this->name;
    }

    /**
     * Set text
     *
     * @param string $text
     * @param string|null $locale
     */
    public function setText($text, $locale = null)
    {
        if ($locale == null) {
            $locale = $this->locale;
        }
        $this->text[$locale] = $text;
    }

    /**
     * Get text
     *
     * @param string|null $locale
     * @return string
     */
    public function getText($locale = null)
    {
        if ($locale == null) {
            $locale = $this->locale;
        }
        if (!isset($this->text[$locale])) {
            return;
        }

        return $this->text[$locale];
    }

    /**
     * Set I18n text
     *
     * @param array $text
     */
    public function setI18nText(array $text)
    {
        $this->text = $text;
    }

    /**
     * Get i18n text
     *
     * @return array
     */
    public function getI18nText()
    {
        return $this->text;
    }

    /**
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Increase number_people_in_multimedia_object
     */
    public function increaseNumberPeopleInMultimediaObject()
    {
        ++$this->number_people_in_multimedia_object;
    }

    /**
     * Decrease number_people_in_multimedia_object
     */
    public function decreaseNumberPeopleInMultimediaObject()
    {
        --$this->number_people_in_multimedia_object;
    }

    /**
     * Get number_people_in_multimedia_object
     */
    public function getNumberPeopleInMultimediaObject()
    {
        return $this->number_people_in_multimedia_object;
    }

    /**
     * Clone Role
     *
     * @return Role
     */
    public function cloneResource()
    {
        $aux = clone $this;
        $aux->id = null;
        $aux->rank = null;

        return $aux;
    }
}
