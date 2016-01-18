<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Document
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Document\Tag;

interface TagInterface {

    /**
     * Return the data for direct output to the frontend, can also contain HTML code!
     *
     * @return string
     */
    public function frontend();

    /**
     * Return the data for the admin, can also contain HTML code!
     *
     * @return string
     */
    public function admin();

    /**
     * Get the current data stored for the element
     * this is used as general fallback for the methods getDataForResource(), admin(), getValue()
     *
     * @return mixed
     */
    public function getData();

    /**
     * Return the type of the element
     *
     * @return string
     */
    public function getType();

    /**
     * Receives the data from the editmode and convert this to the internal data in the object eg. image-id to Asset\Image
     *
     * @param mixed $data
     * @return void
     */
    public function setDataFromEditmode($data);

    /**
     * Receives the data from the resource, an convert to the internal data in the object eg. image-id to Asset\Image
     *
     * @param mixed $data
     * @return string
     */
    public function setDataFromResource($data);


    /**
     * Receives data from webservice import and fills the current tag's data
     *
     * @abstract
     * @param  object $wsElement
     * @param IdMapper $idMapper
     * @return void
     */
//    JAIC: $idMapper is needed for REST webservice import. However, cannot just add this to
//    the interface as otherwise all tags would have to implement it including those defined
//    in (internal & external) plugins which are already in use.
//    public function getFromWebserviceImport($wsElement, $idMapper = null);


    /**
     * Returns the current tag's data for web service export
     *
     * @abstract
     * @return array
     */
    public function getForWebserviceExport();

}
