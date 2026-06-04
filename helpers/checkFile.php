<?php
    //check if file provided
function fileProvided($file) {
    return $file['error'] !== UPLOAD_ERR_NO_FILE;
}

//check if correct format
function checkFile($file) {
    //check if image provide
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) return false;

    //checks if correct image type
    $mime = $imageInfo['mime'];
    return $mime === 'image/png' || $mime === 'image/jpeg';
}

//check if file is in the array and if correct type
function checkFileInArray($file, $index) {
    //check if image
    $imageInfo = getimagesize($file['tmp_name'][$index]);
    if ($imageInfo === false) return false;

    //checks if correct image type
    $mime = $imageInfo['mime'];
    return $mime === 'image/png' || $mime === 'image/jpeg';
}

//gets file path if no error
function getFilePath($file) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return "";
    return $file['tmp_name'];
}
?>