<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

use Pimcore\Model\Tool\Qrcode;
use Pimcore\Model\Document;

class Reports_QrcodeController extends \Pimcore\Controller\Action\Admin\Reports
{

    public function init()
    {
        parent::init();

        $notRestrictedActions = array("code");
        if (!in_array($this->getParam("action"), $notRestrictedActions)) {
            $this->checkPermission("qr_codes");
        }
    }

    public function treeAction()
    {
        $codes = [];

        $list = new Qrcode\Config\Listing();
        $items = $list->load();

        foreach ($items as $item) {
            $codes[] = array(
                "id" => $item->getName(),
                "text" => $item->getName()
            );
        }

        $this->_helper->json($codes);
    }

    public function addAction()
    {
        $success = false;

        $code = Qrcode\Config::getByName($this->getParam("name"));

        if (!$code) {
            $code = new Qrcode\Config();
            $code->setName($this->getParam("name"));
            $code->save();

            $success = true;
        }

        $this->_helper->json(array("success" => $success, "id" => $code->getName()));
    }

    public function deleteAction()
    {
        $code = Qrcode\Config::getByName($this->getParam("name"));
        $code->delete();

        $this->_helper->json(array("success" => true));
    }


    public function getAction()
    {
        $code = Qrcode\Config::getByName($this->getParam("name"));
        $this->_helper->json($code);
    }


    public function updateAction()
    {
        $code = Qrcode\Config::getByName($this->getParam("name"));
        $data = \Zend_Json::decode($this->getParam("configuration"));

        foreach ($data as $key => $value) {
            $setter = "set" . ucfirst($key);
            if (method_exists($code, $setter)) {
                $code->$setter($value);
            }
        }

        $code->save();

        $this->_helper->json(array("success" => true));
    }

    public function codeAction()
    {
        $url = "";

        if ($this->getParam("name")) {
            $url = $this->getRequest()->getScheme() . "://" . $this->getRequest()->getHttpHost() . "/qr~-~code/" .
                $this->getParam("name");
        } elseif ($this->getParam("documentId")) {
            $doc = Document::getById($this->getParam("documentId"));
            $url = $this->getRequest()->getScheme() . "://" . $this->getRequest()->getHttpHost()
                . $doc->getFullPath();
        } elseif ($this->getParam("url")) {
            $url = $this->getParam("url");
        }

        $code = new \Endroid\QrCode\QrCode;
        $code->setText($url);
        $code->setPadding(0);
        $code->setSize(500);

        $hexToRGBA = function ($hex) {
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            return ["r" => $r, "g" => $g, "b" => $b, "a" => 0];
        };

        if (strlen($this->getParam("foreColor", "")) == 7) {
            $code->setForegroundColor($hexToRGBA($this->getParam("foreColor")));
        }

        if (strlen($this->getParam("backgroundColor", "")) == 7) {
            $code->setBackgroundColor($hexToRGBA($this->getParam("backgroundColor")));
        }

        header("Content-Type: image/png");
        if ($this->getParam("download")) {
            $code->setSize(4000);
            header('Content-Disposition: attachment;filename="qrcode-' . $this->getParam("name", "preview") . '.png"', true);
        }

        $code->render();

        exit;
    }
}
