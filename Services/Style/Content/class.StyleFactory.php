<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * Content style factory
 * @author Alexander Killing <killing@leifos.de>
 */
class StyleFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Characteristic
     * @param string $type
     * @param string $characteristic
     * @param bool   $hide
     * @param array  $titles
     * @param int    $style_id
     * @param int    $order_nr
     * @param bool   $deprecated
     * @return Characteristic
     */
    public function characteristic(
        string $type,
        string $characteristic,
        bool $hide,
        array $titles,
        int $style_id = 0,
        int $order_nr = 0,
        bool $deprecated = false
    ) : Characteristic
    {
        $c = new Characteristic(
            $type,
            $characteristic,
            $hide,
            $titles,
            $order_nr,
            $deprecated
        );
        if ($style_id > 0) {
            $c = $c->withStyleId($style_id);
        }
        return $c;
    }

}