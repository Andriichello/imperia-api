<?php

namespace App\Exceptions;

use Exception;

/**
 * Class VersionNotApplied.
 *
 * A scheduled version can't be applied, for a reason the admin can read
 * (e.g. a changed item doesn't exist anymore).
 */
class VersionNotApplied extends Exception
{
}
