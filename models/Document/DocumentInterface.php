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
 * @package    Document
 * @copyright  Copyright (c) 2009-2014 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

namespace Pimcore\Model\Document;

use Pimcore\Model;

interface DocumentInterface extends Model\Element\ElementInterface {

    /**
     * @param string $path
     * @return Model\Document
     */
    public static function getByPath($path);

    /**
     * @param string $id
     * @return Model\Document|Model\Document\Page|Model\Document\Folder|Model\Document\Snippet|Model\Document\Link
     */
    public static function getConcreteById($id);

    /**
     * @param string $path
     * @return Model\Document|Model\Document\Page|Model\Document\Folder|Model\Document\Snippet|Model\Document\Link
     */
    public static function getConcreteByPath($path);


    /**
     * @return void
     */
    public function save();

    /**
     * @return void
     */
    public function delete();
}
