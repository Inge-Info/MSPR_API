<?php
declare(strict_types = 1);

namespace Enums;

/**
 * Action enum for logs
 */
enum Action {
    case Add;
    case Edit;
    case Remove;
    case EmailSend;
}