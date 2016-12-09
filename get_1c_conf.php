<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 09.12.16
 * Time: 13:10
 */
function file_force_download($file) {
    if (file_exists($file)) {
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!
        if (ob_get_level()) {
            ob_end_clean();
        }
        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        // читаем файл и отправляем его пользователю
        readfile($file);
        exit;
    }
}
$filepath = $_SERVER['DOCUMENT_ROOT'] . '/distributives/tenders/';
//echo($filepath);
foreach(glob($filepath . '*.zip') as $file) {

// далее получаем последний добавленный/измененный файл

    $LastModified[] = filemtime($file); // массив файлов со временем изменения файла

    $FileName[] = $file; // массив всех файлов

}

// Сортируем массив с файлами по дате изменения

$files = array_multisort($LastModified, SORT_NUMERIC, SORT_ASC, $FileName);
$lastIndex = count($LastModified) - 1;

// И вот он наш последний добавленный или измененный файл

$LastModifiedFile =  $FileName[$lastIndex];

file_force_download($LastModifiedFile);