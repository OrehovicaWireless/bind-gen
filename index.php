<?PHP
require_once "/var/www/orwi-varijable.php";

echo '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<xmp>';

$filek_ptr = fopen("./db.80.10.in-addr.arpa-korisnici", "w");
$popis_query = "
	SELECT ip_adresa, ime, prezime, clanski_broj, oprema.id as oid
	FROM oprema 
		LEFT JOIN clanovi ON oprema.vlasnik=clanovi.id 
	WHERE LENGTH(ip_adresa) > 6
	AND karta_kategorija = 1
	AND vlasnik <> 108
	ORDER BY INET_ATON(ip_adresa)
	";
$popis_result = mysql_query($popis_query) or die(mysql_error());
while($popis_row = mysql_fetch_array($popis_result)){
		$ip_parts = explode(".", $popis_row['ip_adresa']);
		$imeprezime=$popis_row['ime'].'-'.$popis_row['prezime'];
		$search = array("ć","č","ž","š","đ","Ć","Č","Ž","Š","Đ");
		$replacement = array("c","c","z","s","dj","c","c","z","s","dj");
		$imeprezime=strtolower($imeprezime);
		$imeprezime = str_replace($search,$replacement,$imeprezime);
		$imeprezime = preg_replace('#[^a-zA-Z_-]#', '', $imeprezime);
		if (($ip_parts[0]=='10') && ($ip_parts[1]=='80') && (filter_var($popis_row['ip_adresa'], FILTER_VALIDATE_IP))){
			$redak_ptr = str_pad($ip_parts[3].'.'.$ip_parts[2], 8 , " ")."\tIN\tPTR\t".$imeprezime."-".$popis_row['clanski_broj'].".ow.\n";
			echo $redak_ptr;
			fwrite($filek_ptr, $redak_ptr);
		}
	};
fclose($filek_ptr);
echo '</xmp></body>
</html>';
?>