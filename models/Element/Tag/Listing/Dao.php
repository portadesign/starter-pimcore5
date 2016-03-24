<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Element
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Element\Tag\Listing;

use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{

    /**
     * Loads a list of tags for the specifies parameters, returns an array of Element\Tag elements
     *
     * @return array
     */
    public function load()
    {
        $tagsData = $this->db->fetchCol("SELECT id FROM tags" . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $tags = array();
        foreach ($tagsData as $tagData) {
            if ($tag = Model\Element\Tag::getById($tagData)) {
                $tags[] = $tag;
            }
        }

        $this->model->setTags($tags);
        return $tags;
    }


    public function loadIdList()
    {
        $tagsIds = $this->db->fetchCol("SELECT id FROM tags" . $this->getCondition() . $this->getGroupBy() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());
        return $tagsIds;
    }

    public function getTotalCount()
    {
        try {
            $amount = (int) $this->db->fetchOne("SELECT COUNT(*) as amount FROM tags " . $this->getCondition(), $this->model->getConditionVariables());
        } catch (\Exception $e) {
        }

        return $amount;
    }
}
