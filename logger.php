<?php
$points_limit = 50;
$sleep_time = 3;
$sleep_read = 9;
$dst_dir = '/home/ora/gps-logger/';
$nmea_src = '/dev/ttyS2';
$stop_file = '.gpsstoplog';
$try_limit = 100;

$points_count = 0;
while (1) {
  if (!($gps_src = fopen($nmea_src, 'r'))) {
    echo "!fopen\n";
    fin();
  }

  $try_count = 0;
  while (!feof($gps_src) && false !== ($gps_res = fgets($gps_src))) {
    if (preg_match('/^\$GPRMC\,.{9}\,A/', $gps_res))
      break;
    elseif (++$try_count > $try_limit) {
      echo "op\n";
      if (is_file($stop_file)) {
        echo "stop_file 1\n";
        fin();
      } else {
        $try_count = 0;
        sleep($sleep_read);
      }
    }
  }

  if (!$points_count++) {
    $records = '';
    if (preg_match('/\$GPRMC\,(\d{6}).*\,(\d{6})\,/', $gps_res, $dttm)) {
      $dttm = str_split($dttm[2].$dttm[1], 2);
      $dttm = '20'.$dttm[2].'-'.$dttm[1].'-'.$dttm[0].'_'.$dttm[3].':'.$dttm[4].':'.$dttm[5];
    } else
      $dttm = time();
  }

  $records .= $gps_res;

  $try_count = 0;
  while (!feof($gps_src) && false !== ($gps_res = fgets($gps_src))) {
    if (preg_match('/^\$GPGGA/', $gps_res))
      break;
    elseif (++$try_count > $try_limit) {
      echo "op2\n";
      if (is_file($stop_file)) {
        echo "stop_file 2\n";
        fin();
      }
      else
        break;
    }
  }

  $records .= $gps_res;

  fclose($gps_src);

  if ($points_count == $points_limit) {
    $points_count = 0;
    flush_records();
  }

  if (is_file($stop_file))
    fin();

  sleep($sleep_time);
}

function flush_records() {
  global $dst_dir, $dttm, $records;
  file_put_contents($dst_dir.$dttm.'.nmea', $records);
}

function fin() {
  global $points_count;
  if ($points_count)
    flush_records();
  exit;
}
