<?php

use Carbon\Carbon;

if (! function_exists('format_rupiah')) {
    function format_rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('format_date_id')) {
    function format_date_id(mixed $date, string $format = 'd M Y H:i'): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->locale('id')->translatedFormat($format);
    }
}
