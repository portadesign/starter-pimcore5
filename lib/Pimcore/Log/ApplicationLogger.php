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

namespace Pimcore\Log;

use Psr\Log\LoggerInterface;

class ApplicationLogger /*implements LoggerInterface*/
{

    // we cannot implement LoggerInterface because then we wouldn't able to be compatible with the old logger
    // but we are definitely PSR-3 compatible

    /**
     * @var null
     */
    protected $component = null;

    /**
     * @var null
     */
    protected $fileObject = null;

    /**
     * @var null
     */
    protected $relatedObject = null;

    /**
     * @var string
     */
    protected $relatedObjectType = 'object';

    /**
     * @var array
     */
    protected $loggers = [];

    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @param string $component
     * @return ApplicationLogger
     */
    public static function getInstance($component = "default", $initDbHandler = false)
    {
        if (array_key_exists($component, self::$instances)) {
            return self::$instances[$component];
        }

        $logger = new self;
        $logger->setComponent($component);

        if ($initDbHandler) {
            $logger->addWriter(new \Pimcore\Log\Handler\ApplicationLoggerDb());
        }

        self::$instances[$component] = $logger;

        return $logger;
    }

    /**
     * Shorthand to get a Db Logger
     *
     * @param string $component
     * @param string $logLevel
     *
     * @return static
     * @throws \Zend_Log_Exception
     */
    public static function getDbLogger($component = null, $logLevel = "error")
    {
        $logger = self::getInstance($component, true);
        return $logger;
    }

    /**
     * @param $writer
     */
    public function addWriter($writer)
    {
        if ($writer instanceof \Zend_Log_Writer_Abstract) {
            // ZF compatibility
            if (!isset($this->loggers["default-zend"])) {
                // auto init Monolog logger
                $this->loggers["default-zend"] = new \Zend_Log();
            }
            $this->loggers["default-zend"]->addWriter($writer);
        } elseif ($writer instanceof \Monolog\Handler\HandlerInterface) {
            if (!isset($this->loggers["default-monolog"])) {
                // auto init Monolog logger
                $this->loggers["default-monolog"] = new \Monolog\Logger('app');
            }
            $this->loggers["default-monolog"]->pushHandler($writer);
        } elseif ($writer instanceof \Psr\Log\LoggerInterface) {
            $this->loggers[] = $writer;
        }
    }



    /**
     * @param string $component
     * @return void
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @param \Pimcore\Log\FileObject | string $fileObject
     * @return void
     */
    public function setFileObject($fileObject)
    {
        $this->fileObject = $fileObject;
    }

    /**
     * @param \\Pimcore\Model\Object\AbstractObject | \Pimcore\Model\Document | \Pimcore\Model\Asset | int $relatedObject
     * @return void
     */
    public function setRelatedObject($relatedObject)
    {
        $this->relatedObject = $relatedObject;

        if ($this->relatedObject instanceof \Pimcore\Model\Object\AbstractObject) {
            $this->relatedObjectType = 'object';
        } elseif ($this->relatedObject instanceof \Pimcore\Model\Asset) {
            $this->relatedObjectType = 'asset';
        } elseif ($this->relatedObject instanceof \Pimcore\Model\Document) {
            $this->relatedObjectType = 'document';
        } else {
            $this->relatedObjectType = 'object';
        }
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($context["component"])) {
            $context["component"] = $this->component;
        }

        if (!isset($context["fileObject"]) && $this->fileObject) {
            $context["fileObject"] = $this->fileObject;
        }

        if (isset($context["fileObject"])) {
            if (is_string($context["fileObject"])) {
                $context["fileObject"] = \Pimcore\Tool::getHostUrl() . "/" . str_replace(PIMCORE_DOCUMENT_ROOT, '', $context["fileObject"]);
            } else {
                $context["fileObject"] = \Pimcore\Tool::getHostUrl() . "/" . str_replace(PIMCORE_DOCUMENT_ROOT, '', $context["fileObject"]->getFilename());
            }
        }

        $relatedObject = null;
        if (isset($context["relatedObject"])) {
            $relatedObject = $context["relatedObject"];
        }

        if (!$relatedObject && $this->relatedObject) {
            $relatedObject = $this->relatedObject;
        }

        if ($relatedObject) {
            if ($relatedObject instanceof \Pimcore\Model\Object\AbstractObject or $relatedObject instanceof \Pimcore\Model\Document or $relatedObject instanceof \Pimcore\Model\Asset) {
                $relatedObject = $relatedObject->getId();
            }
            if (is_numeric($relatedObject)) {
                $context["relatedObject"] = $relatedObject;
                $context["relatedObjectType"] = $this->relatedObjectType;
            }
        }


        $backtrace = debug_backtrace();
        $call = $backtrace[1];

        if ($call["class"] == "Pimcore\\Log\\ApplicationLogger") {
            $call = $backtrace[2];
        }

        $call["line"] = $backtrace[0]["line"];
        $context['source'] = $call["class"] . $call["type"] . $call["function"] . "() :" . $call["line"];

        foreach ($this->loggers as $logger) {
            if ($logger instanceof \Psr\Log\LoggerInterface) {
                $logger->log($level, $message, $context);
            } elseif ($logger instanceof \Zend_Log) {
                // zf compatibility
                $zendLoggerPsr3Mapping = array_flip(\Logger::getZendLoggerPsr3Mapping());
                $prio = $zendLoggerPsr3Mapping[$level];
                $logger->log($message, $prio, $context);
            }
        }

        return null;
    }

    /**
     * @param string $message
     */
    public function emergency($message)
    {
        $this->handleLog("emergency", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function critical($message)
    {
        $this->handleLog("critical", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function error($message)
    {
        $this->handleLog("error", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function alert($message)
    {
        $this->handleLog("alert", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function warning($message)
    {
        $this->handleLog("warning", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function notice($message)
    {
        $this->handleLog("notice", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function info($message)
    {
        $this->handleLog("info", $message, func_get_args());
    }

    /**
     * @param string $message
     */
    public function debug($message)
    {
        $this->handleLog("debug", $message, func_get_args());
    }

    /**
     *
     */
    protected function handleLog($level, $message, $params)
    {
        $context = [];

        if (isset($params[1])) {
            if (is_array($params[1])) {
                // standard PSR-3 -> $context is an array
                $context = $params[1];
            } elseif ($params[1] instanceof \Pimcore\Model\Element\ElementInterface) {
                $context["relatedObject"] = $params[1];
            }
        }

        if (isset($params[2])) {
            if ($params[2] instanceof \Pimcore\Log\FileObject) {
                $context["fileObject"] = $params[2];
            }
        }

        if (isset($params[3])) {
            if (is_string($params[3])) {
                $context["component"] = $params[3];
            }
        }

        $this->log($level, $message, $context);
    }

    /**
     * @param $message
     * @param $exceptionObject
     * @param string $priority
     * @param null $relatedObject
     * @param null $component
     */
     public function logException($message, $exceptionObject, $priority = "alert", $relatedObject = null, $component = null)
     {
         if (is_null($priority)) {
             $priority = Zend_Log::ALERT;
         }

         $message .= ' : '.$exceptionObject->getMessage();

         //workaround to prevent "nesting level to deep" errors when used var_export()
         ob_start();
         var_dump($exceptionObject);
         $dataDump = ob_get_clean();

         if (!$dataDump) {
             $dataDump = $exceptionObject->getMessage();
         }

         $fileObject = new \Pimcore\Log\FileObject($dataDump);

         $this->log($priority, $message, [
             "relatedObject" => $relatedObject,
             "fileObject" => $fileObject,
             "component" => $component
         ]);
     }
}
