<?php

if (!function_exists('unformat_phone')) {
    /**
     * unformat_phone
     *
     * @param  mixed $phone
     * @return mixed
     */
    function unformat_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if (strlen($phone) == 10) $phone = '+1' . $phone;
        if (strlen($phone) == 11) $phone = '+' . $phone;

        return $phone;
    }
}

if (!function_exists('format_phone')) {
    /**
     * format_phone
     *
     * @param  mixed $phone
     * @return mixed
     */
    function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if (strlen($phone) == 11) $phone = ltrim($phone, '1');
        $phone = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $phone);

        return $phone;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * format_bytes
     *
     * @param  mixed $size
     * @param  mixed $precision
     * @return mixed
     */
    function format_bytes($size, $precision = 2)
    {
        if ($size <= 0) {
            return '0 B';
        }

        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = (int) floor($base);
        $value = $size / pow(1024, $index);

        return round($value, $precision) . ' ' . $suffixes[$index];
    }
}

if (!function_exists('unslugify')) {
    /**
     * unslugify
     *
     * @param  string $slug
     * @return string
     */
    function unslugify($slug)
    {
        $slug = ucwords(strtolower(str_replace('-', ' ', trim($slug))));

        return $slug;
    }
}

if (!function_exists('slugify')) {
    /**
     * slugify
     *
     * @param  string $slug
     * @return string
     */
    function slugify($slug)
    {
        $slug = trim($slug);
        $slug = str()->of($slug)->replace(':', '_');
        $slug = str()->slug($slug);
        $slug = str()->of($slug)->replace('-', '_');

        return $slug;
    }
}

if (!function_exists('format_date')) {
    /**
     * format_date
     *
     * @param  mixed $date
     * @param  mixed $format
     * @return mixed
     */
    function format_date($date, $format)
    {
        if ($date) {
            try {
                $date = new DateTime($date);
                $date = $date->format($format);
                return $date;
            } catch (Exception $e) {
                return $date;
            }
        } else {
            return null;
        }
    }
}
