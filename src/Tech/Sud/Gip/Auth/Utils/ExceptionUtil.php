<?php
namespace Tech\Sud\Mgp\Auth\Utils;

use Exception;

class ExceptionUtil
{
    public static function getErrorInfoFromException(Exception $e)
    {
        return "StackTrace:" . (string) $e->getTraceAsString() . "\n";
    }
}
?>
