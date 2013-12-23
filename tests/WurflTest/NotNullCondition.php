<?php
namespace WurflTest;

/**
* Utility Classes
*
*/
class NotNullCondition
{
    public function check($key, $value)
    {
        return empty($key) || empty($value) ? false : true;
    }
}
