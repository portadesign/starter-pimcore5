<?php
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    User
 * @copyright  Copyright (c) 2009-2014 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

namespace Pimcore\Model\User\Role;

use Pimcore\Model;

class Listing extends Model\User\Listing\AbstractListing {

    /**
     * @var string
     */
    public $type = "role";

    /**
     * Alias for $this->getItems()
     * @return array
     */
    public function getRoles() {
        return $this->getItems();
    }
}
