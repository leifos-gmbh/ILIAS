<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Generator for scatter charts
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilNewChartScatter extends ilNewChart
{
    protected $ticks; // [array]
    protected $integer_axis; // [array]

    public function __construct($a_id)
    {
        parent::__construct($a_id);
    }

    /**
     * Set ticks
     *
     * @param int|array $a_x
     * @param int|array $a_y
     * @param bool $a_labeled
     */
    public function setTicks($a_x, $a_y, $a_labeled = false)
    {
        //$this->ticks = array("x" => $a_x, "y" => $a_y, "labeled" => (bool) $a_labeled);
    }

    /**
     * Get ticks
     *
     * @return array (x, y)
     */
    public function getTicks()
    {
        //return $this->ticks;
    }

    public function parseGlobalOptions(stdClass $a_options)
    {
        /*
        // axis/ticks
        $tmp = array();
        $ticks = $this->getTicks();
        if ($ticks) {
            $labeled = (bool) $ticks["labeled"];
            unset($ticks["labeled"]);
            foreach ($ticks as $axis => $def) {
                if (is_numeric($def) || is_array($def)) {
                    $a_options->{$axis . "axis"} = new stdClass();
                }
                if (is_numeric($def)) {
                    $a_options->{$axis . "axis"}->ticks = $def;
                } elseif (is_array($def)) {
                    $a_options->{$axis . "axis"}->ticks = array();
                    foreach ($def as $idx => $value) {
                        if ($labeled) {
                            $a_options->{$axis . "axis"}->ticks[] = array($idx, $value);
                        } else {
                            $a_options->{$axis . "axis"}->ticks[] = $value;
                        }
                    }
                }
            }
        }

        // optional: remove decimals
        if ($this->integer_axis["x"] && !isset($a_options->xaxis)) {
            $a_options->{"xaxis"} = new stdClass();
            $a_options->{"xaxis"}->tickDecimals = 0;
        }
        if ($this->integer_axis["y"] && !isset($a_options->yaxis)) {
            $a_options->{"yaxis"} = new stdClass();
            $a_options->{"yaxis"}->tickDecimals = 0;
        }
        */
    }
}
