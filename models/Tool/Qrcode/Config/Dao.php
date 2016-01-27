<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Property
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Tool\Qrcode\Config;

use Pimcore\Model;

class Dao extends Model\Dao\PhpArrayTable {

    /**
     *
     */
    public function configure()
    {
        parent::configure();
        $this->setFile("qrcode");
    }

    /**
     * @param null $id
     * @throws \Exception
     */
    public function getByName($id = null) {

        if ($id != null) {
            $this->model->setName($id);
        }

        $data = $this->db->getById($this->model->getName());

        if(isset($data["id"])) {
            $this->assignVariablesToModel($data);
            $this->model->setName($data["id"]);
        } else {
            throw new \Exception("QR-Code with id: " . $this->model->getName() . " does not exist");
        }
    }

    /**
     * @throws \Exception
     */
    public function save() {

        $ts = time();
        if(!$this->model->getCreationDate()) {
            $this->model->setCreationDate($ts);
        }
        $this->model->setModificationDate($ts);

        try {
            $dataRaw = get_object_vars($this->model);
            $data = [];
            $allowedProperties = ["name","description","url", "foreColor", "backgroundColor", "googleAnalytics",
                "creationDate","modificationDate"];

            foreach($dataRaw as $key => $value) {
                if(in_array($key, $allowedProperties)) {
                    $data[$key] = $value;
                }
            }
            $this->db->insertOrUpdate($data, $this->model->getName());
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Deletes object from database
     *
     * @return void
     */
    public function delete() {
        $this->db->delete($this->model->getName());
    }
}
