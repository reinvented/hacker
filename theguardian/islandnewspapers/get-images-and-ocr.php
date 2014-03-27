<?php

// See http://hacker.vre.upei.ca/bypassing-islandora-harvest-island-newspapers-data-direct

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP script');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$fp = fopen("data/mayne.tsv","r");
$fphtml = fopen("index.html","w");
fwrite($fphtml,"<head>\n");
fwrite($fphtml,'<link type="text/css" rel="stylesheet" media="all" href="css/index.css" />');
fwrite($fphtml,"</head>\n");
fwrite($fphtml,"<body>\n");
fwrite($fphtml,"<table>\n");
fwrite($fphtml,"<tr>\n");
fwrite($fphtml,"<th>\n");
fwrite($fphtml,"Link to Highlighted Newspaper\n");
fwrite($fphtml,"</th>\n");
fwrite($fphtml,"<th>\n");
fwrite($fphtml,"Page Image\n");
fwrite($fphtml,"</th>\n");
fwrite($fphtml,"<th>\n");
fwrite($fphtml,"Page OCR\n");
fwrite($fphtml,"</th>\n");
fwrite($fphtml,"<th>\n");
fwrite($fphtml,"Keyword in Context Snippet\n");
fwrite($fphtml,"</th>\n");
fwrite($fphtml,"</tr>\n");
  
while (!feof($fp)) {
  list($pid,$text) = explode("\t",chop(fgets($fp,4096)));
  print $pid . "\n";
  
  curl_setopt($ch, CURLOPT_URL,"http://newspapers.vre.upei.ca/islandora/object/$pid/datastream/JP2/download");
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  
  $ret = new stdClass;
  $ret->response = curl_exec($ch); // execute and get response
  $ret->error    = curl_error($ch);
  $ret->info     = curl_getinfo($ch);
  
  if ($ret->info['http_code'] == 200) {
    $fp2 = fopen("images/$pid.jp2","w");
    fwrite($fp2,$ret->response);
    fclose($fp2);
  }
  
  curl_setopt($ch, CURLOPT_URL,"http://newspapers.vre.upei.ca/islandora/object/$pid/datastream/OCR/download");
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

  $ret = new stdClass;
  $ret->response = curl_exec($ch); // execute and get response
  $ret->error    = curl_error($ch);
  $ret->info     = curl_getinfo($ch);
  
  if ($ret->info['http_code'] == 200) {
    $fp2 = fopen("ocr/$pid.txt","w");
    fwrite($fp2,$ret->response);
    fclose($fp2);
  }
  
  fwrite($fphtml,"<tr>\n");
  fwrite($fphtml,"<td>\n");
  fwrite($fphtml,"<a href='http://islandnewspapers.ca/islandora/object/$pid?solr[query]=Mayne&solr[params][defType]=dismax&solr[params][facet]=true&solr[params][facet.mincount]=0&solr[params][facet.limit]=20&solr[params][facet.field][0]=PARENT_century_s&solr[params][facet.field][1]=PARENT_decade_s&solr[params][facet.field][2]=PARENT_year_s&solr[params][facet.field][3]=PARENT_month_s&solr[params][qt]=standard&solr[params][facet.date][0]=PARENT_dateIssued_dt&solr[params][f.PARENT_dateIssued_dt.facet.date.start]=NOW/YEAR-120YEARS&solr[params][f.PARENT_dateIssued_dt.facet.date.end]=NOW&solr[params][f.PARENT_dateIssued_dt.facet.date.gap]=%2B1YEAR&solr[params][f.PARENT_dateIssued_dt.facet.mincount]=0&solr[params][facet.date.start]=NOW/YEAR-20YEARS&solr[params][facet.date.end]=NOW&solr[params][facet.date.gap]=%2B1YEAR&solr[params][hl]=true&solr[params][hl.fl]=OCR_t&solr[params][hl.fragsize]=400&solr[params][hl.simple.pre]=%3Cspan%20class%3D%22islandora-solr-highlight%22%3E&solr[params][hl.simple.post]=%3C/span%3E&solr[params][qf]=OCR_t^10.0' target='_blank'>$pid</a>\n");
  fwrite($fphtml,"</td>\n");
  fwrite($fphtml,"<td>\n");
  fwrite($fphtml,"<a href='images/$pid.jp2' target='_blank'>$pid.jp2</a>\n");
  fwrite($fphtml,"</td>\n");
  fwrite($fphtml,"<td>\n");
  fwrite($fphtml,"<a href='ocr/$pid.txt' target='_blank'>$pid.txt</a>\n");
  fwrite($fphtml,"</td>\n");
  fwrite($fphtml,"<td>\n");
  fwrite($fphtml,"$text\n");
  fwrite($fphtml,"</td>\n");
  fwrite($fphtml,"</tr>\n");
}

fwrite($fphtml,"</table>\n");
fwrite($fphtml,"</body>\n");
fclose($fphtml);
fclose($fp);

curl_close ($ch);
unset($ch);