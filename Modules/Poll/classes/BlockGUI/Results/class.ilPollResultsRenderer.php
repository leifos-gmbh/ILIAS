<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

class ilPollResultsRenderer
{
    protected int $ref_id;

    public function __construct(
        int $ref_id
    ) {
        $this->ref_id = $ref_id;
    }

    public function render(
        ilTemplate $tpl,
        ilPollResultsHandler $results,
        int $presentation_mode
    ): void {
        if ($presentation_mode === ilObjPoll::SHOW_RESULTS_AS_PIECHART) {
            $this->renderPieChart($tpl, $results);
        } else {
            $this->renderBarChart($tpl, $results);
        }
    }

    protected function renderPieChart(
        ilTemplate $tpl,
        ilPollResultsHandler $results
    ): void {
        $chart = $this->getPieChart();
        $chart->setSize("400", "200");
        $chart->setAutoResize(true);

        $chart_data = $chart->getDataInstance();

        foreach ($results->getOrderedAnswerIds() as $id) {
            $chart_data->addPiePoint(
                (int) round($results->getAnswerPercentage($id)),
                nl2br($results->getAnswerText($id))
            );
        }

        $chart->addData($chart_data);

        $pie_legend_id = "poll_legend_" . $this->ref_id;
        $legend = $this->getLegend();
        $legend->setContainer($pie_legend_id);
        $chart->setLegend($legend);

        $tpl->setVariable("PIE_LEGEND_ID", $pie_legend_id);
        $tpl->setVariable("PIE_CHART", $chart->getHTML());
    }

    protected function renderBarChart(
        ilTemplate $tpl,
        ilPollResultsHandler $results
    ): void {
        global $DIC;
        $f = $DIC->ui()->factory();
        $df = new \ILIAS\Data\Factory();
        $r = $DIC->ui()->renderer();

        $c_dimension = $df->dimension()->cardinal();
        $dataset = $df->dataset(['Votes' => $c_dimension]);
        foreach ($results->getOrderedAnswerIds() as $id) {
            $total_votes = $results->getAnswerTotal($id);
            $tooltip = $total_votes . ' (' . round($results->getAnswerPercentage($id)) . '%)';
            $dataset = $dataset
                ->withPoint(
                    nl2br($results->getAnswerText($id)),
                    ['Votes' => $total_votes]
                )
                ->withAlternativeInformation(
                    nl2br($results->getAnswerText($id)),
                    ['Votes' => $tooltip]
                );
        }

        $bar = new ILIAS\UI\Component\Chart\Bar\BarConfig();
        $bar = $bar->withColor($df->color('#4c6586'))->withRelativeWidth(0.65);

        $chart = $f->chart()->bar()->horizontal('', $dataset)->withTitleVisible(false)->withLegendVisible(false);
        $chart = $chart->withBarConfigs(['Votes' => $bar]);

        $tpl->setVariable("KS", $r->render($chart));
    }

    protected function getLegend(): ilChartLegend
    {
        return new ilChartLegend();
    }

    protected function getPieChart(): ilChart
    {
        return ilChart::getInstanceByType(
            ilChart::TYPE_PIE,
            "poll_results_pie_" . $this->ref_id
        );
    }

    protected function getProgressBar(): ilProgressBar
    {
        return ilProgressBar::getInstance();
    }
}
