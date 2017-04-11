<?
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('con_to_db.php');

$query_aa = $pdo->prepare('SELECT t.id_tender
						FROM tender as t 
						WHERE t.tender_kwords IS NULL ORDER BY t.id_tender LIMIT 0,1');
$query_aa->execute();
while( $row_aa = $query_aa->fetch(PDO::FETCH_LAZY) ){
    $start =  $row_aa[0];
}
if ($start <> ''){
    $end = $start + 30000;

    $query = $pdo->prepare('SELECT t.purchase_object_info, t.id_tender
							FROM tender as t 
							WHERE id_tender <= '.$end.' AND id_tender >= '.$start.' ORDER BY id_tender');
    $query->execute();
    while( $row = $query->fetch(PDO::FETCH_LAZY) ){
        $result = '';
        $id = $row[1];
        echo $id.'---';
        $query_po = $pdo->prepare('SELECT po.name, po.okpd_name
								 FROM purchase_object as po
								 LEFT JOIN lot as l on l.id_lot = po.id_lot								 
								 WHERE l.id_tender ='.$id);
        $query_po->execute();
        while( $row_po = $query_po->fetch(PDO::FETCH_LAZY) ){
            $result = $result.' '.$row_po[0].' '.$row_po[1];
        }


        $query_a = $pdo->prepare('SELECT file_name FROM attachment WHERE id_tender ='.$id);
        $query_a->execute();
        while( $row_a = $query_a->fetch(PDO::FETCH_LAZY) ){
            $result = $result.' '.$row_a[0];
        }
        $result = $row[0].' '.$result;
        $result = str_replace( '"', '', $result);
        $query_up = $pdo->prepare('UPDATE tender SET tender_kwords = "'.$result.'" WHERE id_tender ='.$id);
        $query_up->execute();

    }
}

?>