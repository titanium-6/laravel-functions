<?php

if (!function_exists('cached_asset')) {
    // https://laravel-tricks.com/tricks/easier-caching-with-cached-asset
    /*
		# Redirect assets with version in name to actual filename
		<IfModule mod_rewrite.c>
			RewriteEngine On
			RewriteCond %{REQUEST_FILENAME} !-f
			RewriteRule ^(.+)\.(\d+)\.(js|css)$ $1\.$3 [L]
		</IfModule>
	*/
    /**
     * cached_asset
     *
     * @param  mixed $path
     * @param  mixed $bustQuery
     * @return mixed
     */
    function cached_asset($path, $bustQuery = false)
    {
        // Get the full path to the asset.
        $realPath = public_path($path);

        if (!file_exists($realPath)) {
            throw new LogicException("File not found at [{$realPath}]");
        }

        // Get the last updated timestamp of the file.
        $timestamp = filemtime($realPath);

        if (!$bustQuery) {
            // Get the extension of the file.
            $extension = pathinfo($realPath, PATHINFO_EXTENSION);

            // Strip the extension off of the path.
            $stripped = substr($path, 0, - (strlen($extension) + 1));

            // Put the timestamp between the filename and the extension.
            $path = implode('.', array($stripped, $timestamp, $extension));
        } else {
            // Append the timestamp to the path as a query string.
            $path  .= '?' . $timestamp;
        }

        return asset($path);
    }
}

