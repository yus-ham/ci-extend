<?php

class MY_Log extends CI_Log
{
    function _format_line($level, $date, $message) {
        return str_pad("[$level]", 7) . ' ' . $date . ' --- ' . $message . PHP_EOL;
    }

}
