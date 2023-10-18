<?php

namespace ImportHandler\Parser\Path;

use ImportHandler\I\Parser\Path\ilComparisonInterface;

class ilComparisonDummy implements ilComparisonInterface
{
    public function toString()
    {
        return '';
    }
}
