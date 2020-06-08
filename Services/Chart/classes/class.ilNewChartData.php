<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract chart data series base class
 *
 * @author Thomas Famula <famula@leifos.de>
 */
abstract class ilNewChartData
{
    protected $type; // [string]
    protected $label; // [string]
    protected $data; // [array]
    protected $color;

    /**
     * Get series type
     *
     * @return string
     */
    abstract protected function getTypeString();

    /**
     * Set label
     *
     * @param string $a_label
     */
    public function setLabel($a_label)
    {
        $this->label = (string) $a_label;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set data
     *
     * @param float $a_x
     * @param float $a_y
     */
    public function addPoint($a_x, $a_y)
    {
        $this->data[] = array($a_x, $a_y);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function setColor($a_color)
    {
        $this->color = $a_color;
    }

    public function getColor()
    {
        return $this->color;
    }


    /**
     * Convert data to chart.js config
     *
     * @param array $a_data
     * @return object
     */
    public function parseData(array &$a_data)
    {
        $series = new stdClass();
        $series->label = $this->getLabel();
        $series->data = array();
        foreach ($this->getData() as $point) {
            $series->data[] = ["x" => $point[0], "y" => $point[1]];
            //$series->data[] = array($point[0], $point[1]);
        }
        //var_dump($series->data); exit;
        $series->borderColor = $this->getColor();
        $series->showLine = true; //fix?
        $series->fill = false; //fix?
        $series->lineTension = 0; //fix?

        $a_data[] = $series;
    }

}
