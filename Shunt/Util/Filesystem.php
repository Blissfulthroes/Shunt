<?php

namespace Shunt\Util {

    defined('SHUNT_ACCESS_SECURED') or header('location: /403-Forbidden');

    class Filesystem {

        /**
     * Return the extension part from a filename
     *
     * @param string $filename
     * @param bool $dot Optional. Whether to include the '.' dot character
     * @param bool $lowercase Optional. Whether to return in all lowercase
     * @return string $file_ext Extension 
     */
    public function getExtension($filename, $dot = true, $lowercase = true) {
        $file_ext = strrchr($filename, '.');
        if ($file_ext) {
            if (!$dot) {
                $file_ext = str_replace('.', '', $file_ext);
            }
            if ($lowercase) {
                $file_ext = strtolower($file_ext);
            }
        }
        return $file_ext;
    }
    }

}