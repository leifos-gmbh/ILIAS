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

namespace ILIAS\Help\GuidedTour\Step;

use ilDBInterface;
use ILIAS\Help\GuidedTour\InternalDataService;

class StepDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function create(Step $step): int
    {
        $id = $this->db->nextId('help_gt_step');
        $this->db->insert('help_gt_step', [
            'id' => ['integer', $id],
            'tour_id' => ['integer', $step->getTourId()],
            'order_nr' => ['integer', $step->getOrderNr()],
            'type' => ['integer', $step->getType()->value],
            'element_id' => ['text', $step->getElementId()]
        ]);
        return $id;
    }

    public function update(Step $step): void
    {
        $this->db->update('help_gt_step', [
            'tour_id' => ['integer', $step->getTourId()],
            'order_nr' => ['integer', $step->getOrderNr()],
            'type' => ['integer', $step->getType()->value],
            'element_id' => ['text', $step->getElementId()]
        ], [
            'id' => ['integer', $step->getId()]
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM help_gt_step WHERE id = %s',
            ['integer'],
            [$id]
        );
    }

    public function getById(int $id): ?Step
    {
        $set = $this->db->queryF(
            'SELECT * FROM help_gt_step WHERE id = %s',
            ['integer'],
            [$id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record === false) {
            return null;
        }

        return $this->mapRecordToStep($record);
    }

    /**
     * @return \Generator<Step>
     */
    public function getAll(): \Generator
    {
        $set = $this->db->query('SELECT * FROM help_gt_step');
        while ($record = $this->db->fetchAssoc($set)) {
            yield $this->mapRecordToStep($record);
        }
    }

    protected function mapRecordToStep(array $record): Step
    {
        return $this->data->step(
            (int) $record['id'],
            (int) $record['tour_id'],
            (int) $record['order_nr'],
            StepType::from((int) $record['type']),
            (string) $record['element_id']
        );
    }
}