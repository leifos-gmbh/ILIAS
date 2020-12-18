<?php

/* Copyright (c) 2016 Amstutz Timon <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Deck;

use ILIAS\UI\Component\Deck as D;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Deck implements D\Deck
{
    use ComponentHelper;

    /**
     * @var \ILIAS\UI\Component\Card\Card[]
     */
    protected $cards;

    /**
     * @var int
     */
    protected $size;
    /**
     * Deck constructor.
     * @param $cards
     * @param $size
     */
    public function __construct($cards, $size)
    {
        $classes = [\ILIAS\UI\Component\Card\Card::class];
        $this->checkArgListElements("cards", $cards, $classes);
        $this->checkArgIsElement("size", $size, self::$sizes, "size type");

        $this->cards = $cards;
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function withCards($cards)
    {
        $classes = [\ILIAS\UI\Component\Card\Card::class];
        $this->checkArgListElements("sections", $cards, $classes);

        $clone = clone $this;
        $clone->cards = $cards;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @inheritdoc
     */
    public function withExtraSmallCardsSize()
    {
        return $this->withCardsSize(self::SIZE_XS);
    }

    /**
     * @inheritdoc
     */
    public function withSmallCardsSize()
    {
        return $this->withCardsSize(self::SIZE_S);
    }
    /**
     * @inheritdoc
     */
    public function withNormalCardsSize()
    {
        return $this->withCardsSize(self::SIZE_M);
    }

    /**
     * @inheritdoc
     */
    public function withLargeCardsSize()
    {
        return $this->withCardsSize(self::SIZE_L);
    }

    /**
     * @inheritdoc
     */
    public function withExtraLargeCardsSize()
    {
        return $this->withCardsSize(self::SIZE_XL);
    }

    /**
     * @inheritdoc
     */
    public function withFullSizedCardsSize()
    {
        return $this->withCardsSize(self::SIZE_FULL);
    }

    /***
     * @param $size
     * @return Deck
     */
    protected function withCardsSize($size)
    {
        $this->checkArgIsElement("size", $size, self::$sizes, "size type");

        $clone = clone $this;
        $clone->size = $size;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCardsSize()
    {
        return $this->size;
    }

    /**
     * @internal This function is only internal and returns the size of the cards for small displays.
     * Note that this size tells how much space the card is using. The number of cards displayed by normal screen size is 12/size.
     *
     * @return int
     */
    public function getCardsSizeSmallDisplays()
    {
        return $this->getCardsSizeForDisplaySize(self::SIZE_S);
    }

    /**
     * @internal This function is only internal and returns the size of the cards for small displays.
     * Note that this size tells how much space the card is using. The number of cards displayed by normal screen size is 12/size.
     *
     * @return int
     */
    public function getCardsSizeForDisplaySize($display_size)
    {
        $sizes = [
            self::SIZE_XS => [
                self::SIZE_XS => 4,
                self::SIZE_S => 2,
                self::SIZE_M => 2,
                self::SIZE_L => 1
            ],
            self::SIZE_S => [
                self::SIZE_XS => 6,
                self::SIZE_S => 3,
                self::SIZE_M => 3,
                self::SIZE_L => 2
            ],
            self::SIZE_M => [
                self::SIZE_XS => 12,
                self::SIZE_S => 6,
                self::SIZE_M => 4,
                self::SIZE_L => 3
            ],
            self::SIZE_L => [
                self::SIZE_XS => 12,
                self::SIZE_S => 6,
                self::SIZE_M => 6,
                self::SIZE_L => 4
            ],
            self::SIZE_XL => [
                self::SIZE_XS => 12,
                self::SIZE_S => 12,
                self::SIZE_M => 6,
                self::SIZE_L => 6
            ],
            self::SIZE_FULL => [
                self::SIZE_XS => 12,
                self::SIZE_S => 12,
                self::SIZE_M => 12,
                self::SIZE_L => 12
            ],
        ];

        return $sizes[$this->getCardsSize()][$display_size];
    }

    private static $sizes = array(self::SIZE_FULL
    , self::SIZE_XL
    , self::SIZE_L
    , self::SIZE_M
    , self::SIZE_S
    , self::SIZE_XS
    );
}
