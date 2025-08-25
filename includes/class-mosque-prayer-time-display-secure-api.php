function prayer_times_api($mosque_slug) {
    $curl = curl_init();
    $base_url = 'https://secure-api.net/api/v1';
    $end_point = '/company/prayer/daily/schedule';
    $query_parameter = '?slug=' . urlencode($mosque_slug);

    curl_setopt_array($curl, array(
        CURLOPT_URL => $base_url . $end_point . $query_parameter,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        error_log('Prayer Times API Error: ' . curl_error($curl));
        $response = false;
    } elseif ($http_status !== 200) {
        error_log('Prayer Times API returned status: ' . $http_status);
        $response = false;
    }

    curl_close($curl);
    return $response;
}

// Get the mosque name & slug from the option
$mosque_name = get_option('mosque_prayer_time_mosque_name');
$mosque_slug = get_option('mosque_prayer_time_mosque_slug');

// Fetch data from the API with the mosque slug
$response = prayer_times_api($mosque_slug);
error_log('API Response: ' . print_r($response, true)); // Debug log

if ($response !== false) {
    $prayer_array = json_decode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error decoding JSON: ' . json_last_error_msg());
        echo '<p>Error decoding JSON data.</p>';
        return;
    }

    ?>
    <div class="prayer-times-floating-wrapper">
        <div class="prayer-times-floating">
            <div class="prayer-times-head">
                <div class="prayer-times-header-text">
                    Prayer Times <?php echo esc_html($mosque_name); ?>
                </div>
                <div id="prayer-time-toggle-secure-api">&#9650;</div>
            </div>
            <div class="prayer-times-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Salah</th>
                                <th>Adhan</th>
                                <th>Iqamah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $key) {
                                if (isset($prayer_array->$key) && $prayer_array->$key->isShow) {
                                    $salah = $prayer_array->$key;
                                    ?>
                                    <tr>
                                        <td class="salat-name">
                                            <img src="<?php echo esc_url($salah->salahIcon); ?>" alt="<?php echo esc_attr($salah->salahName); ?>" style="width: 20px; height: 20px; margin-right: 5px;">
                                            <?php echo esc_html($salah->salahName); ?>
                                        </td>
                                        <td class="salat-time"><?php echo esc_html($salah->salahTime); ?></td>
                                        <td class="salat-time"><?php echo esc_html($salah->salahIqamahTime); ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                            if (!empty($prayer_array->jummahTimes)) {
                                foreach ($prayer_array->jummahTimes as $jummah) {
                                    if (!empty($jummah->isShow)) {
                                        ?>
                                        <tr class="jummah-row">
                                            <td class="salat-name"><?php echo esc_html($jummah->salahName); ?></td>
                                            <td class="salat-time"><?php echo esc_html($jummah->jummahTime); ?></td>
                                            <td class="salat-time"><?php echo esc_html($jummah->iqamahTime); ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
