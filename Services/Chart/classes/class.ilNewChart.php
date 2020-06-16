<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract Chart generator base class
 *
 * @author Thomas Famula <famula@leifos.de>
 */
abstract class ilNewChart
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param string $a_id
     */
    public function __construct($a_id)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->id = $a_id;
        $this->data = array();
    }

    /**
     * Add data series
     *
     * @param ilNewChartData $a_series
     * @param mixed $a_idx
     * @return mixed index
     */
    public function addData(ilNewChartData $a_series, $a_idx = null)
    {
        if ($a_idx === null) {
            $a_idx = sizeof($this->data);
        }
        $this->data[$a_idx] = $a_series;
        return $a_idx;
    }

    public function parseOptions(array &$a_options)
    {
    }

    /**
     * Render
     */
    public function getHTML()
    {
        $chart = new ilTemplate("tpl.scatter.html", true, true, "Services/Chart");
        $chart->setVariable("ID", $this->id);


        // labels for y axis

        $y_index = 0;
        $y_labels = array();
        foreach ($this->getYAxisLabels() as $label) {
            $y_labels[$y_index] = $label;
            $y_index++;
        }
        $chart->setVariable("YLABELS", json_encode($y_labels));


        // labels for x axis

        $x_index = 0;
        $x_labels = array();
        foreach ($this->getXAxisLabels() as $label) {
            $x_labels[$x_index] = $label;
            $x_index++;
        }
        $chart->setVariable("XLABELS", json_encode($x_labels));


        // (series) data

        $json_series = array();
        foreach ($this->data as $series) {
            $series->parseData($json_series);
        }
        $chart->setVariable("SERIES", json_encode($json_series));


        //options

        $json_preferences = array();
        $this->parseOptions($json_preferences);
        $chart->setVariable("PREFERENCES", json_encode($json_preferences));


        $ret = $chart->get();
        return $ret;
    }

}
