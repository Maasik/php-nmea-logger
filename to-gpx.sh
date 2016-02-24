cat *.nmea | gpsbabel -w -r -t -i nmea -f - -o gpx -F "new.gpx" && rm *.nmea
