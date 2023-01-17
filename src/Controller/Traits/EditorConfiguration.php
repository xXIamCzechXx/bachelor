<?php

namespace App\Controller\Traits;

trait EditorConfiguration
{
    // Returns default module value, if not set in called method
    protected function getModuleName($module = MODULE_UNDEFIED)
    {
        return $module;
    }

    // Returns alert
    protected function getAlert($message = null) {
        return $message;
    }
}
