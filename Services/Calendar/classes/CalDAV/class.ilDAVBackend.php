<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\CalDAV\Backend;

use Sabre\CalDAV\Backend\PDO;

class ilDAVBackend extends PDO
{
    private \ilLogger $logger;

    public function __construct(\PDO $pdo)
    {
        global $DIC;

        $this->logger = $DIC->logger()->cal();
        parent::__construct($pdo);
    }

    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
    {
        $this->logger->warning('CalendarID: ' . $calendarId);
        $this->logger->warning('syncToken: ' . $syncToken);
        $this->logger->warning('syncLevel: ' . $syncLevel);
        $this->logger->warning('limit: ' . $limit);

        return parent::getChangesForCalendar(
            $calendarId,
            $syncToken,
            $syncLevel,
            $limit
        );
    }

    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $return = parent::createCalendarObject(
            $calendarId,
            $objectUri,
            $calendarData
        );
        $category_id = \ilCalendarEntry::DAV_CALENDAR_IDS[0];
        $parser = new \ilICalParser($calendarData, \ilICalParser::INPUT_STRING);
        $parser->setCategoryId($category_id);
        $parser->parse();

        $extraData = $this->getDenormalizedData($calendarData);

        if (
            ($extraData['uid'] ?? '') &&
            $parser->getCalendarEntry() instanceof \ilCalendarEntry
        ) {
            $settings = new \ilSetting('caldav');
            $settings->set((string) ($extraData['uid'] ?? ''), (string) $parser->getCalendarEntry()->getEntryId());
        }
        return $return;
    }

    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $return = parent::updateCalendarObject(
            $calendarId,
            $objectUri,
            $calendarData
        );

        $extraData = $this->getDenormalizedData($calendarData);
        $uid = $extraData['uid'] ?? '';
        if ($uid === '') {
            $this->logger->warning('No uid found');
            return $return;
        }

        $settings = new \ilSetting('caldav', true);
        $settings->read();
        $entry_id = $settings->get($uid, '');
        $this->logger->warning('uid: ' . $uid);
        $this->logger->warning('entry_id: ' . $entry_id);
        if ($entry_id == '') {
            $this->logger->warning('No entry_id found');
            return $return;
        }
        $entry = new \ilCalendarEntry((int) $entry_id);
        $category_id = \ilCalendarEntry::DAV_CALENDAR_IDS[0];
        $parser = new \ilICalParser($calendarData, \ilICalParser::INPUT_STRING);
        $parser->setCategoryId($category_id);
        $parser->setCalendarEntry($entry);
        $parser->parse();
        return $return;
    }

    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $return = parent::deleteCalendarObject(
            $calendarId,
            $objectUri
        );

        $settings = new \ilSetting('caldav');
        $entry_id = $settings->get(substr($objectUri, 0, -4), '');
        $settings->delete(substr($objectUri, 0, -4));
        if ($entry_id == '') {
            $this->logger->warning('Cannot find calendar entry id');
        }
        $entry = new \ilCalendarEntry((int) $entry_id);
        $entry->delete();

        return $return;
    }
}
