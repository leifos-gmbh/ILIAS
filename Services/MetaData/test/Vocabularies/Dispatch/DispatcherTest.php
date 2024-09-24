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

namespace ILIAS\MetaData\Vocabularies\Dispatch;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\NullInfos;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepo;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Copyright\NullBridge as NullCopyrightBridge;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\MetaData\Vocabularies\Type;

class DispatcherTest extends TestCase
{
    protected function getVocabulary(
        bool $active,
        string $id = '',
        Type $type = Type::NULL
    ): VocabularyInterface {
        return new class ($active, $type, $id) extends NullVocabulary {
            public function __construct(
                protected bool $active,
                protected Type $type,
                protected string $id
            ) {
            }

            public function isActive(): bool
            {
                return $this->active;
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function id(): string
            {
                return $this->id;
            }
        };
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabsWithIDs(bool $active, string ...$ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->getVocabulary($active, $id);
        }
        return $result;
    }

    protected function getInfos(bool $can_be_deleted = true): InfosInterface
    {
        return new class ($can_be_deleted) extends NullInfos {
            public function __construct(protected bool $can_be_deleted)
            {
            }

            public function canBeDeleted(VocabularyInterface $vocabulary): bool
            {
                return $this->can_be_deleted;
            }
        };
    }

    protected function getCopyrightBridge(?VocabularyInterface $vocabulary = null): CopyrightBridge
    {
        return new class ($vocabulary) extends NullCopyrightBridge {
            public function __construct(protected ?VocabularyInterface $vocabulary)
            {
            }

            public function vocabulary(SlotIdentifier $slot): ?VocabularyInterface
            {
                return $this->vocabulary;
            }
        };
    }

    protected function getControlledRepo(VocabularyInterface ...$vocabularies): ControlledRepo
    {
        return new class ($vocabularies) extends NullControlledRepo {
            public array $exposed_deletions = [];

            public function __construct(protected array $vocabularies)
            {
            }

            public function getVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
            {
                yield from $this->vocabularies;
            }

            public function getActiveVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if (!$vocabulary->isActive()) {
                        continue;
                    }
                    yield $vocabulary;
                }
            }

            public function deleteVocabulary(string $vocab_id): void
            {
                $this->exposed_deletions[] = $vocab_id;
            }
        };
    }

    protected function getStandardRepo(VocabularyInterface ...$vocabularies): StandardRepo
    {
        return new class ($vocabularies) extends NullStandardRepo {
            public function __construct(protected array $vocabularies)
            {
            }

            public function getVocabularies(SlotIdentifier ...$slots): \Generator
            {
                yield from $this->vocabularies;
            }

            public function getActiveVocabularies(SlotIdentifier ...$slots): \Generator
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if (!$vocabulary->isActive()) {
                        continue;
                    }
                    yield $vocabulary;
                }
            }
        };
    }

    /**
     * @param string[] $ids
     * @param VocabularyInterface[] $vocabs
     */
    protected function assertVocabIDsMatch(array $ids, \Generator $vocabs): void
    {
        $actual_ids = [];
        foreach ($vocabs as $vocabulary) {
            $actual_ids[] = $vocabulary->id();
        }
        $this->assertSame($ids, $actual_ids);
    }

    public function testVocabulary(): void
    {
    }

    public function testVocabulariesForSlots(): void
    {
        $cp_vocab = $this->getVocabulary(true, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'cp',
                'contr active 1',
                'contr active 2',
                'contr inactive 1',
                'contr inactive 2',
                'stand active 1',
                'stand active 2',
                'stand inactive 1',
                'stand inactive 2'
            ],
            $dispatcher->vocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testVocabulariesForSlotsNoCopyright(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'contr inactive 1',
                'contr inactive 2',
                'stand active 1',
                'stand active 2',
                'stand inactive 1',
                'stand inactive 2'
            ],
            $dispatcher->vocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlots(): void
    {
        $cp_vocab = $this->getVocabulary(true, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'cp',
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $dispatcher->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlotsNoCopyright(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $dispatcher->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlotsCopyrightVocabInactive(): void
    {
        $cp_vocab = $this->getVocabulary(false, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $dispatcher->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testDeleteCannotBeDeletedException(): void
    {
        $dispatcher = new Dispatcher(
            $this->getInfos(false),
            $this->getCopyrightBridge(),
            $this->getControlledRepo(),
            $this->getStandardRepo()
        );

        $this->expectException(\ilMDVocabulariesException::class);
        $dispatcher->delete($this->getVocabulary(true));
    }

    public function testDeleteStandard(): void
    {
        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $controlled = $this->getControlledRepo(),
            $this->getStandardRepo()
        );

        $dispatcher->delete($this->getVocabulary(true, 'some id', Type::STANDARD, ));

        $this->assertEmpty($controlled->exposed_deletions);
    }

    public function testDeleteControlledString(): void
    {
        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $controlled = $this->getControlledRepo(),
            $this->getStandardRepo()
        );

        $dispatcher->delete($this->getVocabulary(true, 'some id', Type::CONTROLLED_STRING));

        $this->assertSame(
            ['some id'],
            $controlled->exposed_deletions
        );
    }

    public function testDeleteControlledVocabValue(): void
    {
        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $controlled = $this->getControlledRepo(),
            $this->getStandardRepo()
        );

        $dispatcher->delete($this->getVocabulary(true, 'some id', Type::CONTROLLED_VOCAB_VALUE));

        $this->assertSame(
            ['some id'],
            $controlled->exposed_deletions
        );
    }

    public function testDeleteCopyright(): void
    {
        $dispatcher = new Dispatcher(
            $this->getInfos(),
            $this->getCopyrightBridge(),
            $controlled = $this->getControlledRepo(),
            $this->getStandardRepo()
        );

        $dispatcher->delete($this->getVocabulary(true, 'some id', Type::COPYRIGHT));

        $this->assertEmpty($controlled->exposed_deletions);
    }
}
