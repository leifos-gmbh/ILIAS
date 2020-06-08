<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Chart data scatter series
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilNewChartDataScatter extends ilNewChartData
{
    protected function getTypeString()
    {
        return "scatter";
    }

    public function parseData(array &$a_data)
    {
        parent::parseData($a_data);
    }

    public function parseGlobalOptions(stdClass $a_options, ilChart $a_chart)
    {
        /*
        $spider = new stdClass();
        $spider->active = true;

        $spider->highlight = new stdClass();
        $spider->highlight->mode = "line";

        //..............

        $spider->spiderSize = 0.7;
        $spider->lineWidth = 1;
        $spider->pointSize = 0;

        $spider->connection = new StdClass();
        $spider->connection->width = 2;

        $spider->legMin = 0.0000001;
        $spider->legMax = $a_chart->getYAxisMax();

        $a_options->series->spider = $spider;
        */
    }
}
