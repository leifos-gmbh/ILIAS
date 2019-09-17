<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Domain business logic
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalDomainService
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Booking preferences
     *
     * @param ilObjBookingPool $pool
     * @return ilBookingPreferencesManager
     */
    public function preferences(ilObjBookingPool $pool)
    {
        return new ilBookingPreferencesManager($pool);
    }

}