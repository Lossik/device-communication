<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The node kind of a Query tree.
 */
enum QueryOperator
{
    /** `key` equals `value` */
    case Where;
    /** `key` is present */
    case Has;
    /** `key` is absent */
    case HasNot;
    /** `key` is less than `value` */
    case Less;
    /** `key` is greater than `value` */
    case Greater;
    /** all `operands` hold */
    case And;
    /** at least one of the `operands` holds */
    case Or;
    /** the single operand does not hold */
    case Not;
}
