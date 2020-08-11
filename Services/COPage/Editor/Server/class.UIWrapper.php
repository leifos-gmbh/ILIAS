<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class UIWrapper
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(\ILIAS\DI\UIServices $ui, \ilLanguage $lng)
    {
        $this->ui = $ui;
        $this->lng = $lng;
    }

    /**
     * Get multi button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getButton(
        string $content,
        string $type,
        string $action,
        array $data = null): \ILIAS\UI\Component\Button\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $b = $f->button()->standard($content, "");
        if ($data === null) {
            $data = [];
        }
        $b = $b->withOnLoadCode(
            function ($id) use ($type, $data, $action) {
                $code = "document.querySelector('#$id').setAttribute('data-copg-ed-type', '$type');
                         document.querySelector('#$id').setAttribute('data-copg-ed-action', '$action')";
                foreach ($data as $key => $val) {
                    $code .= "\n document.querySelector('#$id').setAttribute('data-copg-ed-par-$key', '$val');";
                }
                return $code;
            }
        );
        return $b;
    }

    /**
     * Get rendered button
     * @param string     $content
     * @param string     $type
     * @param string     $action
     * @param array|null $data
     * @return string
     */
    public function getRenderedButton(string $content, string $type, string $action, array $data = null): string
    {
        $ui = $this->ui;
        $b = $this->getButton($content, $type, $action, $data);
        return $ui->renderer()->renderAsync($b);
    }

}