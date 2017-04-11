<?
ini_set('display_errors', 1);
error_reporting(E_ALL);

$sttime=date("D M j G:i:s T Y");
include('con_to_db.php');

$query = $pdo->prepare('SELECT r.path223, s.folder, s.count_archivs, r.id, s.month FROM settings223 as s LEFT JOIN region as r on r.id = s.id_region');
$query->execute();
while( $row = $query->fetch(PDO::FETCH_LAZY) ){
    $region = $row[0];
    $folder = $row[1];
    $count_pars_arh = $row[2];
    $id_region = $row[3];
    $month = $row[4];
}

$ftp_server = "ftp.zakupki.gov.ru";
$ftp_user_name = "fz223free";
$ftp_user_pass = "fz223free";
$conn_id = ftp_connect($ftp_server);
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
ftp_pasv($conn_id, true);
$arhivs = ftp_nlist($conn_id, "/out/published/".$region."/".$folder."/".$month);
foreach( $arhivs as $arh ){
    if(strripos($arh, '.zip')<>0){
        $arr_arhiv[] = $arh;
    }
}

foreach( $arr_arhiv as $arh ){
    if(strripos($arh, $folder.'_'.$region.'_2017')<>0){
        $arr_arhivs[] = $arh;
    }
}

$count_arhivs = count($arr_arhivs);

$x = 0;
$y = 0;
$count_of_type = 0;

while($x<$count_pars_arh && $y<$count_arhivs){
    $arhiv=$arr_arhivs[$y];
    if(total($arhiv, $id_region, $pdo) >= 1) {
        $y++;
    }
    else{
        deletearhiv();
        downloadarhiv($conn_id, $arr_arhivs[$y]);
        pars(getxml(), $pdo, $id_region, $folder);
        $count_of_type++;
        $query = $pdo->prepare('INSERT INTO archiv SET id_region='.$id_region.', name = "'.$arr_arhivs[$y].'", pars = 1');
        $query->execute();
        $x++;
        $y++;
    }
}

//выводим инфо
$z=$y-$x;
$ost=$count_arhivs-$y;
$endtime=date("D M j G:i:s T Y");
echo "<br><br><br>Добавлено в базу архивов: ".$x.". По региону: ". $region."<br>";

//echo "Архивов по этому региону в базе стало: ".$y."<br>";
//echo "Было одникаковых архивов в БД с FTP: ".$z."<br>";

echo "По текущему типу xml - ".$folder."  добалено архивов в количестве:  " .$count_of_type." ";
echo "Всего архивов по региону: ".$count_arhivs."<br>";
echo "Осталось пропарсить по этому региону архивов: ".$ost."<br>";
echo "Start: ".$sttime."<br>";
echo "End: ".$endtime."<br>";
echo "Готово!<br>";

$action = 'Время: '.date("F j, Y, g:i a").' =======> По '.$month.' месяцу в '.$region.' завершена выгрузка '.$x.' архивов. По текущему типу xml - '.$folder.'  добалено: ' .$count_of_type. ' архивов. Осталось из этой папки '.$ost.';';
$log_file = fopen("logs/log_pars223_new.txt","a+");
fwrite($log_file,$action."\r\n\n");
fclose($log_file);

