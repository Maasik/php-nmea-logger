<?php
define('PTS_PER_FILE', 100);        // Размер буфера, в количестве позиций (*2 строк)
define('PT_INTERVAL',    3);        // Период записи позиций
define('INACT_INTERVAL', 9);        // Интервалы между обращениями, когда устройство выдаёт неактуальные данные
define('TRY_LIMIT',     50);        // Количество неудачных чтений
define('NMEA_DEV', '/dev/ttyS2');   // Путь GPS-устройству, NMEA-сообщений
define('STOP_FILE', '.gpsstoplog'); // Файл для завершения работф скрипта %)

$points_count = 0;
while (1) {
  if (!($gps_src = fopen(NMEA_DEV, 'r'))) {
    fin();
  }

  $try_count = 0;
  while (!feof($gps_src) && false !== ($gps_res = fgets($gps_src))) {
    if (preg_match('/^\$GPRMC\,[\d\.]+\,A\,/', $gps_res))
      break;
    if (++$try_count > TRY_LIMIT) {
      if (is_file(STOP_FILE)) {
        fin();
      } else {
        $try_count = 0;
        sleep(INACT_INTERVAL);
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
    elseif (++$try_count > TRY_LIMIT) {
      if (is_file(STOP_FILE)) {
        fin();
      }
      else
        break;
    }
  }

  $records .= $gps_res;

  fclose($gps_src);

  if ($points_count == PTS_PER_FILE) {
    $points_count = 0;
    flush_records();
  }

  if (is_file(STOP_FILE))
    fin();

  sleep(PT_INTERVAL);
}

function flush_records() {
  global $dttm, $records;
  file_put_contents($dttm.'.nmea', $records);
}

function fin() {
  global $points_count;
  if ($points_count)
    flush_records();
  @unlink(STOP_FILE);
  exit();
}
