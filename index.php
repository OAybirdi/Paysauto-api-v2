<?php 

require "config/class.php"; 

$PaysautoClass = new APICONNECT("Your api token","Your api key","Your api secret");

$Receive = $PaysautoClass->receive();

$VerifyHash = $PaysautoClass->verify();



if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if ($VerifyHash) {

    /********* Details *********/

    $status = $PaysautoClass->status;             // Onay yada reddetme durumunu kontrol eder.
    $payment_type = $PaysautoClass->payment_type; // Hangi bankadan ödeme yapıldı bilgisini verir.
    $name = $PaysautoClass->name;                 // Ödeme yapan kişinin ad soyad bilgisini verir.
    $amount = $PaysautoClass->amount;             // Gelen ödeme tutar bilgisini 'float' tipinde verir.
    $value = $PaysautoClass->value;               // Sipariş numarası yada bakiye yükleyecek kullanıcının id'si.
    $link_key = $PaysautoClass->link_key;         // Ödeme yapan kullanıcının ödeme yaparken girmesi gereken benzersiz kod.



    if ( $status == "Verified") { // Ödeme kabul durumunda alttaki kod çalışır.
      // Burada kullanıcı bakiye ekleme yada sipariş onaylarınızı yapabilirsiniz.
      try { 
        //Aşağıdaki fonksiyon örnektir düzenleyebilirsiniz
       $check = $PaysautoClass->dbupdate("siparisler", array('durum'), array("1"), array("siparis_no"),array($value));

       // Dikkat aşağıdaki kod değiştirilemez
       if ($check) {
        echo $PaysautoClass->sendResponse("resp_ok");
      }else{
        echo $PaysautoClass->sendResponse("resp_fail");
      }
    }catch(PDOException $e){
      echo $PaysautoClass->sendResponse("PDOException");
    }
    }else if ( $status == "Rejected") { // Ödeme reddetme durumunda alttaki kod çalışır.
      // Burada kullanıcının ödemesinin reddi yapılır.
      try {
        //Aşağıdaki fonksiyon örnektir düzenleyebilirsiniz
       $check = $PaysautoClass->dbupdate("siparisler", array('durum'), array("2"), array("siparis_no"),array($value));

        // Dikkat aşağıdaki kod değiştirilemez!
       if ($check) { 
        echo $PaysautoClass->sendResponse("resp_reject_ok");
      }else{
        echo $PaysautoClass->sendResponse("resp_reject_fail");
      }
    }catch(PDOException $e){
      echo $PaysautoClass->sendResponse("PDOException");
    }
    }else{ //  Dikkat aşağıdaki kod değiştirilemez!
     echo $PaysautoClass->sendResponse("status_error");
   }
 }else{ // Dikkat aşağıdaki kod değiştirilemez!
  echo $PaysautoClass->sendResponse("parse_error");
}
}

