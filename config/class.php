<?php 
require "config.php";
/**
 * 
 */
class APICONNECT
{ 
  private $db;
  public $Apitoken;
  public $ApiKey;
  public $ApiSecure;
  public $status;
  public $check_code;
  public $payment_type;
  public $name;
  public $amount;
  public $value;
  public $link_key;

  function __construct($apitoken, $apikey, $apisecure)
  {
    $this->Apitoken = $apitoken;
    $this->ApiKey = $apikey;
    $this->ApiSecure = $apisecure;
    $this->db = $GLOBALS["db"];
  }

  public function receive()
  {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if ($data->status == "1") {
      $this->status = "Verified";
    }else if ($data->status == "0") {
      $this->status = "Rejected";
    }else{
      $this->status = "Unknow";
    }

    $this->check_code   = $data->check_code;
    $this->payment_type = $data->payment_type;
    $this->name         = $data->name;
    $this->amount       = $data->amount;
    $this->value        = $data->value;
    $this->link_key     = $data->link_key;
    return true;
  }

  public function sendResponse($type)
  { 
    if ($type == "resp_ok") {
      return json_encode(array(
        "status"  => "200",
        "response" => array(
          "text"  => array(
            "tr" => "Onay başarılı bir şekilde gerçekleşti!",
            "en"=> "Confirmation successful!",
            "cn" => "确认成功！"
          ),
          "send" => "Verify"
        )));
    }else if ($type == "resp_fail") {
      return json_encode(array(
        "status"  => "500",
        "response" => array(
          "text"  => array(
            "tr" => "Onay gerçekleştirilemedi! Başarısız.",
            "en"=> "Confirmation failed! Unsuccessful.",
            "cn" => "确认失败！ 不成功。"
          ),
          "send" => "Not Verify"
        )));
    }else if ($type == "PDOException") {
     return json_encode(array(
      "status"  => "500",
      "response" => array(
        "text"  => array(
          "tr" => $msg." ERRcode : ".$code,
          "en" => $msg." ERRcode : ".$code,
          "cn" => $msg." ERRcode : ".$code
        ),
        "send" => "PDOException"
      )
    ));
   }else if ($type == "resp_reject_ok") {
    return json_encode(array(
      "status"  => "200",
      "response" => array(
        "text"  => array(
          "tr" => "Reddetme işlemi başarılı bir şekilde gerçekleşti!",
          "en" => "The rejection was successful!",
          "cn" => "拒绝成功！"
        ),
        "send" => "Rejection"
      )
    ));
  }else if ($type == "resp_reject_fail") {
    return json_encode(array(
      "status"  => "500",
      "response" => array(
        "text"  => array(
          "tr" => "Reddetme işlemi gerçekleştirilemedi! Başarısız.",
          "en" => "Failed to reject! Unsuccessful.",
          "cn" => "拒绝失败！ 不成功。"
        ),
        "send" => "Not Rejection"
      )
    ));
  }else if ($type == "parse_error") {
    return json_encode(array(
      "status"  => "500",
      "response" => array(
        "text"  => array(
          "tr" => "Check kodu ayrıştırılamadı!",
          "en" => "Failed to parse hash code!",
          "cn" => "无法解析哈希码！"
        ),
        "send" => "Not Verify"  
      )
    ));
  }else if ($type == "status_error") {
    return json_encode(array(
      "status"  => "500",
      "response" => array(
        "text"  => array(
          "tr" => "Bu hata için lütfen site yetkililerine haber veriniz.",
          "en" => "Please notify the site authorities of this error.",
          "cn" => "请将此错误通知站点当局。"
        ),
        "send" => "Not Verify"  
      )
    ));
  } 

} 

public function verify()
{
  if ($this->check_code == hash("sha256", $this->Apitoken . "|" . $this->ApiSecure . "|" . $this->value . "|" . $this->amount . "|true")) {
    return true;
  }else{
    return false;
  }

}

public function dbquery($table, $title, $value) { 

  $comand ='<?php $oku = $this->db->prepare("SELECT * FROM ' . $table . ' WHERE ';
  $comand2 ='$okuq = $oku->execute(array(';
  '';
  $cook = 0;
  for ($i=0; $i < count($title); $i++) {
    $cook++; 
    if ((count($title)-1) <= $i) {
      $comand .= $title[$i] . "= :" . $title[$i]. "\");";
      $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '"'.")); \$oku = \$oku->fetchAll(PDO::FETCH_ASSOC);?>";
    }else{
      $comand .= $title[$i] . "= :" . $title[$i] . " and " ;
      $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '",';
    }
  }
  if ($cook == count($title)) {
    eval('?> ' . $comand.$comand2 . '<?php ');
    return json_encode($oku);
  }
}

