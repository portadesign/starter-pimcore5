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

namespace Pimcore;

class Update
{

    /**
     * @var string
     */
    public static $updateHost = "update.pimcore.org";

    /**
     * @var bool
     */
    public static $dryRun = false;

    /**
     * @var string
     */
    public static $tmpTable = "_tmp_update";

    /**
     * @return bool
     */
    public static function isWriteable()
    {
        if (self::$dryRun) {
            return true;
        }

        // check permissions
        $files = rscandir(PIMCORE_PATH . "/");

        foreach ($files as $file) {
            if (!is_writable($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param null $currentRev
     * @return array
     * @throws \Exception
     */
    public static function getAvailableUpdates($currentRev = null)
    {
        if (!$currentRev) {
            $currentRev = Version::$revision;
        }

        self::cleanup();

        if (PIMCORE_DEVMODE) {
            $xmlRaw = Tool::getHttpData("http://" . self::$updateHost . "/v2/getUpdateInfo.php?devmode=1&revision=" . $currentRev);
        } else {
            $xmlRaw = Tool::getHttpData("http://" . self::$updateHost . "/v2/getUpdateInfo.php?revision=" . $currentRev);
        }

        $xml = simplexml_load_string($xmlRaw, null, LIBXML_NOCDATA);

        $revisions = array();
        $releases = array();
        if ($xml instanceof \SimpleXMLElement) {
            if (isset($xml->revision)) {
                foreach ($xml->revision as $r) {
                    $date = new \DateTime();
                    $date->setTimestamp((int) $r->date);

                    if (strlen(strval($r->version)) > 0) {
                        $releases[] = array(
                            "id" => strval($r->id),
                            "date" => strval($r->date),
                            "version" => strval($r->version),
                            "text" => strval($r->id) . " - " . $date->format("Y-m-d H:i")
                        );
                    } else {
                        $revisions[] = array(
                            "id" => strval($r->id),
                            "date" => strval($r->date),
                            "text" => strval($r->id) . " - " . $date->format("Y-m-d H:i")
                        );
                    }
                }
            }
        } else {
            throw new \Exception("Unable to retrieve response from update server. Please ensure that your server is allowed to connect to update.pimcore.org:80");
        }

        return array(
            "revisions" => $revisions,
            "releases" => $releases
        );
    }

    /**
     * @param $toRevision
     * @param null $currentRev
     * @return array
     */
    public static function getJobs($toRevision, $currentRev = null)
    {
        if (!$currentRev) {
            $currentRev = Version::$revision;
        }

        $xmlRaw = Tool::getHttpData("http://" . self::$updateHost . "/v2/getDownloads.php?from=" . $currentRev . "&to=" . $toRevision);
        $xml = simplexml_load_string($xmlRaw, null, LIBXML_NOCDATA);

        $jobs = array();
        $updateScripts = array();
        $revisions = array();

        if (isset($xml->download)) {
            foreach ($xml->download as $download) {
                if ($download->type == "script") {
                    $updateScripts[(string) $download->revision]["preupdate"] = array(
                        "type" => "preupdate",
                        "revision" => (string) $download->revision
                    );
                    $updateScripts[(string) $download->revision]["postupdate"] = array(
                        "type" => "postupdate",
                        "revision" => (string) $download->revision
                    );
                }
            }
        }


        if (isset($xml->download)) {
            foreach ($xml->download as $download) {
                $jobs["parallel"][] = array(
                    "type" => "download",
                    "revision" => (string) $download->revision,
                    "url" => (string) $download->url
                );

                $revisions[] = (int) $download->revision;
            }
        }

        $revisions = array_unique($revisions);

        foreach ($revisions as $revision) {
            if ($updateScripts[$revision]["preupdate"]) {
                $jobs["procedural"][] = $updateScripts[$revision]["preupdate"];
            }

            $jobs["procedural"][] = array(
                "type" => "files",
                "revision" => $revision
            );


            if ($updateScripts[$revision]["postupdate"]) {
                $jobs["procedural"][] = $updateScripts[$revision]["postupdate"];
            }
        }

        $jobs["procedural"][] = array(
            "type" => "clearcache"
        );

        $jobs["procedural"][] = array(
            "type" => "cleanup"
        );

        return $jobs;
    }

    /**
     * @param $revision
     * @param $url
     * @throws \Zend_Db_Adapter_Exception
     */
    public static function downloadData($revision, $url)
    {
        $db = Db::get();

        $db->query("CREATE TABLE IF NOT EXISTS `" . self::$tmpTable . "` (
          `id` int(11) NULL DEFAULT NULL,
          `revision` int(11) NULL DEFAULT NULL,
          `path` varchar(255) NULL DEFAULT NULL,
          `action` varchar(50) NULL DEFAULT NULL
        );");

        $downloadDir = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/update/".$revision;
        if (!is_dir($downloadDir)) {
            File::mkdir($downloadDir);
        }

        $filesDir = $downloadDir . "/files";
        if (!is_dir($filesDir)) {
            File::mkdir($filesDir);
        }

        $scriptsDir = $downloadDir . "/scripts";
        if (!is_dir($scriptsDir)) {
            File::mkdir($scriptsDir);
        }

        $xml = Tool::getHttpData($url);
        if ($xml) {
            $parserOptions = LIBXML_NOCDATA;
            if (defined("LIBXML_PARSEHUGE")) {
                $parserOptions = LIBXML_NOCDATA | LIBXML_PARSEHUGE;
            }

            $updateFiles = simplexml_load_string($xml, null, $parserOptions);

            foreach ($updateFiles->file as $file) {
                if ($file->type == "file") {
                    if ($file->action == "update" || $file->action == "add") {
                        $newFile = $filesDir . "/" . $file->id . "-" . $file->revision;
                        File::put($newFile, base64_decode((string) $file->content));
                    }

                    $db->insert(self::$tmpTable, array(
                        "id" => $file->id,
                        "revision" => $revision,
                        "path" => (string) $file->path,
                        "action" => (string)$file->action
                    ));
                } elseif ($file->type == "script") {
                    $newScript = $scriptsDir. $file->path;
                    File::put($newScript, base64_decode((string) $file->content));
                }
            }
        }
    }

    /**
     * @param $revision
     */
    public static function installData($revision)
    {
        $db = Db::get();
        $files = $db->fetchAll("SELECT * FROM `" . self::$tmpTable . "` WHERE revision = ?", $revision);

        foreach ($files as $file) {
            if ($file["action"] == "update" || $file["action"] == "add") {
                if (!is_dir(dirname(PIMCORE_DOCUMENT_ROOT . $file["path"]))) {
                    if (!self::$dryRun) {
                        File::mkdir(dirname(PIMCORE_DOCUMENT_ROOT . $file["path"]));
                    }
                }

                if (array_key_exists("id", $file) && $file["id"]) {
                    // this is the new style, see https://www.pimcore.org/issues/browse/PIMCORE-2722
                    $srcFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/update/".$revision."/files/" . $file["id"] . "-" . $file["revision"];
                } else {
                    // this is the old style, which we still have to support here, otherwise there's the risk that the
                    // running update cannot be finished
                    $srcFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/update/".$revision."/files/" . str_replace("/", "~~~", $file["path"]);
                }

                $destFile = PIMCORE_DOCUMENT_ROOT . $file["path"];

                if (!self::$dryRun) {
                    if ($file["path"] == "/composer.json") {
                        // composer.json needs some special processing
                        self::installComposerJson($srcFile, $destFile);
                    } else {
                        copy($srcFile, $destFile);
                    }
                }
            } elseif ($file["action"] == "delete") {
                if (!self::$dryRun) {
                    if (file_exists(PIMCORE_DOCUMENT_ROOT . $file["path"])) {
                        unlink(PIMCORE_DOCUMENT_ROOT . $file["path"]);
                    }

                    clearstatcache();

                    // remove also directory if its empty
                    if (count(glob(dirname(PIMCORE_DOCUMENT_ROOT . $file["path"]) . "/*")) === 0) {
                        recursiveDelete(dirname(PIMCORE_DOCUMENT_ROOT . $file["path"]), true);
                    }
                }
            }
        }

        self::clearOPCaches();
    }

    /**
     * @param $revision
     * @param $type
     * @return array
     */
    public static function executeScript($revision, $type)
    {
        $script = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/update/".$revision . "/scripts/" . $type . ".php";

        $maxExecutionTime = 900;
        @ini_set("max_execution_time", $maxExecutionTime);
        set_time_limit($maxExecutionTime);

        Cache::disable(); // it's important to disable the cache here eg. db-schemas, ...

        if (is_file($script)) {
            ob_start();
            try {
                if (!self::$dryRun) {
                    include($script);
                }
            } catch (\Exception $e) {
                \Logger::error($e);
            }
            $outputMessage = ob_get_clean();
        }

        self::clearOPCaches();

        return array(
            "message" => $outputMessage,
            "success" => true
        );
    }

    /**
     * @param $newFile
     * @param $oldFile
     */
    public static function installComposerJson($newFile, $oldFile)
    {
        $existingContents = file_get_contents($oldFile);
        $newContents = file_get_contents($newFile);

        $existingContents = json_decode($existingContents, true);
        $newContents = json_decode($newContents, true);

        if ($existingContents && $newContents) {
            $mergeResult = array_replace_recursive($existingContents, $newContents);
            $newJson = json_encode($mergeResult);
            $newJson = \Zend_Json::prettyPrint($newJson);
            File::put($oldFile, $newJson);
        }
    }

    /**
     *
     */
    public static function clearOPCaches()
    {
        if (function_exists("opcache_reset")) {
            opcache_reset();
        }
    }

    /**
     *
     */
    public static function cleanup()
    {

        // remove database tmp table
        $db = Db::get();
        $db->query("DROP TABLE IF EXISTS `" . self::$tmpTable . "`");

        //delete tmp data
        recursiveDelete(PIMCORE_SYSTEM_TEMP_DIRECTORY . "/update", true);
    }

    public static function updateMaxmindDb()
    {
        $downloadUrl = "http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz";
        $geoDbFile = PIMCORE_CONFIGURATION_DIRECTORY . "/GeoLite2-City.mmdb";
        $geoDbFileGz = $geoDbFile . ".gz";

        $firstTuesdayOfMonth = strtotime(date("F") . " 2013 tuesday");
        $filemtime = 0;
        if (file_exists($geoDbFile)) {
            $filemtime = filemtime($geoDbFile);
        }

        // update if file is older than 30 days, or if it is the first tuesday of the month
        if ($filemtime < (time()-30*86400) || (date("m/d/Y") == date("m/d/Y", $firstTuesdayOfMonth) && $filemtime < time()-86400)) {
            $data = Tool::getHttpData($downloadUrl);
            if (strlen($data) > 1000000) {
                File::put($geoDbFileGz, $data);

                @unlink($geoDbFile);

                $sfp = gzopen($geoDbFileGz, "rb");
                $fp = fopen($geoDbFile, "w");

                while ($string = gzread($sfp, 4096)) {
                    fwrite($fp, $string, strlen($string));
                }
                gzclose($sfp);
                fclose($fp);

                unlink($geoDbFileGz);

                \Logger::info("Updated MaxMind GeoIP2 Database in: " . $geoDbFile);
            } else {
                \Logger::err("Failed to update MaxMind GeoIP2, size is under about 1M");
            }
        } else {
            \Logger::debug("MayMind GeoIP2 Download skipped, everything up to date, last update: " . date("m/d/Y H:i", $filemtime));
        }
    }
}
