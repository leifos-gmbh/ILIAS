<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Chart data scatter series
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilNewChartDataScatter extends ilNewChartData
{
    /**
     * @return string
     */
    protected function getTypeString()
    {
        return "scatter";
    }

    /**
     * @param array $a_data
     */
    public function parseData(array &$a_data)
    {
        parent::parseData($a_data);

        foreach ($a_data as $i => $data) {
            $series = $a_data[$i];
            $series->showLine = true;
            $series->fill = false;
            $series->lineTension = 0;
            $a_data[$i] = $series;
        }


    }
}
