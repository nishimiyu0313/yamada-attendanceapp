<?php
if (!function_exists('formatMinutes')) {
    function formatMinutes($minutes)
    {
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        return sprintf('%d:%02d', $h, $m);
    }
}
