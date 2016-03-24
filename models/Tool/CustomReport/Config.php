<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @category   Pimcore
 * @package    Tool
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Model\Tool\CustomReport;

use Pimcore\Model;

class Config extends Model\AbstractModel
{

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var string
     */
    public $sql = "";

    /**
     * @var string[]
     */
    public $dataSourceConfig = array();

    /**
     * @var array
     */
    public $columnConfiguration = array();

    /**
     * @var string
     */
    public $niceName = "";

    /**
     * @var string
     */
    public $group = "";

    /**
     * @var string
     */
    public $groupIconClass = "";

    /**
     * @var string
     */
    public $iconClass = "";

    /**
     * @var bool
     */
    public $menuShortcut;

    /**
     * @var string
     */
    public $chartType;

    /**
     * @var string
     */
    public $pieColumn;

    /**
     * @var string
     */
    public $pieLabelColumn;

    /**
     * @var string
     */
    public $xAxis;

    /**
     * @var string|array
     */
    public $yAxis;

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
        try {
            $report = new self();
            $report->getDao()->getByName($name);
        } catch (\Exception $e) {
            return null;
        }

        return $report;
    }

    /**
     * @return array
     */
    public static function getReportsList()
    {
        $reports = [];

        $list = new Config\Listing();
        $items = $list->load();

        foreach ($items as $item) {
            $reports[] = array(
                "id" => $item->getName(),
                "text" => $item->getName()
            );
        }

        return $reports;
    }

    /**
     * @param $configuration
     * @param null $fullConfig
     * @return mixed
     */
    public static function getAdapter($configuration, $fullConfig = null)
    {
        $type = $configuration->type ? ucfirst($configuration->type) : 'Sql';
        $adapter = "\\Pimcore\\Model\\Tool\\CustomReport\\Adapter\\{$type}";
        return new $adapter($configuration, $fullConfig);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $sql
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param array $columnConfiguration
     */
    public function setColumnConfiguration($columnConfiguration)
    {
        $this->columnConfiguration = $columnConfiguration;
    }

    /**
     * @return array
     */
    public function getColumnConfiguration()
    {
        return $this->columnConfiguration;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $groupIconClass
     */
    public function setGroupIconClass($groupIconClass)
    {
        $this->groupIconClass = $groupIconClass;
    }

    /**
     * @return string
     */
    public function getGroupIconClass()
    {
        return $this->groupIconClass;
    }

    /**
     * @param string $iconClass
     */
    public function setIconClass($iconClass)
    {
        $this->iconClass = $iconClass;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @param string $niceName
     */
    public function setNiceName($niceName)
    {
        $this->niceName = $niceName;
    }

    /**
     * @return string
     */
    public function getNiceName()
    {
        return $this->niceName;
    }

    /**
     * @param boolean $menuShortcut
     */
    public function setMenuShortcut($menuShortcut)
    {
        $this->menuShortcut = (bool) $menuShortcut;
    }

    /**
     * @return boolean
     */
    public function getMenuShortcut()
    {
        return $this->menuShortcut;
    }


    /**
     * @param \string[] $dataSourceConfig
     */
    public function setDataSourceConfig($dataSourceConfig)
    {
        $this->dataSourceConfig = $dataSourceConfig;
    }

    /**
     * @return \string[]
     */
    public function getDataSourceConfig()
    {
        return $this->dataSourceConfig;
    }

    /**
     * @param string $chartType
     */
    public function setChartType($chartType)
    {
        $this->chartType = $chartType;
    }

    /**
     * @return string
     */
    public function getChartType()
    {
        return $this->chartType;
    }

    /**
     * @param string $pieColumn
     */
    public function setPieColumn($pieColumn)
    {
        $this->pieColumn = $pieColumn;
    }

    /**
     * @return string
     */
    public function getPieColumn()
    {
        return $this->pieColumn;
    }

    /**
     * @param string $xAxis
     */
    public function setXAxis($xAxis)
    {
        $this->xAxis = $xAxis;
    }

    /**
     * @return string
     */
    public function getXAxis()
    {
        return $this->xAxis;
    }

    /**
     * @param array|string $yAxis
     */
    public function setYAxis($yAxis)
    {
        $this->yAxis = $yAxis;
    }

    /**
     * @return array|string
     */
    public function getYAxis()
    {
        return $this->yAxis;
    }

    /**
     * @param string $pieLabelColumn
     */
    public function setPieLabelColumn($pieLabelColumn)
    {
        $this->pieLabelColumn = $pieLabelColumn;
    }

    /**
     * @return string
     */
    public function getPieLabelColumn()
    {
        return $this->pieLabelColumn;
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
