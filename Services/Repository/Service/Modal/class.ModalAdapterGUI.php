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

namespace ILIAS\Repository\Modal;

use ILIAS\UI\Component\Modal;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ModalAdapterGUI
{
    protected ?\ILIAS\Repository\Form\FormAdapterGUI $form = null;
    protected \ILIAS\Refinery\Factory $refinery;
    protected string $title = "";
    protected \ILIAS\HTTP\Services $http;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected ?array $ui_content = null;

    /**
     * @param string|array $class_path
     */
    public function __construct(
        $title = ""
    ) {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->title = $title;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function _send(string $output) : void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function legacy(string $content) : self
    {
        $this->ui_content = [$this->ui->factory()->legacy($content)];
        $this->form = null;
        return $this;
    }

    public function form(\ILIAS\Repository\Form\FormAdapterGUI $form) : self
    {
        $this->form = $form;
        $this->ui_content = null;
        return $this;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function send() : void
    {
        $modal = [];
        if (!is_null($this->form)) {
            $this->ui_content = [$this->ui->factory()->legacy($this->form->render())];
        }
        if (!is_null($this->ui_content)) {
            $modal = $this->ui->factory()->modal()->roundtrip($this->getTitle(), $this->ui_content);
        }
        $this->_send($this->ui->renderer()->renderAsync($modal));
    }

    public function renderAsyncButton(string $button_title, string $url) : string
    {
        $ui = $this->ui;
        $modal = $ui->factory()->modal()->roundtrip("", $ui->factory()->legacy(""));
        $url .= '&replaceSignal=' . $modal->getReplaceSignal()->getId();
        $modal = $modal->withAsyncRenderUrl($url);
        $button = $ui->factory()->button()->shy($this->title, "#")
                     ->withOnClick($modal->getShowSignal());
        return $ui->renderer()->render([$button, $modal]);
    }
}
