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

    protected $id; // [string]
    protected $data; // [array]
    protected $series;
    protected $options;

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

    public function getData1()
    {
        $data1 = ["x" => 4, "y" => 4];
        $data2 = ["x" => 3, "y" => 3];
        $data3 = ["x" => 3, "y" => 2];
        $data4 = ["x" => 4, "y" => 1];
        $data5 = ["x" => 3, "y" => 0];

        $this->series = [$data1, $data2, $data3, $data4, $data5];

        return $this->series;
    }

    public function getData2()
    {
        $data1 = ["x" => 1, "y" => 4];
        $data2 = ["x" => 0, "y" => 3];
        $data3 = ["x" => 1, "y" => 2];
        $data4 = ["x" => 2, "y" => 1];
        $data5 = ["x" => 1, "y" => 0];

        $this->series1 = [$data1, $data2, $data3,  $data5];

        return $this->series1;
    }

    public function setOptions($a_options)
    {
        $this->options = $a_options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Render
     */
    public function getHTML()
    {
        $chart = new ilTemplate("tpl.scatter.html", true, true, "Services/Chart");
        $chart->setVariable("ID", $this->id);

        /*
        $json_data = new stdClass();
        $json_data->dataset = new stdClass();
        $json_data->dataset->label = "Zielstufe";
        $json_data->dataset->data = $this->getData1();
        $json_data->dataset->showLine = true;
        $json_data->dataset->fill = false;
        $json_data->dataset->lineTension = 0;
        $json_data->dataset->borderColor = "green";

        $json_dataset = new stdClass();
        $json_dataset->label = "Zielstufe";
        $json_dataset->data = $this->getData1();
        $json_dataset->showLine = true;
        $json_dataset->fill = false;
        $json_dataset->lineTension = 0;
        $json_dataset->borderColor = "green";

        $json_dataset1 = new stdClass();
        $json_dataset1->label = "Eine Quelle";
        $json_dataset1->data = $this->getData2();
        $json_dataset1->showLine = true;
        $json_dataset1->fill = false;
        $json_dataset1->lineTension = 0;
        $json_dataset1->borderColor = "red";

        */




        // (series) data

        $json_series = array();
        //var_dump($this->data); exit;
        foreach ($this->data as $series) {
            //var_dump($series);
            $series->parseData($json_series);
        }
        //exit;

        $chart->setVariable("SERIES", json_encode($json_series));


        //options

        //$json_options = new stdClass();
       // $json_options->scales = new stdClass();
        //...

        //playground
        $arr = ["blup" => "dieser Wert", "blop" => "anderer Wert"];
        $arr1 = ["hop" => "ein Wert", $arr];

        //$encoded = json_encode($json_dataset);
        //$encoded1 = json_encode($json_dataset1);
        //$encoded_all = json_encode([$json_dataset, $json_dataset1]);

        //$chart->setVariable("SERIES", $encoded_all);





        $ret = $chart->get();
        return $ret;
    }

}