  public function dbinsert($table, $title, $value) { // DB İnsert

    $comand ='<?php $oku = $this->db->prepare("INSERT INTO ' . $table . ' SET ';
    $comand2 ='$okuq = $oku->execute(array(';
    $cook = 0;
    for ($i=0; $i < count($title); $i++) {
      $cook++; 
      if ((count($title)-1) <= $i) {
        $comand .= $title[$i] . "= :" . $title[$i]. "\");";
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '"'.")); ?>";
      }else{
        $comand .= $title[$i] . "= :" . $title[$i] . " , " ;
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '",';
      }
    }
    if ($cook == count($title)) {
      eval('?> ' . $comand.$comand2 . '<?php ');
      if ($okuq) {
        return "1";
      }      
    }
  }

  public function dbupdate($table, $title, $value, $where,$wvalue) { // DB update

    $comand ='<?php $oku = $this->db->prepare("UPDATE ' . $table . ' SET ';
    $comand2 ='$okuq = $oku->execute(array(';
    $cook = 0;
    for ($i=0; $i < count($title); $i++) { // value set update
      $cook++; 
      if ((count($title)-1) <= $i) {
        $comand .= $title[$i] . "= :" . $title[$i]. " WHERE ";
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '",';
      }else{
        $comand .= $title[$i] . "= :" . $title[$i] . " , " ;
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '",';
      }
    }
    $cook2 = 0;
    for ($i=0; $i < count($where); $i++) { // where update set
      $cook2++; 
      if ((count($where)-1) <= $i) {
        $comand .= $where[$i] . "= :" . $where[$i]."\");";
        $comand2 .= '"' . $where[$i]. '"' . " => ". '"' . $wvalue[$i]. '"'.")); ?>";
      }else{
        $comand .= $where[$i] . "= :" . $where[$i] . " , " ;
        $comand2 .= '"' . $where[$i]. '"' . " => ". '"' . $wvalue[$i]. '",';
      }
    }
    if ($cook == count($title)) {
      eval('?> ' . $comand.$comand2 . '<?php ');
      if ($okuq) {
        return "1";
      }  
      return $comand.$comand2;
    }
  }

  public function dbdelete($table, $title, $value) { // DB Delete

    $comand ='<?php $oku = $this->db->prepare("DELETE FROM ' . $table . ' WHERE ';
    $comand2 ='$okuq = $oku->execute(array(';
    $cook = 0;
    for ($i=0; $i < count($title); $i++) {
      $cook++; 
      if ((count($title)-1) <= $i) {
        $comand .= $title[$i] . "= :" . $title[$i]. "\");";
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '"'.")); ?>";
      }else{
        $comand .= $title[$i] . "= :" . $title[$i] . " , " ;
        $comand2 .= '"' . $title[$i]. '"' . " => ". '"' . $value[$i]. '",';
      }
    }
    if ($cook == count($title)) {
      eval('?> ' . $comand.$comand2 . '<?php ');
      if ($okuq) {
        return "1";
      }      
    }
  }


}

?>