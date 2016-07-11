<?php
require 'config.php';
require 'rb.php';

function fin($msg) {
  echo '<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><script>alert("'.$msg.'");</script></body></html>';
  exit();
}

function check_ws($sid) {
  @file_get_contents('http://www.im.ntu.edu.tw/~'.$sid.'/');
  if(strpos($http_response_header[0], '403') !== false || strpos($http_response_header[0], '200') !== false) {
    return true;
  }
  return false;
}

if(!isset($_POST['user']) || !isset($_POST['sid'])) {
  fin('請填寫所有欄位唷～');
}

if(!preg_match("/^[a-z]\d{8}$/", $_POST['sid'])) {
  fin('學號格式錯誤，請使用小寫半形字元');
}

if(!preg_match("/^[a-z]\d{2}7[02]5\d{3}$/", $_POST['sid'])) {
  if(!ws_check($_POST['sid'])) {
    fin('轉系或雙主修同學，請先使用 SSH 登入系上工作站，並在自己的家目錄執行 mkdir public_html 命令後申請，方能自動驗證身份');
  }
}

if(!preg_match("/^[a-z0-9-_.]{3,60}$/", $_POST['user'])) {
  fin('帳號格式錯誤，限用小寫英文、數字及 - _ .');
}

try {

  R::setup( 'mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);

  if(R::findOne('record', ' user LIKE ? ', [$_POST['user']])) {
    fin('帳號已經有人用過囉，請使用其他名稱');
  }

  if(R::findOne('record', ' sid LIKE ? ', [$_POST['sid']])) {
    fin('你已經註冊過囉，一人限申請一次');
  }


  $rec = R::dispense('record');
  $rec['user'] = $_POST['user'];
  $rec['sid'] = $_POST['sid'];
  $rec['status'] = 0;
  $rec['ctime'] = R::isoDateTime();

  R::store($rec);

  fin('申請完成！帳號開設需要些許時間，完成後密碼及登入方式將自動寄送到你的學號信箱');

  R::close();

} catch(Exception $e) {
  fin('發生錯誤，請稍後再試，或洽系學會資訊部');
}