if (($ost == 0) and ($folder == "purchaseNotice")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeAE"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNotice";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeAE")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeAE94"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeAE";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeAE94")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeEP"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeAE94";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeEP")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeIS"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeEP";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeIS")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeOA"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeIS";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeOA")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeOK"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeOA";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeOK")){
    $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeZK"');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка из папки "purchaseNoticeOK";';
    $log_file = fopen("logs/log_pars223_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}
if (($ost == 0) and ($folder == "purchaseNoticeZK")){
    if ( $id_region == 86 ){
        $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNoticeZK", id_region= 1, month = "daily/"');
        $query->execute();
        $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу "purchaseNoticeZK";';
        $log_file = fopen("logs/log_pars223_new.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
    }
    else{
        $id_region++;
        $query = $pdo->prepare('UPDATE settings223 SET folder = "purchaseNotice", id_region= '.$id_region);
        $query->execute();
        $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу "purchaseNoticeZK";';
        $log_file = fopen("logs/log_pars223_new.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
    }
}
// if (($ost == 0) and ($xml_type == "sign")){
// $query = $pdo->prepare('DELETE FROM archiv WHERE pars = 0');
// $query->execute();
// $query = $pdo->prepare('UPDATE settings SET id_xml_type = 4');
// $query->execute();
// $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу SIGN;';
// $log_file = fopen("logs/log_pars_new.txt","a+");
// fwrite($log_file,$action."\r\n\n");
// fclose($log_file);
// }

// if (($ost == 0) and ($xml_type == "cancelFailure")){
// $query = $pdo->prepare('UPDATE archiv SET pars = 1 WHERE pars = 0');
// $query->execute();
// if ( $id_region == 86 ){
// $query = $pdo->prepare('UPDATE settings SET id_xml_type = 1, id_region= 1, month = "currMonth"');
// $query->execute();
// $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу CANCEL_FAILURE;';
// $log_file = fopen("logs/log_pars_new.txt","a+");
// fwrite($log_file,$action."\r\n\n");
// fclose($log_file);
// }
// else{
// $id_region++;
// $query = $pdo->prepare('UPDATE settings SET id_xml_type = 1, id_region= '.$id_region);
// $query->execute();
// $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу CANCEL_FAILURE;';
// $log_file = fopen("logs/log_pars_new.txt","a+");
// fwrite($log_file,$action."\r\n\n");
// fclose($log_file);
// }
// }


function total($arhiv, $id_region, $pdo){
    $res = $pdo->prepare('SELECT Count(id_archiv) FROM archiv WHERE id_region='.$id_region.' AND name="'.$arhiv.'"');
    $res->execute();
    $result = $res->fetchColumn();
    if ($result <> Null){
        $max = $result;
    }
    else
        $max = 0;
    Return $max;
}

function pars($XMLarray, $pdo, $id_region, $folder){
    foreach($XMLarray as $xml){
        try{
            DOMtoSimpleXML($xml);

            $id = '';
            $docPublishDate = '';
            $purchaseNumber = '';
            $href='';
            $purchaseObjectInfo = '';
            $placingWay_name = '';
            $placingWay_code = '';
            $ETP_code = '';
            $ETP_name = '';
            $ETP_url = '';
            $organizer_reg_num = '';
            $organizer_full_name = '';
            $organizer_post_address = '';
            $organizer_fact_address = '';
            $organizer_inn = '';
            $organizer_kpp = '';
            $organizer_responsible_role = '';
            $organizer_last_name = '';
            $organizer_first_name = '';
            $organizer_middle_name = '';
            $organizer_contact = '';
            $organizer_email = '';
            $organizer_fax = '';
            $organizer_phone = '';
            $id_etp = 0;
            $id_organizer = 0;
            $id_placing_way = 0;

            try{
                $export = simplexml_load_file("./SimpleXML223/xml.xml");
            }
            catch(Exception $e){
                $action = $e->getMessage();
                $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
                fwrite($log_file,$action."\r\n\n");
                fclose($log_file);
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
            }
            //echo $xml."<br>";
            if ($folder == "purchaseNotice" )
                $f = "purchaseNoticeData";
            if ($folder == "purchaseNoticeAE" )
                $f = "purchaseNoticeAEData";
            if ($folder == "purchaseNoticeAE94" )
                $f = "purchaseNoticeAE94FZData";
            if ($folder == "purchaseNoticeEP" )
                $f = "purchaseNoticeEPData";
            if ($folder == "purchaseNoticeIS" )
                $f = "purchaseNoticeISData";
            if ($folder == "purchaseNoticeOA" )
                $f = "purchaseNoticeOAData";
            if ($folder == "purchaseNoticeOK" )
                $f = "purchaseNoticeOKData";
            if ($folder == "purchaseNoticeZK" )
                $f = "purchaseNoticeZKData";
            $id = $export->body->item->guid;
            $purchaseNumber = $export->body->item->$f->registrationNumber;
            $docPublishDate = $export->body->item->$f->publicationDateTime;
            $href = $export->body->item->$f->urlVSRZ;
            $purchaseObjectInfo = $export->body->item->$f->name;

            if ( $id == '' ){
                $id = 0;
            }

            $query = $pdo->prepare('SELECT count(id_tender) FROM tender WHERE id_xml = "'.$id.'" AND id_region = '.$id_region);
            $query->execute();

            $countId = $query->fetchColumn();
            if ( $countId == 0 ){
                $query = $pdo->prepare('INSERT INTO tender ( id_region, id_xml, purchase_number, doc_publish_date, href, purchase_object_info, type_fz ) 
									VALUES ( :id_region, :id_xml, :purchase_number, :doc_publish_date, :href, :purchase_object_info, 223 ) ');
                $query->bindParam(':id_region', $id_region);
                $query->bindParam(':id_xml', $id);
                $query->bindParam(':purchase_number', $purchaseNumber);
                $query->bindParam(':doc_publish_date', $docPublishDate);
                $query->bindParam(':href', $href);
                $query->bindParam(':purchase_object_info', $purchaseObjectInfo);
                $query->execute();

                $query = $pdo->prepare('SELECT id_tender FROM tender WHERE id_xml = "'.$id.'" AND id_region = '.$id_region);
                $query->execute();
                $id_tender = $query->fetchColumn();

                //$organizer_reg_num = checked_value($child->purchaseResponsible->responsibleOrg->regNum);
                $organizer_full_name = checked_value($export->body->item->$f->placer->mainInfo->fullName);
                $organizer_post_address = checked_value($export->body->item->$f->placer->mainInfo->postalAddress);
                $organizer_fact_address = checked_value($export->body->item->$f->placer->mainInfo->legalAddress);
                $organizer_inn = checked_value($export->body->item->$f->placer->mainInfo->inn);
                $organizer_kpp = checked_value($export->body->item->$f->placer->mainInfo->kpp);
                // $organizer_responsible_role = checked_value($child->purchaseResponsible->responsibleRole);
                // $organizer_last_name = $child->purchaseResponsible->responsibleInfo->contactPerson->lastName;
                // $organizer_first_name = $child->purchaseResponsible->responsibleInfo->contactPerson->firstName;
                // $organizer_middle_name = $child->purchaseResponsible->responsibleInfo->contactPerson->middleName;
                // $organizer_contact = $organizer_last_name." ".$organizer_first_name." ".$organizer_middle_name;
                $organizer_email = checked_value($export->body->item->$f->placer->mainInfo->email);
                $organizer_phone = checked_value($export->body->item->$f->placer->mainInfo->phone);
                $organizer_fax = checked_value($export->body->item->$f->placer->mainInfo->fax);

                if ( $organizer_inn <> '' ){
                    $query = $pdo->prepare('SELECT id_organizer FROM organizer WHERE inn = :inn');
                    $query->bindParam(':inn', $organizer_inn);
                    $query->execute();
                    $id_organizer = $query->fetchColumn();
                    if( !$id_organizer){
                        $query = $pdo->prepare('INSERT INTO organizer ( full_name, post_address, 
																	fact_address, inn, kpp,  
																	contact_email, contact_phone, contact_fax ) 
											VALUES ( :full_name, :post_address, 
																	:fact_address, :inn, :kpp,  
																	:contact_email, :contact_phone, :contact_fax )');
                        // $query->bindParam(':reg_num', $organizer_reg_num);
                        $query->bindParam(':full_name', $organizer_full_name);
                        $query->bindParam(':post_address', $organizer_post_address);
                        $query->bindParam(':fact_address', $organizer_fact_address);
                        $query->bindParam(':inn', $organizer_inn);
                        $query->bindParam(':kpp', $organizer_kpp);
                        // $query->bindParam(':responsible_role', $organizer_responsible_role);
                        // $query->bindParam(':contact_person', $organizer_contact);
                        $query->bindParam(':contact_email', $organizer_email);
                        $query->bindParam(':contact_phone', $organizer_phone);
                        $query->bindParam(':contact_fax', $organizer_fax);
                        $query->execute();

                        $query = $pdo->prepare('SELECT id_organizer FROM organizer WHERE inn = :inn');
                        $query->bindParam(':inn', $organizer_inn);
                        $query->execute();
                        $id_organizer = $query->fetchColumn();
                    }
                }


                $placingWay_code = checked_value($export->body->item->$f->purchaseMethodCode);
                $placingWay_name = checked_value($export->body->item->$f->purchaseCodeName);

                if ( $placingWay_code <> '' ){
                    $query = $pdo->prepare('SELECT id_placing_way FROM placing_way WHERE code = :placingWay_code');
                    $query->bindParam(':placingWay_code', $placingWay_code);
                    $query->execute();
                    $id_placing_way = $query->fetchColumn();
                    if( !$id_placing_way ){
                        $query = $pdo->prepare('INSERT INTO placing_way ( code, name ) VALUES ( :code, :name )');
                        $query->bindParam(':code', $placingWay_code);
                        $query->bindParam(':name', $placingWay_name);
                        $query->execute();
                        $query = $pdo->prepare('SELECT id_placing_way FROM placing_way WHERE code = :placingWay_code');
                        $query->bindParam(':placingWay_code', $placingWay_code);
                        $query->execute();
                        $id_placing_way = $query->fetchColumn();
                    }
                }

                $ETP_code = checked_value($export->body->item->$f->electronicPlaceInfo->electronicPlaceId);
                $ETP_name = checked_value($export->body->item->$f->electronicPlaceInfo->name);
                $ETP_url = checked_value($export->body->item->$f->electronicPlaceInfo->url);

                $id_etp = 0;
                if ( $ETP_code <> '' ){
                    $query = $pdo->prepare('SELECT id_etp FROM etp WHERE code = :code');
                    $query->bindParam(':code', $ETP_code);
                    $query->execute();
                    $id_etp = $query->fetchColumn();
                    if( !$id_etp ){
                        $query = $pdo->prepare('INSERT INTO etp ( code, name, url ) VALUES ( :code, :name, :url )');
                        $query->bindParam(':code', $ETP_code);
                        $query->bindParam(':name', $ETP_name);
                        $query->bindParam(':url', $ETP_url);
                        $query->execute();
                        $query = $pdo->prepare('SELECT id_etp FROM etp WHERE code = :code');
                        $query->bindParam(':code', $ETP_code);
                        $query->execute();
                        $id_etp = $query->fetchColumn();
                    }
                }

                if ( $export->body->item->$f->attachments ){
                    foreach ($export->body->item->$f->attachments->document as $XMLattachment){
                        $attach_name = '';
                        $attach_url = '';
                        $attach_description = '';
                        $attach_name = $XMLattachment->fileName;
                        $attach_description = $XMLattachment->description;
                        $attach_url = $XMLattachment->url;
                        if ( $attach_name <> '' ){
                            $query = $pdo->prepare('INSERT INTO attachment ( id_tender, file_name, url, description ) VALUES ( :id_tender, :file_name, :url, :description )');
                            $query->bindParam(':id_tender', $id_tender);
                            $query->bindParam(':file_name', $attach_name);
                            $query->bindParam(':url', $attach_url);
                            $query->bindParam(':description', $attach_description);
                            $query->execute();
                        }
                    }
                }


                if (isset($export->body->item->$f->submissionCloseDateTime))
                    $end_date = substr($export->body->item->$f->submissionCloseDateTime, 0, 10);
                // else
                // $end_date = '';
                // if (isset($child->procedureInfo->scoring->date))
                // $scoring_date = substr($child->procedureInfo->scoring->date, 0, 10);
                // else
                // $scoring_date = '';
                // if (isset($child->procedureInfo->bidding->date))
                // $bidding_date = substr($child->procedureInfo->bidding->date, 0, 10);
                // else
                // $bidding_date = '';

                $query = $pdo->prepare('UPDATE tender SET id_organizer = :id_organizer, id_placing_way = :id_placing_way, id_etp = :id_etp,
									end_date = :end_date WHERE id_tender = :id_tender');

                $query->bindParam(':id_organizer', $id_organizer);
                $query->bindParam(':id_placing_way', $id_placing_way);
                $query->bindParam(':id_etp', $id_etp);
                $query->bindParam(':end_date', $end_date);
                // $query->bindParam(':scoring_date', $scoring_date);
                // $query->bindParam(':bidding_date', $bidding_date);
                $query->bindParam(':id_tender', $id_tender);
                $query->execute();


                //$lot_finance_source = '';

                $customer_inn =  $export->body->item->$f->customer->mainInfo->inn;
                if ($customer_inn == Null)
                    $customer_inn = '';
                $customer_full_name = $export->body->item->$f->customer->mainInfo->fullName;
                if ($customer_full_name == Null)
                    $customer_full_name = '';
                $customer_kpp = $export->body->item->$f->customer->mainInfo->kpp;
                if ($customer_kpp == Null)
                    $customer_kpp = '';
                $customer_ogrn = $export->body->item->$f->customer->mainInfo->ogrn;
                if ($customer_ogrn == Null)
                    $customer_ogrn = '';
                $customer_post_address = $export->body->item->$f->customer->mainInfo->postalAddress;
                if ($customer_post_address == Null)
                    $customer_post_address = '';
                $customer_phone = $export->body->item->$f->customer->mainInfo->phone;
                if ($customer_phone == Null)
                    $customer_phone = '';
                $customer_fax = $export->body->item->$f->customer->mainInfo->fax;
                if ($customer_fax == Null)
                    $customer_fax = '';
                $customer_email = $export->body->item->$f->customer->mainInfo->email;
                if ($customer_email == Null)
                    $customer_email = '';
                $cus_ln = $export->body->item->$f->contact->lastName;
                $cus_fn = $export->body->item->$f->contact->firstName;
                $cus_mn = $export->body->item->$f->contact->middleName;
                $cus_contact = $cus_ln.' '.$cus_fn.' '.$cus_mn;

                $id_customer = 0;
                if ( $customer_inn <> '' ){
                    $query = $pdo->prepare('SELECT regNumber FROM od_customer WHERE inn = :inn');
                    $query->bindParam(':inn', $customer_inn);
                    $query->execute();
                    $regNum_cust = $query->fetchcolumn();
                    //echo $customer_inn."-".$regNum_cust."<br>";
                    if ($regNum_cust <> ''){
                        $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                        $query->bindParam(':reg_num', $regNum_cust);
                        $query->execute();
                        $id_customer = $query->fetchColumn();
                        if( !$id_customer  ){
                            $query = $pdo->prepare('INSERT INTO customer SET reg_num = :reg_num, full_name = :full_name');
                            $query->bindParam(':reg_num', $regNum_cust);
                            $query->bindParam(':full_name', $customer_full_name);
                            $query->execute();

                            $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                            $query->bindParam(':reg_num', $regNum_cust);
                            $query->execute();
                            $id_customer = $query->fetchColumn();
                        }
                    }
                    else{
                        $query = $pdo->prepare('SELECT id_customer FROM customer WHERE inn = :inn');
                        $query->bindParam(':inn', $customer_inn);
                        $query->execute();
                        $id_customer = $query->fetchcolumn();
                        if ( !$id_customer ){
                            $reg_num223 = '00000223'.$customer_inn;
                            $query = $pdo->prepare('INSERT INTO customer SET inn = :inn, full_name = :full_name, reg_num = :reg_num');
                            $query->bindParam(':inn', $customer_inn);
                            $query->bindParam(':full_name', $customer_full_name);
                            $query->bindParam(':reg_num', $reg_num223);
                            $query->execute();

                            $query = $pdo->prepare('INSERT INTO customer223 SET inn = :inn, full_name = :full_name, contact = :contact,
												kpp = :kpp, ogrn = :ogrn, post_address = :post_address, phone = :phone, fax = :fax, email = :email');
                            $query->bindParam(':inn', $customer_inn);
                            $query->bindParam(':full_name', $customer_full_name);
                            $query->bindParam(':kpp', $customer_kpp);
                            $query->bindParam(':ogrn', $customer_ogrn);
                            $query->bindParam(':post_address', $customer_post_address);
                            $query->bindParam(':phone', $customer_phone);
                            $query->bindParam(':fax', $customer_fax);
                            $query->bindParam(':email', $customer_email);
                            $query->bindParam(':contact', $cus_contact);
                            $query->execute();

                            $query = $pdo->prepare('SELECT id_customer FROM customer WHERE inn = :inn');
                            $query->bindParam(':inn', $customer_inn);
                            $query->execute();
                            $id_customer = $query->fetchColumn();
                        }
                    }
                }

                if (isset($export->body->item->$f->lots))
                    $lots = $export->body->item->$f->lots->lot;
                else
                    $lots = $export->body->item->$f->lot;

                $lotNumber = 1;
                $lot_max_price = '';
                $lot_currency = '';

                foreach ($lots as $lot){
                    $lot_max_price = $lot->lotData->initialSum;
                    $lot_currency = checked_value($lot->lotData->currency->name);
                    // $lot_finance_source = checked_value($lot->financeSource);

                    $query = $pdo->prepare('INSERT INTO lot ( id_tender, lot_number, max_price, currency ) VALUES ( :id_tender, :lot_number, :max_price, :currency )');
                    $query->bindParam(':id_tender', $id_tender);
                    $query->bindParam(':lot_number', $lotNumber);
                    $query->bindParam(':max_price', $lot_max_price);
                    $query->bindParam(':currency', $lot_currency);
                    // $query->bindParam(':finance_source', $lot_finance_source);
                    $query->execute();

                    $query = $pdo->prepare('SELECT id_lot FROM lot WHERE id_tender = :id_tender AND lot_number = :lot_number AND max_price = :max_price');
                    $query->bindParam(':id_tender', $id_tender);
                    $query->bindParam(':lot_number', $lotNumber);
                    $query->bindParam(':max_price', $lot_max_price);
                    $query->execute();
                    $id_lot = $query->fetchColumn();


                    $okpd2_code = '';
                    $okpd_code = '';
                    $okpd_name = '';
                    if (isset($lot->lotData->lotItems->lotItem)){
                        foreach ($lot->lotData->lotItems->lotItem as $lotItem){
                            if (isset($lotItem->okpd2->code))
                                $okpd2_code = $lotItem->okpd2->code;
                            if (isset($lotItem->okpd2->name)){
                                $okpd_name = $lotItem->okpd2->name;
                                $name = $lotItem->okpd2->name;
                            }
                            if (isset($lotItem->qty))
                                $quantity_value = $lotItem->qty;
                            else
                                $quantity_value = 0;
                            // if (isset($purchaseObject->price)){
                            // $price = $purchaseObject->price;
                            // }
                            // else
                            // $price = 0;
                            if (isset($lotItem->okei->name))
                                $okei = $lotItem->okei->name;
                            else
                                $okei = '';
                            // if (isset($purchaseObject->sum))
                            // $sum = $purchaseObject->sum;
                            // else
                            // $sum = 0;

                            $okpd2_group_code = '';
                            $okpd2_group_level1_code = '';

                            if ($okpd2_code <> ''){
                                $okpd2_group_code = substr( $okpd2_code, 0, 2 );
                                $okpd2_group_level1_code = substr( $okpd2_code, strpos($okpd2_code, ".")+1, 1 );
                            }

                            // if (isset($purchaseObject->pucustomerQuantities->customerQuantitie)){
                            // foreach ($purchaseObject->pucustomerQuantities->customerQuantitie as $customerQuantitie){
                            // $customer_quantity_value = $customerQuantitie->quantitie;
                            // $cust_regNum = $customerQuantitie->customer->regNum;
                            // $cust_full_name = $customerQuantitie->fullName;

                            // if ( $customer_regNum <> '' ){
                            // $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                            // $query->bindParam(':reg_num', $cust_regNum);
                            // $query->execute();
                            // $id_customer = $query->fetchColumn();
                            // if( !$id_customer  ){
                            // $query = $pdo->prepare('INSERT INTO customer SET reg_num = :reg_num, full_name = :full_name');
                            // $query->bindParam(':reg_num', $cust_regNum);
                            // $query->bindParam(':full_name', $cust_full_name);
                            // $query->execute();

                            // $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                            // $query->bindParam(':reg_num', $cust_regNum);
                            // $query->execute();
                            // $id_customer = $query->fetchColumn();
                            // }
                            // }
                            // $query = $pdo->prepare('INSERT INTO purchase_object ( id_lot, id_customer ,okpd2_code, okpd2_group_code,
                            // okpd2_group_level1_code, okpd_code, okpd_name, name, quantity_value, price,
                            // okei, sum, customer_quantity_value ) VALUES ( :id_lot, :id_customer ,:okpd2_code, :okpd2_group_code,
                            // :okpd2_group_level1_code, :okpd_code, :okpd_name, :name, :quantity_value, :price,
                            // :okei, :sum, :customer_quantity_value )');
                            // $query->bindParam(':id_lot', $id_lot);
                            // $query->bindParam(':id_customer', $id_customer);
                            // $query->bindParam(':okpd2_code', $okpd2_code);
                            // $query->bindParam(':okpd2_group_code', $okpd2_group_code);
                            // $query->bindParam(':okpd2_group_level1_code', $okpd2_group_level1_code);
                            // $query->bindParam(':okpd_code', $okpd_code);
                            // $query->bindParam(':okpd_name', $okpd_name);
                            // $query->bindParam(':name', $name);
                            // $query->bindParam(':quantity_value', $quantity_value);
                            // $query->bindParam(':price', $price);
                            // $query->bindParam(':okei',$okei);
                            // $query->bindParam(':sum', $sum);
                            // $query->bindParam(':customer_quantity_value', $customer_quantity_value);
                            // $query->execute();
                            // }
                            // }
                            // else{
                            $query = $pdo->prepare('INSERT INTO purchase_object ( id_lot, id_customer ,okpd2_code, okpd2_group_code,
													okpd2_group_level1_code, okpd_name, name, quantity_value,  
													okei, customer_quantity_value ) VALUES ( :id_lot, :id_customer ,:okpd2_code, :okpd2_group_code,
													:okpd2_group_level1_code, :okpd_name, :name, :quantity_value,  
													:okei, :quantity_value )');
                            $query->bindParam(':id_lot', $id_lot);
                            $query->bindParam(':id_customer', $id_customer);
                            $query->bindParam(':okpd2_code', $okpd2_code);
                            $query->bindParam(':okpd2_group_code', $okpd2_group_code);
                            $query->bindParam(':okpd2_group_level1_code', $okpd2_group_level1_code);
                            // $query->bindParam(':okpd_code', $okpd_code);
                            $query->bindParam(':okpd_name', $okpd_name);
                            $query->bindParam(':name', $name);
                            $query->bindParam(':quantity_value', $quantity_value);
                            // $query->bindParam(':price', $price);
                            $query->bindParam(':okei',$okei);
                            // $query->bindParam(':sum', $sum);
                            $query->execute();
                            // }
                        }
                    }
                    // $lotNumber++;
                }
            }
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
            fwrite($log_file,$action."\r\n\n");
            fclose($log_file);
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
}

function cancel($XMLarray, $pdo, $id_region){
    foreach($XMLarray as $xml){
        try{
            DOMtoSimpleXML($xml);

            $purchaseNumber = '';
            $export = simplexml_load_file("./SimpleXML223/xml.xml");

            foreach ( $export->children() as $child ){
                $purchaseNumber = $child->purchaseNumber;
            }

            $query = $pdo->prepare('UPDATE tender SET cancel = 1 WHERE purchase_number = :purchase_number');
            $query->bindParam(':purchase_number', $purchaseNumber);
            $query->execute();
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
            fwrite($log_file,$action."\r\n\n");
            fclose($log_file);
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
}

function cancelFailure($XMLarray, $pdo, $id_region){
    foreach($XMLarray as $xml){
        try{
            DOMtoSimpleXML($xml);
            $export = simplexml_load_file("./SimpleXML223/xml.xml");

            $purchaseNumber = '';

            foreach ( $export->children() as $child ){
                $purchaseNumber = $child->purchaseNumber;
            }

            $query = $pdo->prepare('UPDATE tender SET cancel_failure = 1 WHERE purchase_number = :purchase_number');
            $query->bindParam(':purchase_number', $purchaseNumber);
            $query->execute();
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
            fwrite($log_file,$action."\r\n\n");
            fclose($log_file);
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
}

function sign($XMLarray, $pdo, $id_region){
    foreach($XMLarray as $xml){
        try{
            DOMtoSimpleXML($xml);

            $purchaseNumber = '';
            $sign_number = '';
            $sign_date = '';
            $organizer_reg_num = '';
            $contract_sign_price = 0;
            $sign_currency = '';
            $conclude_contract_right = 0;
            $protocole_date = '';
            $supplier_contact = '';
            $supplier_lastName = '';
            $supplier_firstName = '';
            $supplier_middleName = '';
            $supplier_email = '';
            $supplier_contact_phone = '';
            $supplier_contact_fax = '';
            $supplier_inn = '';
            $participant_type = '';
            $country_full_name = '';
            $factual_address = '';
            $post_address = '';
            $organization_name = '';
            $customer_reg_num = '';
            $id_supplier = '';
            $id_customer = 0;

            $export = simplexml_load_file("./SimpleXML223/xml.xml");

            foreach ( $export->children() as $child ){
                $purchaseNumber = checked_value($child->foundation->order->purchaseNumber);
                $sign_number =  checked_value($child->foundation->order->foundationProtocolNumber);
                $sign_date = $child->signDate;
                $customer_reg_num = checked_value($child->customer->regNum);
                $contract_sign_price = $child->price;
                if ($contract_sign_price == Null)
                    $contract_sign_price = 0;
                $sign_currency = checked_value($child->currency->name);
                $conclude_contract_right = checked_value($child->concludeContractRight);
                $protocole_date = checked_value($child->protocolDate);

                $supplier_lastName = $child->suppliers->supplier->contactInfo->lastName;
                $supplier_firstName = $child->suppliers->supplier->contactInfo->firstName;
                $supplier_middleName = $child->suppliers->supplier->contactInfo->middleName;
                $supplier_contact = $supplier_lastName." ".$supplier_firstName." ".$supplier_middleName;

                $supplier_email = checked_value($child->suppliers->supplier->contactInfo->contactEMail);
                $supplier_contact_phone = checked_value($child->suppliers->supplier->contactPhone);
                $supplier_contact_fax = checked_value($child->suppliers->supplier->contactFax);
                $supplier_inn =  checked_value($child->suppliers->supplier->inn);
                $participant_type = checked_value($child->suppliers->supplier->participantType);
                $organization_name = checked_value($child->suppliers->supplier->organizationName);
                $country_full_name = checked_value($child->suppliers->supplier->country->countryFullName);
                $factual_address = checked_value($child->suppliers->supplier->factualAddress);
                $post_address = checked_value($child->suppliers->supplier->postAddress);

            }

            if ( isset($customer_reg_num) ){
                $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                $query->bindParam(':reg_num', $customer_reg_num);
                $query->execute();
                $id_customer = $query->fetchColumn();
                if ($id_customer == ''){
                    $id_customer = 0;
                }
            }
            if (!isset($id_customer)){
                $id_customer = 0;
            }

            $query = $pdo->prepare('SELECT id_tender FROM tender WHERE id_region = :id_region AND cancel = 0 AND purchase_number = :purchase_number');
            $query->bindParam(':id_region', $id_region);
            $query->bindParam(':purchase_number', $purchaseNumber);
            $query->execute();
            $id_tender = $query->fetchColumn();
            if (!$id_tender){
                $id_tender = 0;
            }



            if ( $supplier_inn <> '' ){
                $query = $pdo->prepare('SELECT id_supplier FROM supplier WHERE inn_supplier = :inn_supplier');
                $query->bindParam(':inn_supplier', $supplier_inn);
                $query->execute();
                $id_supplier = $query->fetchColumn();
            }

            if( $id_supplier == ''  ){
                //$query = $pdo->prepare('INSERT INTO supplier SET participant_type = "'.$participant_type.'", inn_supplier = "'.$supplier_inn.'",
                //organization_name = '.$organization_name.', country_full_name="'.$country_full_name.'",
                //factual_address='.$factual_address.', post_address='.$post_address.', contact="'.$supplier_contact.'",
                //email="'.$supplier_email.'", phone="'.$supplier_contact_phone.'", fax="'.$supplier_contact_fax.'"');
                $query = $pdo->prepare('INSERT INTO supplier ( participant_type, inn_supplier, organization_name, country_full_name, factual_address,
										post_address, contact, email, phone, fax ) VALUES ( :participant_type, :inn_supplier, :organization_name, 
										:country_full_name, :factual_address, :post_address, :contact, :email, :phone, :fax )');
                $query->bindParam(':participant_type', $participant_type);
                $query->bindParam(':inn_supplier', $supplier_inn);
                $query->bindParam(':organization_name', $organization_name);
                $query->bindParam(':country_full_name', $country_full_name);
                $query->bindParam(':factual_address', $factual_address);
                $query->bindParam(':post_address', $post_address);
                $query->bindParam(':contact', $supplier_contact);
                $query->bindParam(':email', $supplier_email);
                $query->bindParam(':phone', $supplier_contact_phone);
                $query->bindParam(':fax', $supplier_contact_fax);
                $query->execute();

                $query = $pdo->prepare('SELECT id_supplier FROM supplier WHERE inn_supplier = :inn_supplier');
                $query->bindParam(':inn_supplier', $supplier_inn);
                $query->execute();
                $id_supplier = $query->fetchColumn();
            }

            if ($contract_sign_price == '')
                $contract_sign_price = 0;
            //$query = $pdo->prepare('INSERT INTO contract_sign SET id_tender ='.$id_tender.', purchase_number="'.$purchaseNumber.'",
            //sign_number="'.$sign_number.'", sign_date="'.$sign_date.'", id_customer='.$id_customer.', customer_reg_num="'.$customer_reg_num.'",
            //id_supplier='.$id_supplier.', contract_sign_price ='.$contract_sign_price.', sign_currency="'.$sign_currency.'", conclude_contract_right='.$conclude_contract_right.',
            //protocole_date="'.$protocole_date.'", supplier_contact="'.$supplier_contact.'", supplier_email="'.$supplier_email.'",
            //supplier_contact_phone = "'.$supplier_contact_phone.'", supplier_contact_fax="'.$supplier_contact_fax.'"');
            $query = $pdo->prepare('INSERT INTO contract_sign ( id_tender, purchase_number, sign_number, sign_date, id_customer, customer_reg_num,
									id_supplier, contract_sign_price, sign_currency, conclude_contract_right, protocole_date, supplier_contact,
									supplier_email, supplier_contact_phone, supplier_contact_fax ) VALUES ( :id_tender, :purchase_number, 
									:sign_number, :sign_date, :id_customer, :customer_reg_num, :id_supplier, :contract_sign_price, 
									:sign_currency, :conclude_contract_right, :protocole_date, :supplier_contact,
									:supplier_email, :supplier_contact_phone, :supplier_contact_fax )');
            $query->bindParam(':id_tender', $id_tender);
            $query->bindParam(':purchase_number', $purchaseNumber);
            $query->bindParam(':sign_number', $sign_number);
            $query->bindParam(':sign_date', $sign_date);
            $query->bindParam(':id_customer', $id_customer);
            $query->bindParam(':customer_reg_num', $customer_reg_num);
            $query->bindParam(':id_supplier', $id_supplier);
            $query->bindParam(':contract_sign_price', $contract_sign_price);
            $query->bindParam(':sign_currency', $sign_currency);
            $query->bindParam(':conclude_contract_right', $conclude_contract_right);
            $query->bindParam(':protocole_date', $protocole_date);
            $query->bindParam(':supplier_contact', $supplier_contact);
            $query->bindParam(':supplier_email', $supplier_email);
            $query->bindParam(':supplier_contact_phone', $supplier_contact_phone);
            $query->bindParam(':supplier_contact_fax', $supplier_contact_fax);
            $query->execute();
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
            fwrite($log_file,$action."\r\n\n");
            fclose($log_file);
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
}

function downloadarhiv($conn_id, $arhiv){
    $local_archiv = "arhiv.zip";
    ftp_get($conn_id, $local_archiv, $arhiv, FTP_BINARY);
    $zip = new ZipArchive;
    $zip->open('arhiv.zip');
    $zip->extractTo('./xmls223');
    $zip->close();
}

function deletearhiv(){
    if (file_exists('./xmls223'))
        foreach (glob('./xmls223/*.xml') as $file)
            unlink($file);
}

function checked_value( $value ){
    if(isset($value))
        $result = $value;
    else
        $result = '';
    return $result;
}

function DOMtoSimpleXML($xml){
    try{
        $file = ("./SimpleXML223/xml.xml");
        $a = file_get_contents("./xmls223/".$xml);
        if($a === false){
            throw new Exception('Невозможно открыть файл!');
        }
        $xml_origin = str_replace("ns2:","", $a);
        $xml_origin =  str_replace("oos:","", $xml_origin);
        file_put_contents($file, $xml_origin);
    }
    catch( Exception $e ){
        $action = $e->getMessage();
        $log_file = fopen("logs/log_pars223_new_exception.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
    }
}

function getxml(){
    $filelist = array();
    if ($handle = opendir("./xmls223")){
        while ($entry = readdir($handle)) {
            if (strpos($entry, "xml") <> 0)
                $filelist[] = $entry;
        }
    }
    closedir($handle);
    Return $filelist;
}

$pdo = null;
?>