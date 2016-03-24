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

namespace Pimcore\Model\Document\DocType;

use Pimcore\Model;

class Listing extends Model\Listing\JsonListing
{

    /**
     * Contains the results of the list. They are all an instance of Document\Doctype
     *
     * @var array
     */
    public $docTypes = array();

    /**
     * @return array
     */
    public function getDocTypes()
    {
        return $this->docTypes;
    }

    /**
     * @param array $docTypes
     * @return void
     */
    public function setDocTypes($docTypes)
    {
        $this->docTypes = $docTypes;
        return $this;
    }
}
