<?php

namespace ILIAS\Export;

use DateTime;
use ilDateTime;
use ilDBConstants;
use ilDBInterface;
use ilFileDelivery;
use ilFileUtils;
use ILIAS\Filesystem\Filesystem;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ilLogger;
use ilXmlExporter;
use ILIAS\ResourceStorage\Services as ilResourceStorageService;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Export\ilExportDummyStakeholder as ilExportDummyStakeholder;

class ilExportDummy
{
    protected const TABLE = 'il_exp_dummy';
    protected ilResourceStorageService $irss;
    protected ilDBInterface $db;
    protected ilExportDummyStakeholder $stakeholder;
    protected ilLogger $logger;
    protected Filesystem $tmp_filesystem;

    public function __construct()
    {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->logger = $DIC->logger()->root();
        $this->db = $DIC->database();
        $this->stakeholder = new ilExportDummyStakeholder();
        $this->tmp_filesystem = $DIC->filesystem()->temp();
    }

    public function initTable(): void
    {
        if ($this->db->tableExists(self::TABLE)) {
            return;
        }
        $this->db->createTable(
            self::TABLE,
            [
                'obj_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'collection_cid_token' => ['type' => 'text', 'length' => 64, 'notnull' => true],
                'timestamp' => ['type' => 'timestamp'],
            ]
        );
        $this->db->addPrimaryKey(self::TABLE, ['collection_cid_token']);
    }

    public function saveXML(
        int $export_holder_obj_id,
        array $infos
    ) {
        $col_id = $this->irss->collection()->id();
        $collection = $this->irss->collection()->get($col_id);
        foreach ($infos as $info) {
            $path = $info['path'];
            $xml_str = $info['xml'];
            $rid = $this->irss->manage()->stream(Streams::ofString($xml_str), $this->stakeholder, $path);
            $collection->add($rid);
        }
        $this->irss->collection()->store($collection);
        $this->storeResourceCollectionId($export_holder_obj_id, $col_id);
    }

    public function download(
        int $export_holder_obj_id,
        string $file_name
    ): void {
        # Get collection
        $cid_token = $this->lookupNewestCIdTokenForObjId($export_holder_obj_id);
        if ($cid_token === "") {
            $this->logger->debug("No colleciton found for id: " . $export_holder_obj_id);
            return;
        }
        $this->logger->debug("Found collection id token: " . $cid_token);
        $cid = $this->irss->collection()->id($cid_token);
        $collection = $this->irss->collection()->get($cid);
        # Create tmp dir
        $temp_dir = ilFileUtils::ilTempnam();
        $rel_temp_dir = basename($temp_dir);
        $download_file_name = $file_name;
        $download_dir = $temp_dir . "/" . $download_file_name;
        $rel_download_dir = $rel_temp_dir . "/" . $download_file_name;
        $this->tmp_filesystem->createDir($rel_download_dir);
        # Write xml
        foreach ($collection->getResourceIdentifications() as $rid) {
            $revision = $this->irss->manage()->getCurrentRevision($rid);
            $title = $revision->getInformation()->getTitle();
            $size = $revision->getInformation()->getSize();
            $mime = $revision->getInformation()->getMimeType();
            $file_stream_consumer = $this->irss->consume()->stream($rid);
            $this->logger->debug(sprintf("OID(%s) Title(%s) Size(%s) Mime(%s)", $export_holder_obj_id, $title, $size, $mime));
            $path = $rel_download_dir . "/" . $title;
            $this->tmp_filesystem->writeStream($path, $file_stream_consumer->getStream());
        }
        # Send zip file
        $zip_file = $temp_dir . '/' . $download_file_name . '.zip';
        ilFileUtils::zip($download_dir, $zip_file);
        ilFileDelivery::deliverFileAttached(
            $zip_file,
            ilFileUtils::getValidFilename($download_file_name . '.zip')
        );
    }

    public function debug(): void
    {
        $data = $this->lookupCollectionTokens();
        foreach ($data as $obj_id => $col_tokens) {
            foreach ($col_tokens as $col_token) {
                $col_rid = $this->irss->collection()->id($col_token);
                $collection = $this->irss->collection()->get($col_rid);
                $this->debugCollection($obj_id, $collection);
            }
        }
    }

    protected function debugCollection(int $obj_id, ResourceCollection $collection): void
    {
        foreach ($collection->getResourceIdentifications() as $rid) {
            $revision = $this->irss->manage()->getCurrentRevision($rid);
            $title = $revision->getInformation()->getTitle();
            $size = $revision->getInformation()->getSize();
            $mime = $revision->getInformation()->getMimeType();
            $this->logger->debug(sprintf("OID(%s) Title(%s) Size(%s) Mime(%s)", $obj_id, $title, $size, $mime));
            $file_stream_consumer = $this->irss->consume()->stream($rid);
            $xml_str = $file_stream_consumer->getStream()->getContents();
            $this->logger->debug($xml_str);
        }
    }

    public function clearEntries(): void
    {
        $data = $this->lookupCollectionTokens();
        foreach ($data as $obj_id => $cid_tokens) {
            foreach ($cid_tokens as $cid_token) {
                $cid = $this->irss->collection()->id($cid_token);
                $collection = $this->irss->collection()->get($cid);
                $this->irss->collection()->remove($cid, $this->stakeholder, true);
            }
        }
        $this->clearTable();
    }

    protected function lookupCollectionTokens(): array
    {
        $data = [];
        $res = $this->db->query(
            "SELECT obj_id, collection_cid_token, timestamp FROM " . $this->db->quoteIdentifier(self::TABLE)
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $obj_id = (int) $row['obj_id'];
            $collection_cid_token = $row['collection_cid_token'];
            if (!key_exists($obj_id, $data)) {
                $data[$obj_id] = [];
            }
            $data[$obj_id][] = $collection_cid_token;
        }
        return $data;
    }

    protected function lookupNewestCIdTokenForObjId(int $obj_id): string
    {
        $res = $this->db->query(
            "SELECT collection_cid_token, timestamp FROM " . $this->db->quoteIdentifier(self::TABLE)
            . " WHERE obj_id = " . $this->db->quote($obj_id, ilDBConstants::T_INTEGER)
        );
        $newest = "";
        $timestamp = null;
        while ($row = $this->db->fetchAssoc($res)) {
            $curr_timestamp = new DateTime($row['timestamp']);
            $cur_cid_token = $row['collection_cid_token'];

            if(is_null($timestamp) || $curr_timestamp >= $timestamp) {
                $timestamp = $curr_timestamp;
                $newest = $cur_cid_token;
            }
        }
        return $newest;
    }

    protected function storeResourceCollectionId(int $obj_id, ResourceCollectionIdentification $rid): void
    {
        if (!$this->db->tableExists(self::TABLE)) {
            return;
        }
        $this->db->query(
            "INSERT INTO " . $this->db->quoteIdentifier(self::TABLE) . " (obj_id, collection_cid_token, timestamp)"
            . " VALUES ("
            . $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($rid, ilDBConstants::T_TEXT) . ","
            . $this->db->quote((new DateTime('now'))->format("Y-m-d H:i:s"), ilDBConstants::T_DATETIME) . ")"
        );
    }

    protected function clearTable(): void
    {
        if($this->db->tableExists(self::TABLE)) {
            $this->db->manipulate(
                "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE)
            );
        }
    }
}
