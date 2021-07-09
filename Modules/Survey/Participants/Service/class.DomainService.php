<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Participants;

/**
 * Participants domain service
 * @author killing@leifos.de
 */
class DomainService
{
    public function __construct()
    {
    }

    public function invitations() : InvitationsManager
    {
        return new InvitationsManager();
    }

}