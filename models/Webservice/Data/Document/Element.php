<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Webservice
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Webservice\Data\Document;

use Pimcore\Model;

class Element extends Model\Webservice\Data
{
    
    /**
     * @var string
     */
    public $type;
    
    /**
     * @var object[]
     */
    public $value;
    
    /**
     * @var string
     */
    public $name;
}