if (!function_exists('array_to_object')) {
    /**
     * array_to_object
     *
     * @param  mixed $array
     * @return mixed
     */
    function array_to_object($array)
    {
        $object = (object) array();

        foreach ($array as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }
}

if (!function_exists('convert_array_to_object')) {
    /**
     * convert_array_to_object
     *
     * @param  mixed $array
     * @return mixed
     */
    function convert_array_to_object($array)
    {
        return json_decode(json_encode($array));
    }
}

if (!function_exists('convert_to_array')) {
    /**
     * convert_to_array
     *
     * @param  mixed $value
     * @return mixed
     */
    function convert_to_array($value)
    {
        return json_decode(json_encode($value), true);
    }
}

if (!function_exists('generate_random_hex')) {
    /**
     * generate_random_hex
     *
     * @param  mixed $strlen
     * @return mixed
     */
    function generate_random_hex($strlen = 16)
    {
        return strtoupper(implode(array_map(function () {
            return dechex(mt_rand(0, 15));
        }, array_fill(0, $strlen, null))));
    }
}

if (!function_exists('csv_to_array')) {
    /**
     * csv_to_array
     *
     * @param  mixed $filename
     * @param  mixed $trim
     * @return mixed
     */
    function csv_to_array($filename, $trim = '')
    {
        $row = 0;
        $col = 0;
        $results = [];

        $handle = @fopen($filename, "r");
        if ($handle) {
            while (($row = fgetcsv($handle, 16384, ",", '"', '"')) !== false) {
                if (empty($fields)) {
                    $fields = $row;
                    continue;
                }

                foreach ($row as $k => $value) {
                    if ($trim) $value = trim($value, $trim);

                    $results[$col][$fields[$k]] = $value;
                }
                $col++;
                unset($row);
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() failn";
            }
            fclose($handle);
        }

        return $results;
    }
}

if (!function_exists('ascii_only')) {
    /**
     * ascii_only
     *
     * @param  mixed $text
     * @return mixed
     */
    function ascii_only($text)
    {
        return preg_replace('/[[:^print:]]/', '', $text);
    }
}

if (!function_exists('is_valid_object')) {
    /**
     * is_valid_object
     *
     * @param  mixed $object
     * @param  mixed $index
     * @return mixed
     */
    function is_valid_object($object, $index = "")
    {
        if (!empty($index)) {
            if (isset($object[$index]) && !empty($object[$index]) && is_object($object[$index]) && get_class($object[$index]) != 'stdClass') {
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($object) && !empty($object) && is_object($object) && get_class($object) != 'stdClass') {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('url_exists')) {
    /**
     * url_exists
     *
     * @param  mixed $url
     * @return mixed
     */
    function url_exists($url = null)
    {
        if ($url == null) return false;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode >= 200 && $httpcode < 300) ? true : false;
    }
}

if (!function_exists('get_base64_info')) {
    /**
     * get_base64_info
     *
     * @param  mixed $base64_data
     * @return mixed
     */
    function get_base64_info($base64_data)
    {
        $image_parts = explode(";base64,", $base64_data);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        return array(
            'image_type_aux' => $image_type_aux,
            'image_type' => $image_type,
            'image_base64' => $image_base64,
        );
    }
}

if (!function_exists('age_from_date')) {
    /**
     * age_from_date
     *
     * @param  mixed $date_of_birth
     * @return mixed
     */
    function age_from_date($date_of_birth)
    {
        $today = date('Y-m-d');

        try {
            $years = date_diff(date_create($date_of_birth), date_create($today))->format('%y');
        } catch (exception $e) {
            return null;
        }

        return $years;
    }
}

if (!function_exists('convert_meters_to_miles')) {
    /**
     * convert_meters_to_miles
     *
     * @param  mixed $meters
     * @param  mixed $precision
     * @param  mixed $floor
     * @return mixed
     */
    function convert_meters_to_miles($meters, $precision = 2, $floor = false)
    {
        if ($meters === null) return null;

        $miles = $meters * 0.000621371;

        if ($floor) {
            $multiplier = pow(10, $precision);
            return floor($miles * $multiplier) / $multiplier;
        }

        return round($miles, $precision);
    }
}

if (!function_exists('convert_meters_to_feet')) {
    /**
     * convert_meters_to_feet
     *
     * @param  mixed $meters
     * @param  mixed $precision
     * @param  mixed $floor
     * @return mixed
     */
    function convert_meters_to_feet($meters, $precision = 2, $floor = false)
    {
        if ($meters === null) return null;

        $feet = $meters * 3.2808399;

        if ($floor) {
            $multiplier = pow(10, $precision);
            return floor($feet * $multiplier) / $multiplier;
        }

        return round($feet, $precision);
    }
}

if (!function_exists('convert_miles_to_meters')) {
    /**
     * convert_miles_to_meters
     *
     * @param  mixed $miles
     * @param  mixed $precision
     * @param  mixed $floor
     * @return mixed
     */
    function convert_miles_to_meters($miles, $precision = 2, $floor = false)
    {
        if ($miles === null) return null;

        $meters = $miles * 1609.344;

        if ($floor) {
            $multiplier = pow(10, $precision);
            return floor($meters * $multiplier) / $multiplier;
        }

        return round($meters, $precision);
    }
}

if (!function_exists('convert_miles_to_feet')) {
    /**
     * convert_miles_to_feet
     *
     * @param  mixed $miles
     * @param  mixed $precision
     * @param  mixed $floor
     * @return mixed
     */
    function convert_miles_to_feet($miles, $precision = 2, $floor = false)
    {
        if ($miles === null) return null;

        $feet = $miles * 5280;

        if ($floor) {
            $multiplier = pow(10, $precision);
            return floor($feet * $multiplier) / $multiplier;
        }

        return round($feet, $precision);
    }
}

if (!function_exists('adjust_brightness')) {
    /**
     * adjust_brightness
     *
     * @param  mixed $hex
     * @param  mixed $steps
     * @return mixed
     */
    function adjust_brightness($hex, $steps)
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }
}

if (!function_exists('build_alert')) {
    /**
     * build_alert
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $class
     * @return mixed
     */
    function build_alert($status, $message, $class = null)
    {
        return '<div class="alert alert-' . $status . ' ' . $class . '" data-aos="fade-down" data-aos-easing="linear" data-aos-duration="1000">' . $message . '</div>';
    }
}

if (!function_exists('get_model_count')) {
    /**
     * get_model_count
     *
     * @param  mixed $model
     * @return mixed
     */
    function get_model_count($model)
    {
        $model = 'App\\Models\\' . $model;
        $count = (new $model)->count();

        return $count;
    }
}

if (!function_exists('update_status')) {
    /**
     * update_status
     *
     * @return mixed
     */
    function update_status()
    {
        request()->validate([
            'status' => 'required',
        ]);

        $model_id = request('model_id');
        $model_type = request('model_type');
        $status = request('status');

        $model_path = '\\App\\Models\\' . $model_type;
        $model = (new $model_path)->find($model_id);

        // Update blog
        $model->update([
            'status' => $status,
        ]);

        return response()->json(['status' => true, 'message' => $model_type . ' Status Updated Successfully.'], 200);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * is_super_admin
     *
     * @return mixed
     */
    function is_super_admin()
    {
        // @phpstan-ignore-next-line
        $user_roles = request()->user()->roles->pluck('name')->toArray();

        if (in_array('Super Admin', $user_roles)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_roles')) {
    /**
     * get_roles
     *
     * @return mixed
     */
    function get_roles()
    {
        // @phpstan-ignore-next-line
        $roles = \App\Models\Role::query();

        if (!is_super_admin()) {
            $roles->where('name', '<>', 'Super Admin');
        }

        $roles = $roles->pluck('name', 'id')->toArray();

        return $roles;
    }
}

if (!function_exists('get_timezones')) {
    /**
     * get_guards
     *
     * @return mixed
     */
    function get_timezones()
    {
        $timezones = array(
            'Pacific/Midway'       => "(GMT-11:00) Midway Island",
            'US/Samoa'             => "(GMT-11:00) Samoa",
            'US/Hawaii'            => "(GMT-10:00) Hawaii",
            'US/Alaska'            => "(GMT-09:00) Alaska",
            'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
            'America/Tijuana'      => "(GMT-08:00) Tijuana",
            'US/Arizona'           => "(GMT-07:00) Arizona",
            'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
            'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
            'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
            'America/Mexico_City'  => "(GMT-06:00) Mexico City",
            'America/Monterrey'    => "(GMT-06:00) Monterrey",
            'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
            'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
            'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
            'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
            'America/Bogota'       => "(GMT-05:00) Bogota",
            'America/Lima'         => "(GMT-05:00) Lima",
            'America/Caracas'      => "(GMT-04:30) Caracas",
            'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
            'America/La_Paz'       => "(GMT-04:00) La Paz",
            'America/Santiago'     => "(GMT-04:00) Santiago",
            'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
            'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
            'Greenland'            => "(GMT-03:00) Greenland",
            'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
            'Atlantic/Azores'      => "(GMT-01:00) Azores",
            'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
            'Africa/Casablanca'    => "(GMT) Casablanca",
            'Europe/Dublin'        => "(GMT) Dublin",
            'Europe/Lisbon'        => "(GMT) Lisbon",
            'Europe/London'        => "(GMT) London",
            'Africa/Monrovia'      => "(GMT) Monrovia",
            'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
            'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
            'Europe/Berlin'        => "(GMT+01:00) Berlin",
            'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
            'Europe/Brussels'      => "(GMT+01:00) Brussels",
            'Europe/Budapest'      => "(GMT+01:00) Budapest",
            'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
            'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
            'Europe/Madrid'        => "(GMT+01:00) Madrid",
            'Europe/Paris'         => "(GMT+01:00) Paris",
            'Europe/Prague'        => "(GMT+01:00) Prague",
            'Europe/Rome'          => "(GMT+01:00) Rome",
            'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
            'Europe/Skopje'        => "(GMT+01:00) Skopje",
            'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
            'Europe/Vienna'        => "(GMT+01:00) Vienna",
            'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
            'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
            'Europe/Athens'        => "(GMT+02:00) Athens",
            'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
            'Africa/Cairo'         => "(GMT+02:00) Cairo",
            'Africa/Harare'        => "(GMT+02:00) Harare",
            'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
            'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
            'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
            'Europe/Kiev'          => "(GMT+02:00) Kyiv",
            'Europe/Minsk'         => "(GMT+02:00) Minsk",
            'Europe/Riga'          => "(GMT+02:00) Riga",
            'Europe/Sofia'         => "(GMT+02:00) Sofia",
            'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
            'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
            'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
            'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
            'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
            'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
            'Europe/Moscow'        => "(GMT+03:00) Moscow",
            'Asia/Tehran'          => "(GMT+03:30) Tehran",
            'Asia/Baku'            => "(GMT+04:00) Baku",
            'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
            'Asia/Muscat'          => "(GMT+04:00) Muscat",
            'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
            'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
            'Asia/Kabul'           => "(GMT+04:30) Kabul",
            'Asia/Karachi'         => "(GMT+05:00) Karachi",
            'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
            'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
            'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
            'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
            'Asia/Almaty'          => "(GMT+06:00) Almaty",
            'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
            'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
            'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
            'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
            'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
            'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
            'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
            'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
            'Australia/Perth'      => "(GMT+08:00) Perth",
            'Asia/Singapore'       => "(GMT+08:00) Singapore",
            'Asia/Taipei'          => "(GMT+08:00) Taipei",
            'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
            'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
            'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
            'Asia/Seoul'           => "(GMT+09:00) Seoul",
            'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
            'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
            'Australia/Darwin'     => "(GMT+09:30) Darwin",
            'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
            'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
            'Australia/Canberra'   => "(GMT+10:00) Canberra",
            'Pacific/Guam'         => "(GMT+10:00) Guam",
            'Australia/Hobart'     => "(GMT+10:00) Hobart",
            'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
            'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
            'Australia/Sydney'     => "(GMT+10:00) Sydney",
            'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
            'Asia/Magadan'         => "(GMT+12:00) Magadan",
            'Pacific/Auckland'     => "(GMT+12:00) Auckland",
            'Pacific/Fiji'         => "(GMT+12:00) Fiji",
        );

        return $timezones;
    }
}

if (!function_exists('create_password')) {
    /**
     * create_password
     *
     * @param  mixed $deprecrated
     * @return mixed
     */
    function create_password($deprecrated = null)
    {
        return (new \SgtCoder\LaravelFunctions\Services\PasswordService)->password(16);
    }
}

if (!function_exists('generate_new_token')) {
    /**
     * generate_new_token
     *
     * @return mixed
     */
    function generate_new_token()
    {
        return (new \SgtCoder\LaravelFunctions\Services\PasswordService)->hex(16);
    }
}

if (!function_exists('generateNewToken')) {
    /**
     * generateNewToken
     *
     * @return mixed
     */
    function generateNewToken()
    {
        return generate_new_token();
    }
}

if (!function_exists('generate_mac_address')) {
    /**
     * generate_mac_address
     *
     * @return mixed
     */
    function generate_mac_address($qty = 1, $html = false)
    {
        return (new \SgtCoder\LaravelFunctions\Services\PasswordService)->generate_mac_address($qty, $html);
    }
}

if (!function_exists('content_for')) {
    /**
     * content_for
     *
     * @param  mixed $section
     * @return mixed
     */
    function content_for($section)
    {
        return array_key_exists($section, app('view')->getSections());
    }
}

if (!function_exists('download_datatable')) {
    /**
     * download_datatable
     *
     * @param  mixed $data
     * @param  mixed $model_name
     * @param  mixed $additional_columns
     * @param  mixed $remove_columns
     * @param  mixed $model_only
     * @return mixed
     */
    function download_datatable($data, $model_name, $additional_columns = [], $remove_columns = [], $model_only = false)
    {
        $model = '\App\Models\\' . $model_name;
        $table_name = app($model)->getTable();

        if ($model_only) {
            $columns = app($model)->getFillable();
        } else {
            $columns = collect(request('columns'))->pluck('data')->toArray();
        }

        $remove_columns = array_merge($remove_columns, ['actions']);

        $columns = array_merge($columns, $additional_columns);
        $columns = array_diff($columns, $remove_columns);

        $filename = $table_name . '_' . now()->format('Y-m-d') . '.csv';

        // Reject
        $disable_models = ['User'];
        if (in_array($model_name, $disable_models)) {
            throw new \Illuminate\Auth\Access\AuthorizationException;
        }

        request()->merge(['length' => -1]);
        $data = $data->toJson()->getData(true)['data'];

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                $data_item = [];

                foreach ($columns as $column) {
                    $data_item[$column] = strip_tags($item[$column]);
                }

                fputcsv($file, $data_item);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

if (!function_exists('strip_https')) {
    /**
     * strip_https
     *
     * @param  string $url
     * @return string
     */
    function strip_https($url)
    {
        return preg_replace('#^https?://#', '', rtrim($url, '/'));
    }
}

if (!function_exists('icmp_ping')) {
    /**
     * icmp_ping
     *
     * @param  string $ip
     * @param  integer $count
     * @param  integer $interval
     * @return boolean
     */
    function icmp_ping($ip, $count = 4, $interval = 1)
    {
        $process = \Symfony\Component\Process\Process::fromShellCommandline('ping ' . $ip . ' -c ' . $count . ' -i ' . $interval);
        $process->setTimeout(28800);
        $process->disableOutput();
        $process->run();

        if (!$process->isSuccessful()) {
            return false;
        }

        return true;
    }
}

if (!function_exists('icmp_ping_batch')) {
    /**
     * icmp_ping_batch
     *
     * @param  mixed $ips
     * @param  integer $count
     * @param  integer $interval
     * @param  integer $pool_size
     * @param  string $signature
     * @return void
     */
    function icmp_ping_batch($ips, $count = 4, $interval = 1, $pool_size = 4, $signature = null)
    {
        if (!is_array($ips)) {
            $ips = [$ips];
        }

        collect($ips)
            ->chunk($pool_size)
            ->each(function ($batch, $batch_index) use ($count, $interval, $signature) {
                $results = \Illuminate\Support\Facades\Process::concurrently(function (\Illuminate\Process\Pool $pool) use ($batch, $count, $interval) {
                    $batch->each(function ($ip) use ($pool, $count, $interval) {
                        $pool->command(['ping', '-c', (string) $count, '-i', (string) $interval, (string) $ip])->timeout(28800);
                    });
                });

                $results->collect()->each(function ($process, $index) use ($batch, $signature) {
                    $ip = $batch->values()[$index];
                    $result = $process->successful();

                    console_log($signature, $result ? 'SUCCESS' : 'ERROR', $ip);
                });
            });
    }
}

if (!function_exists('make_directory')) {
    /**
     * make_directory
     *
     * @param  string $path
     * @return boolean
     */
    function make_directory($path)
    {
        if (!Illuminate\Support\Facades\File::exists($path)) {
            Illuminate\Support\Facades\File::makeDirectory($path);
        }

        return true;
    }
}

if (!function_exists('send_laravel_email')) {
    /**
     * send_laravel_email
     *
     * @param string|array $to_emails
     * @return mixed
     */
    function send_laravel_email($to_emails)
    {
        $laravel_email = \SgtCoder\LaravelFunctions\LaravelEmail::send_laravel_email($to_emails);

        return $laravel_email;
    }
}

if (!function_exists('is_livewire_redirect')) {
    /**
     * is_livewire_redirect
     *
     * @param  mixed $redirect_url
     * @return mixed
     */
    function is_livewire_redirect($redirect_url)
    {
        return request()->getHttpHost() == parse_url($redirect_url, PHP_URL_HOST);
    }
}

if (!function_exists('getResourceRoutesForNameHelper')) {
    /**
     * getResourceRoutesForNameHelper
     *
     * @param  mixed $name
     * @return mixed
     */
    function getResourceRoutesForNameHelper($name)
    {
        return [
            'index' => $name . ".index",
            'create' => $name . ".create",
            'store' => $name . ".store",
            'show' => $name . ".show",
            'edit' => $name . ".edit",
            'update' => $name . ".update",
            'destroy' => $name . ".destroy",
        ];
    }
}

if (!function_exists('get_reading_time')) {
    /**
     * get_reading_time
     *
     * @param  mixed $length
     * @param  mixed $type
     * @return mixed
     */
    function get_reading_time($length, $type = 'words')
    {
        if (empty($length)) return null;

        if ($type == 'words') {
            $words_per_minute_low = 100;
            $words_per_minute_high = 260;

            $reading_slow = ceil($length / $words_per_minute_low);
            $reading_fast = ceil($length / $words_per_minute_high);

            return $reading_fast . '-' . $reading_slow;
        } else {
            $cpm = 987;
            $variance = 118;
            $charactersPerMinuteLow = $cpm - $variance;
            $charactersPerMinuteHigh = $cpm + $variance;

            $readingTimeMinsSlow = ceil($length / $charactersPerMinuteLow);
            $readingTimeMinsFast = ceil($length / $charactersPerMinuteHigh);

            return $readingTimeMinsFast . '-' . $readingTimeMinsSlow;
        }
    }
}

if (!function_exists('days_ago')) {
    /**
     * days_ago
     *
     * @param  mixed $date
     * @param  mixed $timezone
     * @return mixed
     */
    function days_ago($date, $timezone = null)
    {
        $date = now()->parse($date);

        if ($timezone) {
            $date = $date->timezone($timezone);
        }

        return (int)$date->diffInDays();
    }
}

if (!function_exists('format_date_carbon')) {
    /**
     * format_date_carbon
     *
     * @param  mixed $date
     * @param  mixed $format
     * @param  mixed $timezone
     * @return mixed
     */
    function format_date_carbon($date, $format = 'm/d/y', $timezone = null)
    {
        $time_ago = is_bool($format) ? $format : false;

        $date = now()->parse($date);

        if ($timezone) {
            $date = $date->timezone($timezone);
        }

        if ($time_ago) {
            $date = $date->diffForHumans();
        } else {
            $date = $date->format($format);
        }

        return $date;
    }
}

if (!function_exists('get_months')) {
    /**
     * get_months
     *
     * @param  mixed $month
     * @param  mixed $shorthand
     * @return mixed
     */
    function get_months($month = null, $shorthand = false)
    {
        if ($shorthand) {
            $months = [
                '1' => 'Jan',
                '2' => 'Feb',
                '3' => 'Mar',
                '4' => 'Apr',
                '5' => 'May',
                '6' => 'Jun',
                '7' => 'Jul',
                '8' => 'Aug',
                '9' => 'Sep',
                '10' => 'Oct',
                '11' => 'Nov',
                '12' => 'Dec',
            ];
        } else {
            $months = [
                '1' => 'January',
                '2' => 'February',
                '3' => 'March',
                '4' => 'April',
                '5' => 'May',
                '6' => 'June',
                '7' => 'July',
                '8' => 'August',
                '9' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December',
            ];
        }

        if ($month) return $months[$month] ?? null;

        return $months;
    }
}

if (!function_exists('floor_number')) {
    /**
     * floor_number
     *
     * @param  mixed $number
     * @return mixed
     */
    function floor_number($number)
    {
        return number_format(floor($number), 0, '.');
    }
}

if (!function_exists('get_class_basename')) {
    /**
     * get_class_basename
     *
     * @param  mixed $model
     * @param  mixed $snake
     * @return mixed
     */
    function get_class_basename($model, $snake = false)
    {
        $class_name = get_class($model);
        $class_name = basename(str_replace('\\', '/', $class_name));

        if ($snake) {
            $class_name = str()->snake($class_name);
        }

        return $class_name;
    }
}

if (!function_exists('parse_bool')) {
    /**
     * parse_bool
     *
     * @param  mixed $value
     * @param  mixed $check_isset
     * @return mixed
     */
    function parse_bool($value, $check_isset = false)
    {
        if ($check_isset && !isset($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('isset_bool')) {
    /**
     * isset_bool
     *
     * @param  mixed $value
     * @param  mixed $default
     * @return mixed
     */
    function isset_bool($value, $default = false)
    {
        if (isset($value)) {
            return $value;
        }

        return $default;
    }
}

if (!function_exists('build_get_url')) {
    /**
     * build_get_url
     *
     * @param  mixed $url
     * @param  mixed $payload
     * @return mixed
     */
    function build_get_url($url, $payload = [])
    {
        $query_parts = [];
        foreach ($payload as $key => $value) {
            $query_parts[] = $key . '=' . $value;
        }

        $full_url = $url . '?' . implode('&', $query_parts);

        return $full_url;
    }
}

if (!function_exists('app_url')) {
    /**
     * app_url
     *
     * @param  mixed $path
     * @param  mixed $type
     * @return mixed
     */
    function app_url($path = null, $type = null)
    {
        // Backwards compatibility
        if ($type === true) $type = 'default';
        if ($type === false) $type = null;

        if ($type) $type = $type . '_';

        $url = config('app.' . $type . 'url');

        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }
}
