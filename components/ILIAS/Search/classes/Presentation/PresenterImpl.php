<?php

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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Search\Presentation;

use ILIAS\DI\Container;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ILIAS\Search\Presentation\Result\ResultPresenterImpl;
use ILIAS\Search\Presentation\Result\ComponentFactoryImpl;
use ILIAS\Search\Presentation\Result\ObjectPropertiesAggregatorImpl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Search\Presentation\Result\Subitem\SubitemPropertiesAggregatorImpl;

class PresenterImpl implements Presenter
{
    protected ResultPresenter $result_presenter;

    public function __construct(
        protected Container $dic
    ) {
    }

    public function result(): ResultPresenter
    {
        $lng = $this->dic->language();
        $lng->loadLanguageModule('search');
        return $this->result_presenter ??= new ResultPresenterImpl(
            new ComponentFactoryImpl(
                $this->dic->ui()->factory(),
                $lng
            ),
            new ObjectPropertiesAggregatorImpl(
                $this->dic['objDefinition'],
                $lng,
                $this->dic['static_url'],
                new DataFactory()
            ),
            new SubitemPropertiesAggregatorImpl(
                $this->dic
            )
        );
    }
}
