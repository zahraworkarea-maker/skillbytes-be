<?php

namespace App\Enums;

enum CaseSectionItemType: string
{
    case HEADING = 'heading';
    case TEXT = 'text';
    case LIST = 'list';
    case IMAGE = 'image';
}
