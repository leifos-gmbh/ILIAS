<?php

namespace ImportStatus;

enum StatusType
{
    case NONE;
    case DUMMY;
    case ZIP_SUCCESS;
    case FAILED;
}
