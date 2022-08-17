<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\getObjectSettingsCommand;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SlotGUI
{
    protected $from;
    protected string $to;
    protected int $from_ts;
    protected int $to_ts;
    protected string $title;
    protected int $available;
    protected string $link;

    public function __construct(
        string $link,
        string $from,
        string $to,
        int $from_ts,
        int $to_ts,
        string $title,
        int $available
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->from_ts = $from_ts;
        $this->to_ts = $to_ts;
        $this->title = $title;
        $this->available = $available;
        $this->link = $link;
    }

    public function render() : string
    {
        global $DIC;
        $ui = $DIC->ui();
        $tpl = new \ilTemplate("tpl.slot.html", true, true, "Modules/BookingManager/BookingProcess");

        $link = $ui->factory()->link()->standard($this->title, $this->link);

        $tpl->setVariable("OBJECT_LINK", $ui->renderer()->render($link));
        $tpl->setVariable("TIME", $this->from . "-" . $this->to);
        $tpl->setVariable("AVAILABILITY", "(" . $this->available . ") ");

        return $tpl->get();
    }
}
