<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Asset
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Asset\Video\Thumbnail;

use Pimcore\Model;

class Config extends Model\AbstractModel
{

    /**
     * format of array:
     * array(
        array(
            "method" => "myName",
            "arguments" =>
                array(
                    "width" => 345,
                    "height" => 200
                )
        )
     * )
     *
     * @var array
     */
    public $items = array();

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var string
     */
    public $description = "";

    /**
     * @var int
     */
    public $videoBitrate;

    /**
     * @var int
     */
    public $audioBitrate;

    /**
     * @var int
     */
    public $modificationDate;

    /**
     * @var int
     */
    public $creationDate;

    /**
     * @param $name
     * @return null|Config
     */
    public static function getByName($name)
    {
        $cacheKey = "videothumb_" . crc32($name);

        try {
            $thumbnail = \Zend_Registry::get($cacheKey);
            if (!$thumbnail) {
                throw new \Exception("Thumbnail in registry is null");
            }
        } catch (\Exception $e) {
            try {
                $thumbnail = new self();
                $thumbnail->getDao()->getByName($name);

                \Zend_Registry::set($cacheKey, $thumbnail);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $thumbnail;
    }

    /**
     * @return Config
     */
    public static function getPreviewConfig()
    {
        $config = new self();
        $config->setName("pimcore-system-treepreview");
        $config->setAudioBitrate(128);
        $config->setVideoBitrate(700);

        $config->setItems(array(
            array(
                "method" => "scaleByWidth",
                "arguments" =>
                array(
                    "width" => 500
                )
            )
        ));

        return $config;
    }

    /**
     * @param  $name
     * @param  $parameters
     * @return bool
     */
    public function addItem($name, $parameters)
    {
        $this->items[] = array(
            "method" => $name,
            "arguments" => $parameters
        );

        return true;
    }

    /**
     * @param  $name
     * @param  $parameters
     * @return bool
     */
    public function addItemAt($position, $name, $parameters)
    {
        array_splice($this->items, $position, 0, array(array(
            "method" => $name,
            "arguments" => $parameters
        )));

        return true;
    }


    /**
     * @return void
     */
    public function resetItems()
    {
        $this->items = array();
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $audioBitrate
     * @return $this
     */
    public function setAudioBitrate($audioBitrate)
    {
        $this->audioBitrate = (int) $audioBitrate;
        return $this;
    }

    /**
     * @return int
     */
    public function getAudioBitrate()
    {
        return $this->audioBitrate;
    }

    /**
     * @param $videoBitrate
     * @return $this
     */
    public function setVideoBitrate($videoBitrate)
    {
        $this->videoBitrate = (int) $videoBitrate;
        return $this;
    }

    /**
     * @return int
     */
    public function getVideoBitrate()
    {
        return $this->videoBitrate;
    }

    /**
     * @return array
     */
    public function getEstimatedDimensions()
    {
        $dimensions = array();
        $transformations = $this->getItems();
        if (is_array($transformations) && count($transformations) > 0) {
            foreach ($transformations as $transformation) {
                if (!empty($transformation)) {
                    if (is_array($transformation["arguments"])) {
                        foreach ($transformation["arguments"] as $key => $value) {
                            if ($key == "width" || $key == "height") {
                                $dimensions[$key] = $value;
                            }
                        }
                    }
                }
            }
        }

        return $dimensions;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
