<?
ini_set('display_errors', 1);
error_reporting(E_ALL);

$sttime=date("D M j G:i:s T Y");
include('con_to_db.php');

$query = $pdo->prepare('SELECT r.path, s.month, s.count_archivs, xt.name, r.id FROM settings as s LEFT JOIN region as r on r.id = s.id_region
						LEFT JOIN xml_type as xt on xt.id_xml_type = s.id_xml_type');
$query->execute();

while( $row = $query->fetch(PDO::FETCH_LAZY) ){
    $region = $row[0];
    $path = $row[1];
    $count_pars_arh = $row[2];
    $xml_type = $row[3];
    $id_region = $row[4];
}

$ftp_server = "ftp.zakupki.gov.ru";
$ftp_user_name = "free";
$ftp_user_pass = "free";
$conn_id = ftp_connect($ftp_server);
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
ftp_pasv($conn_id, true);
$arhivs = ftp_nlist($conn_id, "fcs_regions/".$region."/notifications/".$path);
foreach( $arhivs as $arh ){
    if(strripos($arh, '.zip')<>0){
        $arr_arhiv[] = $arh;
    }
}

foreach( $arr_arhiv as $arh ){
    if(strripos($arh, 'notification_'.$region.'_2017')<>0){
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
        if (count(getxml()[$xml_type]) > 0){
            $xml_type(getxml()[$xml_type], $pdo, $id_region);
            $count_of_type++;
            $query = $pdo->prepare('INSERT INTO archiv SET id_region='.$id_region.', name = "'.$arr_arhivs[$y].'", pars = 1');
            $query->execute();
        }
        else{
            $query = $pdo->prepare('INSERT INTO archiv SET id_region='.$id_region.', name = "'.$arr_arhivs[$y].'", pars = 0');
            $query->execute();
        }
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
echo "По текущему типу xml - ".$xml_type."  добалено архивов в количестве:  " .$count_of_type." ";
echo "Всего архивов по региону: ".$count_arhivs."<br>";
echo "Осталось пропарсить по этому региону архивов: ".$ost."<br>";
echo "Start: ".$sttime."<br>";
echo "End: ".$endtime."<br>";
echo "Готово!<br>";

$action = 'Время: '.date("F j, Y, g:i a").' =======> По '.$path.' месяцу в '.$region.' завершена выгрузка '.$x.' архивов. По текущему типу xml - '.$xml_type.'  добалено: ' .$count_of_type. ' архивов. Осталось из этой папки '.$ost.';';
$log_file = fopen("logs/log_pars_new.txt","a+");
fwrite($log_file,$action."\r\n\n");
fclose($log_file);

if (($ost == 0) and ($xml_type == "xml44")){
    $query = $pdo->prepare('DELETE FROM archiv WHERE pars = 0');
    $query->execute();
    $query = $pdo->prepare('UPDATE settings SET id_xml_type = 2');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу XML44;';
    $log_file = fopen("logs/log_pars_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}

if (($ost == 0) and ($xml_type == "cancel")){
    $query = $pdo->prepare('DELETE FROM archiv WHERE pars = 0');
    $query->execute();
    $query = $pdo->prepare('UPDATE settings SET id_xml_type = 3');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу CANCEL;';
    $log_file = fopen("logs/log_pars_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}

if (($ost == 0) and ($xml_type == "sign")){
    $query = $pdo->prepare('DELETE FROM archiv WHERE pars = 0');
    $query->execute();
    $query = $pdo->prepare('UPDATE settings SET id_xml_type = 4');
    $query->execute();
    $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу SIGN;';
    $log_file = fopen("logs/log_pars_new.txt","a+");
    fwrite($log_file,$action."\r\n\n");
    fclose($log_file);
}

if (($ost == 0) and ($xml_type == "cancelFailure")){
    $query = $pdo->prepare('UPDATE archiv SET pars = 1 WHERE pars = 0');
    $query->execute();
    if ( $id_region == 86 ){
        $query = $pdo->prepare('UPDATE settings SET id_xml_type = 1, id_region= 1, month = "currMonth"');
        $query->execute();
        $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу CANCEL_FAILURE;';
        $log_file = fopen("logs/log_pars_new.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
    }
    else{
        $id_region++;
        $query = $pdo->prepare('UPDATE settings SET id_xml_type = 1, id_region= '.$id_region);
        $query->execute();
        $action = 'Время: '.date("F j, Y, g:i a").' =======> По предыдущим месяцам в '.$region.' завершена выгрузка по типу CANCEL_FAILURE;';
        $log_file = fopen("logs/log_pars_new.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
    }
}


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

function xml44($XMLarray, $pdo, $id_region){
    foreach($XMLarray as $xml){
        //echo $xml;
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
                $export = simplexml_load_file("./SimpleXML/xml.xml");
            }
            catch(Exception $e){
                $action = $e->getMessage();
                $log_file = fopen("logs/log_pars_new_exception.txt","a+");
                fwrite($log_file,$action."\r\n\n");
                fclose($log_file);
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
            }

            foreach ( $export->children() as $child ){
                $id = $child->id;
                $purchaseNumber = $child->purchaseNumber;
                $docPublishDate = $child->docPublishDate;
                $href = $child->href;
                $purchaseObjectInfo = checked_value($child->purchaseObjectInfo);
            }

            if ( $id == '' ){
                $id = 0;
            }

            $query = $pdo->prepare('SELECT count(id_tender) FROM tender WHERE id_xml = "'.$id.'" AND id_region = '.$id_region);
            $query->execute();

            $countId = $query->fetchColumn();
            if ( $countId == 0 ){
                //$query = $pdo->prepare('INSERT INTO tender SET id_region='.$id_region.', id_xml = '.$id.', purchase_number = "'.$purchaseNumber.'", 
                //doc_publish_date = "'.$docPublishDate.'", href = "'.$href.'", purchase_object_info = '.stripslashes($purchaseObjectInfo));
                $query = $pdo->prepare('INSERT INTO tender ( id_region, id_xml, purchase_number, doc_publish_date, href, purchase_object_info, type_fz ) 
									VALUES ( :id_region, :id_xml, :purchase_number, :doc_publish_date, :href, :purchase_object_info, 44 ) ');
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

                $organizer_reg_num = checked_value($child->purchaseResponsible->responsibleOrg->regNum);
                $organizer_full_name = checked_value($child->purchaseResponsible->responsibleOrg->fullName);
                $organizer_post_address = checked_value($child->purchaseResponsible->responsibleOrg->postAddress);
                $organizer_fact_address = checked_value($child->purchaseResponsible->responsibleOrg->factAddress);
                $organizer_inn = checked_value($child->purchaseResponsible->responsibleOrg->INN);
                $organizer_kpp = checked_value($child->purchaseResponsible->responsibleOrg->KPP);
                $organizer_responsible_role = checked_value($child->purchaseResponsible->responsibleRole);
                $organizer_last_name = $child->purchaseResponsible->responsibleInfo->contactPerson->lastName;
                $organizer_first_name = $child->purchaseResponsible->responsibleInfo->contactPerson->firstName;
                $organizer_middle_name = $child->purchaseResponsible->responsibleInfo->contactPerson->middleName;
                $organizer_contact = $organizer_last_name." ".$organizer_first_name." ".$organizer_middle_name;
                $organizer_email = checked_value($child->purchaseResponsible->responsibleInfo->contactEMail);
                $organizer_phone = checked_value($child->purchaseResponsible->responsibleInfo->contactPhone);
                $organizer_fax = checked_value($child->purchaseResponsible->responsibleInfo->contactFax);

                if ( $organizer_reg_num <> '' ){
                    $query = $pdo->prepare('SELECT id_organizer FROM organizer WHERE reg_num = :organizer_reg_num');
                    $query->bindParam(':organizer_reg_num', $organizer_reg_num);
                    $query->execute();
                    $id_organizer = $query->fetchColumn();
                    if( !$id_organizer){
                        //$query = $pdo->prepare('INSERT INTO organizer SET reg_num = "'.$organizer_reg_num.'", full_name = '.$organizer_full_name.', 
                        //post_address = '.$organizer_post_address.', fact_address = '.$organizer_fact_address.', inn = "'.$organizer_inn.'",
                        //kpp = "'.$organizer_kpp.'", responsible_role = "'.$organizer_responsible_role.'", contact_person = "'.$organizer_contact.'",
                        //contact_email = "'.$organizer_email.'", contact_phone = "'.$organizer_phone.'", contact_fax = "'.$organizer_fax.'"');
                        $query = $pdo->prepare('INSERT INTO organizer ( reg_num, full_name, post_address, 
																	fact_address, inn, kpp, responsible_role, 
																	contact_person, contact_email, contact_phone, contact_fax ) 
											VALUES ( :reg_num, :full_name, :post_address, 
																	:fact_address, :inn, :kpp, :responsible_role, 
																	:contact_person, :contact_email, :contact_phone, :contact_fax )');
                        $query->bindParam(':reg_num', $organizer_reg_num);
                        $query->bindParam(':full_name', $organizer_full_name);
                        $query->bindParam(':post_address', $organizer_post_address);
                        $query->bindParam(':fact_address', $organizer_fact_address);
                        $query->bindParam(':inn', $organizer_inn);
                        $query->bindParam(':kpp', $organizer_kpp);
                        $query->bindParam(':responsible_role', $organizer_responsible_role);
                        $query->bindParam(':contact_person', $organizer_contact);
                        $query->bindParam(':contact_email', $organizer_email);
                        $query->bindParam(':contact_phone', $organizer_phone);
                        $query->bindParam(':contact_fax', $organizer_fax);
                        $query->execute();

                        $query = $pdo->prepare('SELECT id_organizer FROM organizer WHERE reg_num = :organizer_reg_num');
                        $query->bindParam(':organizer_reg_num', $organizer_reg_num);
                        $query->execute();
                        $id_organizer = $query->fetchColumn();
                    }
                }


                $placingWay_code = checked_value($child->placingWay->code);
                $placingWay_name = checked_value($child->placingWay->name);

                if ( $placingWay_code <> '' ){
                    $query = $pdo->prepare('SELECT id_placing_way FROM placing_way WHERE code = :placingWay_code');
                    $query->bindParam(':placingWay_code', $placingWay_code);
                    $query->execute();
                    $id_placing_way = $query->fetchColumn();
                    if( !$id_placing_way ){
                        //$query = $pdo->prepare('INSERT INTO placing_way SET code = "'.$placingWay_code.'", name = "'.$placingWay_name.'"');
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

                $ETP_code = checked_value($child->ETP->code);
                $ETP_name = checked_value($child->ETP->name);
                $ETP_url = checked_value($child->ETP->url);

                $id_etp = 0;
                if ( $ETP_code <> '' ){
                    $query = $pdo->prepare('SELECT id_etp FROM etp WHERE code = :code');
                    $query->bindParam(':code', $ETP_code);
                    $query->execute();
                    $id_etp = $query->fetchColumn();
                    if( !$id_etp ){
                        //$query = $pdo->prepare('INSERT INTO etp SET code = "'.$ETP_code.'", name = "'.$ETP_name.'", url = "'.$ETP_url.'"');
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

                if ( $child->attachments->attachment ){
                    foreach ($child->attachments->attachment as $XMLattachment){
                        $attach_name = '';
                        $attach_url = '';
                        $attach_description = '';
                        $attach_name = $XMLattachment->fileName;
                        $attach_description = $XMLattachment->docDescription;
                        $attach_url = $XMLattachment->url;
                        if ( $attach_name <> '' ){
                            //$query = $pdo->prepare('INSERT INTO attachment SET id_tender = '.$id_tender.', file_name = '.$attach_name.', 
                            //url = '.$attach_url.', description = '.$attach_description);
                            $query = $pdo->prepare('INSERT INTO attachment ( id_tender, file_name, url, description ) VALUES ( :id_tender, :file_name, :url, :description )');
                            $query->bindParam(':id_tender', $id_tender);
                            $query->bindParam(':file_name', $attach_name);
                            $query->bindParam(':url', $attach_url);
                            $query->bindParam(':description', $attach_description);
                            $query->execute();
                        }
                    }
                }


                if (isset($child->procedureInfo->collecting->endDate))
                    $end_date = substr($child->procedureInfo->collecting->endDate, 0, 10);
                else
                    $end_date = '';
                if (isset($child->procedureInfo->scoring->date))
                    $scoring_date = substr($child->procedureInfo->scoring->date, 0, 10);
                else
                    $scoring_date = '';
                if (isset($child->procedureInfo->bidding->date))
                    $bidding_date = substr($child->procedureInfo->bidding->date, 0, 10);
                else
                    $bidding_date = '';

                $query = $pdo->prepare('UPDATE tender SET id_organizer = :id_organizer, id_placing_way = :id_placing_way, id_etp = :id_etp,
									end_date = :end_date, scoring_date = :scoring_date, bidding_date = :bidding_date WHERE id_tender = :id_tender');

                //$query = $pdo->prepare('UPDATE tender ( id_organizer, id_placing_way, id_etp, end_date, scoring_date, bidding_date ) VALUES ( :id_organizer, :id_placing_way, :id_etp, :end_date, :scoring_date, :bidding_date ) WHERE id_tender = :id_tender');						
                $query->bindParam(':id_organizer', $id_organizer);
                $query->bindParam(':id_placing_way', $id_placing_way);
                $query->bindParam(':id_etp', $id_etp);
                $query->bindParam(':end_date', $end_date);
                $query->bindParam(':scoring_date', $scoring_date);
                $query->bindParam(':bidding_date', $bidding_date);
                $query->bindParam(':id_tender', $id_tender);
                $query->execute();

                if (isset($child->lots))
                    $lots = $child->lots->lot;
                else
                    $lots = $child->lot;

                $lotNumber = 1;
                $lot_max_price = '';
                $lot_currency = '';
                $lot_finance_source = '';

                foreach ($lots as $lot){
                    $lot_max_price = $lot->maxPrice;
                    $lot_currency = checked_value($lot->currency->name);
                    $lot_finance_source = checked_value($lot->financeSource);


                    $query = $pdo->prepare('INSERT INTO lot SET id_tender = '.$id_tender.', lot_number = '.$lotNumber.', 
										max_price = '.$lot_max_price.', currency = "'.$lot_currency.'", finance_source = '.$lot_finance_source);
                    $query = $pdo->prepare('INSERT INTO lot ( id_tender, lot_number, max_price, currency, finance_source ) VALUES ( :id_tender, :lot_number, :max_price, :currency, :finance_source )');
                    $query->bindParam(':id_tender', $id_tender);
                    $query->bindParam(':lot_number', $lotNumber);
                    $query->bindParam(':max_price', $lot_max_price);
                    $query->bindParam(':currency', $lot_currency);
                    $query->bindParam(':finance_source', $lot_finance_source);
                    $query->execute();

                    $query = $pdo->prepare('SELECT id_lot FROM lot WHERE id_tender = :id_tender AND lot_number = :lot_number AND max_price = :max_price');
                    $query->bindParam(':id_tender', $id_tender);
                    $query->bindParam(':lot_number', $lotNumber);
                    $query->bindParam(':max_price', $lot_max_price);
                    $query->execute();
                    $id_lot = $query->fetchColumn();

                    foreach ( $lot->customerRequirements->customerRequirement as $customerRequirement ){
                        if (isset($customerRequirement->kladrPlaces->kladrPlace->kladr->fullName))
                            $kladr_place = $customerRequirement->kladrPlaces->kladrPlace->kladr->fullName;
                        else
                            $kladr_place = '';
                        if (isset($customerRequirement->kladrPlaces->kladrPlace->deliveryPlace))
                            $delivery_place = $customerRequirement->kladrPlaces->kladrPlace->deliveryPlace;
                        elseif (isset($customerRequirement->kladrPlace->deliveryPlace))
                            $delivery_place = $customerRequirement->kladrPlace->deliveryPlace;
                        else
                            $delivery_place = '';
                        $delivery_term = $customerRequirement->deliveryTerm;
                        if (isset($customerRequirement->applicationGuarantee->amount))
                            $application_guarantee_amount = $customerRequirement->applicationGuarantee->amount;
                        else
                            $application_guarantee_amount = 0;
                        if (isset($customerRequirement->contractGuarantee->amount))
                            $contract_guarantee_amount = $customerRequirement->contractGuarantee->amount;
                        else
                            $contract_guarantee_amount = 0;
                        if (isset($customerRequirement->applicationGuarantee->settlementAccount))
                            $application_settlement_account = $customerRequirement->applicationGuarantee->settlementAccount;
                        else
                            $application_settlement_account = '';
                        if (isset($customerRequirement->applicationGuarantee->personalAccount))
                            $application_personal_account = $customerRequirement->applicationGuarantee->personalAccount;
                        else
                            $application_personal_account = '';

                        $application_bik = $customerRequirement->applicationGuarantee->bik;
                        if ($application_bik == Null)
                            $application_bik = '';
                        $contract_settlement_account = $customerRequirement->contractGuarantee->settlementAccount;
                        if ($contract_settlement_account == Null)
                            $contract_settlement_account = '';
                        $contract_personal_account = $customerRequirement->contractGuarantee->personalAccount;
                        if ($contract_personal_account == Null)
                            $contract_personal_account = '';
                        $contract_bik = $customerRequirement->contractGuarantee->bik;
                        if ($contract_bik == Null)
                            $contract_bik = '';
                        $customer_regNum =  $customerRequirement->customer->regNum;
                        if ($customer_regNum == Null)
                            $customer_regNum = '';
                        $customer_full_name = $customerRequirement->customer->fullName;
                        if ($customer_full_name == Null)
                            $customer_full_name = '';
                        $customer_requirement_max_price = $customerRequirement->maxPrice;
                        if ($customer_requirement_max_price == Null)
                            $customer_requirement_max_price = '';

                        $id_customer = 0;
                        if ( $customer_regNum <> '' ){
                            $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                            $query->bindParam(':reg_num', $customer_regNum);
                            $query->execute();
                            $id_customer = $query->fetchColumn();
                            if( !$id_customer  ){
                                $query = $pdo->prepare('INSERT INTO customer SET reg_num = :reg_num, full_name = :full_name');
                                $query->bindParam(':reg_num', $customer_regNum);
                                $query->bindParam(':full_name', $customer_full_name);
                                $query->execute();

                                $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                                $query->bindParam(':reg_num', $customer_regNum);
                                $query->execute();
                                $id_customer = $query->fetchColumn();
                            }
                        }
                        //$query = $pdo->prepare('INSERT INTO customer_requirement SET id_lot = '.$id_lot.', id_customer = '.$id_customer.',
                        //kladr_place = '.$kladr_place.', delivery_place = '.$delivery_place.', delivery_term = '.$delivery_term.',
                        //application_guarantee_amount = "'.$application_guarantee_amount.'", application_settlement_account = "'.$application_settlement_account.'",
                        //application_personal_account = "'.$application_personal_account.'", application_bik = "'.$application_bik.'",
                        //contract_guarantee_amount = "'.$contract_guarantee_amount.'", contract_settlement_account = "'.$contract_settlement_account.'",
                        //contract_personal_account = "'.$contract_personal_account.'", contract_bik = "'.$contract_bik.'", 
                        //max_price = '.$customer_requirement_max_price);
                        $query = $pdo->prepare('INSERT INTO customer_requirement ( id_lot, id_customer, kladr_place, delivery_place, delivery_term,
											application_guarantee_amount, application_settlement_account, application_personal_account,
											application_bik, contract_guarantee_amount, contract_settlement_account, contract_personal_account,
											contract_bik, max_price) VALUES ( :id_lot, :id_customer, :kladr_place, :delivery_place, :delivery_term,
											:application_guarantee_amount, :application_settlement_account, :application_personal_account,
											:application_bik, :contract_guarantee_amount, :contract_settlement_account, :contract_personal_account,
											:contract_bik, :max_price )');
                        $query->bindParam(':id_lot', $id_lot);
                        $query->bindParam(':id_customer', $id_customer);
                        $query->bindParam(':kladr_place', $kladr_place);
                        $query->bindParam(':delivery_place', $delivery_place);
                        $query->bindParam(':delivery_term', $delivery_term);
                        $query->bindParam(':application_guarantee_amount', $application_guarantee_amount);
                        $query->bindParam(':application_settlement_account', $application_settlement_account);
                        $query->bindParam(':application_personal_account', $application_personal_account);
                        $query->bindParam(':application_bik', $application_bik);
                        $query->bindParam(':contract_guarantee_amount', $contract_guarantee_amount);
                        $query->bindParam(':contract_settlement_account', $contract_settlement_account);
                        $query->bindParam(':contract_personal_account', $contract_personal_account);
                        $query->bindParam(':contract_bik', $contract_bik);
                        $query->bindParam(':max_price', $customer_requirement_max_price);
                        $query->execute();
                    }

                    if (isset($lot->preferenses->preferense)){
                        foreach ( $lot->preferenses->preferense as $preferense ){
                            $preferense_name = $preferense->name;
                            $query = $pdo->prepare('INSERT INTO preferense ( id_lot, name )  VALUES ( :id_lot, :name )');
                            $query->bindParam(':id_lot', $id_lot);
                            $query->bindParam(':name', $preferense_name);
                            $query->execute();
                        }
                    }
                    if (isset($lot->requirements->requirement)){
                        foreach ( $lot->requirements->requirement as $requirement ){
                            $requirement_name = $requirement->name;
                            $requirement_content = $requirement->content;
                            $requirement_code = $requirement->code;

                            $query = $pdo->prepare('INSERT INTO requirement ( id_lot, name, content, code ) VALUES ( :id_lot, :name, :content, :code )');
                            $query->bindParam(':id_lot', $id_lot);
                            $query->bindParam(':name', $requirement_name);
                            $query->bindParam(':content', $requirement_content);
                            $query->bindParam(':code', $requirement_code);
                            $query->execute();
                        }
                    }

                    $restrict_info = $lot->restrictInfo;
                    $foreign_info = $lot->foreignInfo;
                    $query = $pdo->prepare('INSERT INTO restricts ( id_lot, foreign_info, info ) VALUES ( :id_lot, :foreign_info, :info )');
                    $query->bindParam(':id_lot', $id_lot);
                    $query->bindParam(':foreign_info', $foreign_info);
                    $query->bindParam(':info', $restrict_info);
                    $query->execute();

                    $okpd2_code = '';
                    $okpd_code = '';
                    if (isset($lot->purchaseObjects->purchaseObject)){
                        foreach ($lot->purchaseObjects->purchaseObject as $purchaseObject){
                            if (isset($purchaseObject->OKPD2->code))
                                $okpd2_code = $purchaseObject->OKPD2->code;
                            else
                                $okpd_code = $purchaseObject->OKPD->code;

                            if (isset($purchaseObject->OKPD2->name))
                                $okpd_name = $purchaseObject->OKPD2->name;
                            else
                                $okpd_name = $purchaseObject->OKPD->name;

                            $name = $purchaseObject->name;
                            if (isset($purchaseObject->quantity->value)){
                                $quantity_value = $purchaseObject->quantity->value;
                            }
                            else
                                $quantity_value = 0;
                            if (isset($purchaseObject->price)){
                                $price = $purchaseObject->price;
                            }
                            else
                                $price = 0;
                            if (isset($purchaseObject->OKEI->nationalCode))
                                $okei = $purchaseObject->OKEI->nationalCode;
                            else
                                $okei = '';
                            if (isset($purchaseObject->sum))
                                $sum = $purchaseObject->sum;
                            else
                                $sum = 0;

                            $okpd2_group_code = '';
                            $okpd2_group_level1_code = '';

                            if ($okpd2_code <> ''){
                                $okpd2_group_code = substr( $okpd2_code, 0, 2 );
                                $okpd2_group_level1_code = substr( $okpd2_code, strpos($okpd2_code, ".")+1, 1 );
                            }

                            if (isset($purchaseObject->pucustomerQuantities->customerQuantitie)){
                                foreach ($purchaseObject->pucustomerQuantities->customerQuantitie as $customerQuantitie){
                                    $customer_quantity_value = $customerQuantitie->quantitie;
                                    $cust_regNum = $customerQuantitie->customer->regNum;
                                    $cust_full_name = $customerQuantitie->fullName;

                                    if ( $customer_regNum <> '' ){
                                        $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                                        $query->bindParam(':reg_num', $cust_regNum);
                                        $query->execute();
                                        $id_customer = $query->fetchColumn();
                                        if( !$id_customer  ){
                                            $query = $pdo->prepare('INSERT INTO customer SET reg_num = :reg_num, full_name = :full_name');
                                            $query->bindParam(':reg_num', $cust_regNum);
                                            $query->bindParam(':full_name', $cust_full_name);
                                            $query->execute();

                                            $query = $pdo->prepare('SELECT id_customer FROM customer WHERE reg_num = :reg_num');
                                            $query->bindParam(':reg_num', $cust_regNum);
                                            $query->execute();
                                            $id_customer = $query->fetchColumn();
                                        }
                                    }

                                    //$query = $pdo->prepare('INSERT INTO purchase_object SET id_lot = '.$id_lot.', id_customer = '.$id_customer.', okpd2_code = "'.$okpd2_code.'",
                                    //okpd2_group_code = '.$okpd2_group_code.', okpd2_group_level1_code = '.$okpd2_group_level1_code.',	
                                    //okpd_code = "'.$okpd_code.'", okpd_name = '.$okpd_name.', name = '.$name.',
                                    //quantity_value = '.$quantity_value.', price = '.$price.',
                                    //okei = "'.$okei.'", sum = '.$sum.', customer_quantity_value = '.$customer_quantity_value);		
                                    $query = $pdo->prepare('INSERT INTO purchase_object ( id_lot, id_customer ,okpd2_code, okpd2_group_code,
														okpd2_group_level1_code, okpd_code, okpd_name, name, quantity_value, price, 
														okei, sum, customer_quantity_value ) VALUES ( :id_lot, :id_customer ,:okpd2_code, :okpd2_group_code,
														:okpd2_group_level1_code, :okpd_code, :okpd_name, :name, :quantity_value, :price, 
														:okei, :sum, :customer_quantity_value )');
                                    $query->bindParam(':id_lot', $id_lot);
                                    $query->bindParam(':id_customer', $id_customer);
                                    $query->bindParam(':okpd2_code', $okpd2_code);
                                    $query->bindParam(':okpd2_group_code', $okpd2_group_code);
                                    $query->bindParam(':okpd2_group_level1_code', $okpd2_group_level1_code);
                                    $query->bindParam(':okpd_code', $okpd_code);
                                    $query->bindParam(':okpd_name', $okpd_name);
                                    $query->bindParam(':name', $name);
                                    $query->bindParam(':quantity_value', $quantity_value);
                                    $query->bindParam(':price', $price);
                                    $query->bindParam(':okei',$okei);
                                    $query->bindParam(':sum', $sum);
                                    $query->bindParam(':customer_quantity_value', $customer_quantity_value);
                                    $query->execute();
                                }
                            }
                            else{
                                //$query = $pdo->prepare('INSERT INTO purchase_object SET id_lot = '.$id_lot.', id_customer = '.$id_customer.', okpd2_code = "'.$okpd2_code.'",
                                //okpd2_group_code = '.$okpd2_group_code.', okpd2_group_level1_code = '.$okpd2_group_level1_code.',	
                                //okpd_code = "'.$okpd_code.'", okpd_name = '.$okpd_name.', name = '.$name.',
                                //quantity_value = '.$quantity_value.', price = '.$price.',
                                //okei = "'.$okei.'", sum = '.$sum.', customer_quantity_value = '.$quantity_value);					
                                $query = $pdo->prepare('INSERT INTO purchase_object ( id_lot, id_customer ,okpd2_code, okpd2_group_code,
														okpd2_group_level1_code, okpd_code, okpd_name, name, quantity_value, price, 
														okei, sum, customer_quantity_value ) VALUES ( :id_lot, :id_customer ,:okpd2_code, :okpd2_group_code,
														:okpd2_group_level1_code, :okpd_code, :okpd_name, :name, :quantity_value, :price, 
														:okei, :sum, :quantity_value )');
                                $query->bindParam(':id_lot', $id_lot);
                                $query->bindParam(':id_customer', $id_customer);
                                $query->bindParam(':okpd2_code', $okpd2_code);
                                $query->bindParam(':okpd2_group_code', $okpd2_group_code);
                                $query->bindParam(':okpd2_group_level1_code', $okpd2_group_level1_code);
                                $query->bindParam(':okpd_code', $okpd_code);
                                $query->bindParam(':okpd_name', $okpd_name);
                                $query->bindParam(':name', $name);
                                $query->bindParam(':quantity_value', $quantity_value);
                                $query->bindParam(':price', $price);
                                $query->bindParam(':okei',$okei);
                                $query->bindParam(':sum', $sum);
                                $query->execute();
                            }
                        }
                    }
                    $lotNumber++;
                }
            }
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars_new_exception.txt","a+");
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
            $export = simplexml_load_file("./SimpleXML/xml.xml");

            foreach ( $export->children() as $child ){
                $purchaseNumber = $child->purchaseNumber;
            }

            $query = $pdo->prepare('UPDATE tender SET cancel = 1 WHERE purchase_number = :purchase_number');
            $query->bindParam(':purchase_number', $purchaseNumber);
            $query->execute();
        }
        catch(Exception $e){
            $action = $e->getMessage();
            $log_file = fopen("logs/log_pars_new_exception.txt","a+");
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
            $export = simplexml_load_file("./SimpleXML/xml.xml");

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
            $log_file = fopen("logs/log_pars_new_exception.txt","a+");
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

            $export = simplexml_load_file("./SimpleXML/xml.xml");

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
            $log_file = fopen("logs/log_pars_new_exception.txt","a+");
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
    $zip->extractTo('./xmls');
    $zip->close();
}

function deletearhiv(){
    if (file_exists('./xmls'))
        foreach (glob('./xmls/*.xml') as $file)
            unlink($file);
}

function checked_value( $value ){
    if(isset($value))
        $result = $value;
    else
        $result = '';
    return $result;
}

function getxml(){
    $filelist = array();
    $xml44 = array();
    $cancel = array();
    $sign = array();
    $prolongation = array();
    $clarification = array();
    $lotCancel = array();
    $cancelFailure = array();
    $placementResult = array();
    $dateChange = array();
    $otherXML = array();
    if ($handle = opendir("./xmls")) {
        while ($entry = readdir($handle)) {
            if (strpos($entry, "xml") <> 0){
                if (strpos($entry, "LotCancel") <> 0) {
                    $lotCancel[] = $entry;
                }
                elseif (strpos($entry, "Sign") <> 0) {
                    $sign[] = $entry;
                }
                elseif (strpos($entry, "Clarification") <> 0) {
                    $clarification[] = $entry;
                }
                elseif (strpos($entry, "DateChange") <> 0) {
                    $dateChange[] = $entry;
                }
                elseif (strpos($entry, "Prolongation") <> 0) {
                    $prolongation[] = $entry;
                }
                elseif (strpos($entry, "CancelFailure") <> 0) {
                    $cancelFailure[] = $entry;
                }
                elseif (strpos($entry, "PlacementResult") <> 0) {
                    $placementResult[] = $entry;
                }
                elseif (strpos($entry, "Cancel") <> 0) {
                    $cancel[] = $entry;
                }
                elseif (strpos($entry, "44_") <> 0) {
                    $xml44[] = $entry;
                }
                else
                    $otherXML[] = $entry;
            }
        }
        closedir($handle);
        $filelist['xml44'] = $xml44;
        $filelist['cancel'] = $cancel;
        $filelist['sign'] = $sign;
        $filelist['prolongation'] = $prolongation;
        $filelist['clarification'] = $clarification;
        $filelist['lotCancel'] = $lotCancel;
        $filelist['cancelFailure'] = $cancelFailure;
        $filelist['placementResult'] = $placementResult;
        $filelist['dateChange'] = $dateChange;
        $filelist['otherXML'] = $otherXML;
    }
    Return $filelist;
}

function DOMtoSimpleXML($xml){
    try{
        $file = ("./SimpleXML/xml.xml");
        $a = file_get_contents("./xmls/".$xml);
        if($a === false){
            throw new Exception('Невозможно открыть файл!');
            file_put_contents($file, '');
        }
        else{
            $xml_origin = str_replace("ns2:","", $a);
            $xml_origin =  str_replace("oos:","", $xml_origin);
            file_put_contents($file, $xml_origin);
        }
    }
    catch( Exception $e ){
        $action = $e->getMessage();
        $log_file = fopen("logs/log_pars_new_exception.txt","a+");
        fwrite($log_file,$action."\r\n\n");
        fclose($log_file);
        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
    }
}

$pdo = null;
?>