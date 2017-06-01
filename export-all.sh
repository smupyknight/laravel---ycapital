#!/bin/bash

dir="/tmp/export"
mkdir -p $dir
chmod 777 $dir

fields="state, court_type, court_address, court_phone, court_room, file_no, file_name, origin_location, current_location, session_time, jurisdiction, officer, list_type, plaintiff_company, plaintiff_first_name, plaintiff_last_name, defendant_company, defendant_first_name, defendant_last_name"

for scraper in qld qld-magistrates nsw vic-county vic-magistrates vic-supreme wa act-supreme act-magistrates act-acat federal-1 federal-2; do
	mysql -e "select $fields into outfile '$dir/$scraper.csv.tmp' fields terminated by ',' lines terminated by '\n' from ycapital.proceedings where scraper = '$scraper';" 

	echo $fields > $dir/$scraper.csv
	cat $dir/$scraper.csv.tmp >> $dir/$scraper.csv
	rm -f $dir/$scraper.csv.tmp
done
