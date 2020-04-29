<?php
date_default_timezone_set('Asia/Jakarta');

if(!isset($_GET['corpid']) OR !isset($_GET['username']) OR !isset($_GET['password']) OR !isset($_GET['norek']) OR $_GET['corpid']=="" OR $_GET['username']=="" OR $_GET['password']=="" OR $_GET['norek']==""){
	die();
}

define('CORPID', $_GET['corpid']);
define('USERNAME', $_GET['username']);
define('PASSWORD', $_GET['password']);
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36');
define('COOKIE_FILE', 'cookie'.date('ymdhis').'.txt');
define('TOKEN_RESET_URL', 'http://mcm.bankmandiri.co.id/common/error/error_failed_token.jsp2');
define('LOGIN_FORM_URL', 'https://mcm2.bankmandiri.co.id/corporate/#!/login');
define('LOGIN_ACTION_URL', 'https://mcm.bankmandiri.co.id/corp/common/login.do?action=login');

define('NOREK', $_GET['norek']);
define('FILTER', 'credit');

$total_page	= array();
$TGL = preg_replace('/\D/', '', isset($_GET['tgl'])?$_GET['tgl']:0);
if($TGL=='' OR $TGL<0){
	$TGL = 0;
}
$TGL = 0-$TGL;

$noreks = explode(',',NOREK);

//GET sessionId
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, LOGIN_FORM_URL);
curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
curl_setopt($curl, CURLOPT_COOKIEFILE, COOKIE_FILE);
curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

if (curl_errno($curl)) die(curl_error($curl));

$document = new \DOMDocument('1.0', 'UTF-8');
$internalErrors = libxml_use_internal_errors(true);
$document->loadHTML($response);
libxml_use_internal_errors($internalErrors);
$inputs = $document->getElementsByTagName("input");

foreach ($inputs as $input) {
	if ($input->getAttribute("name") == "org.apache.struts.taglib.html.TOKEN") {
		$TOKEN = $input->getAttribute("value");
	}
}

if(!isset($TOKEN)){	
	die();
}

//LOGIN
$pass = sha1(md5(PASSWORD));

//Param Login
$postValues = array(
	'org.apache.struts.taglib.html.TOKEN' => $TOKEN,
    'corpId' => CORPID,
    'userName' => USERNAME,
	'passwordEncryption' => '',
    'language' => 'en_US',
    'password' => $pass,
	'sessionId' => '',
	'ssoFlag' => '',
	'eTax' => '#'
);
//execute Login
curl_setopt($curl, CURLOPT_URL, LOGIN_ACTION_URL);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postValues));
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_REFERER, LOGIN_FORM_URL);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
curl_exec($curl);
if(curl_errno($curl)){
    throw new Exception(curl_error($curl));
}

//Go To Page Menu
curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/common/login.do?action=menuRequest");
curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($curl);
 
//Go To Page Menu
curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/front/balanceinquiry.do?action=balanceRequest&menuCode=MNU_GCME_040100");
curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($curl);
echo $result;

$document2 = new DOMDocument('1.0', 'UTF-8');
$internalErrors = libxml_use_internal_errors(true);
$document2->loadHTML($result);
libxml_use_internal_errors($internalErrors);
$inputs2 = $document2->getElementsByTagName("input");

foreach ($inputs2 as $input2){
	if ($input2->getAttribute("name") == "corpId" AND $input2->getAttribute("value")!="") {
		$corpId = $input2->getAttribute("value");
	}
	elseif ($input2->getAttribute("name") == "corporateId" AND $input2->getAttribute("value")!="") {
		$corporateId = $input2->getAttribute("value");
	}
}

if(isset($corpId) AND isset($corporateId)){
	$data_request = array(
		"action"=>"showSingleAccountNoShowDate",
		"accountPick"=>"multiple",
		"selectby"=>"2",
		"hierarchy"=>"",
		"accountType"=>"",
		"accountBranch"=>"", 
		"menu"=>"search",
		"backFlag"=>"search",
		"firstFlag"=>"Y",
		"grouping"=>"1"
	);
		
	$data_request_post = array(
		"accountDisplay"=>"",
		"accountNo"=>"",
		"accountName"=>"",
		"currency"=>"",
		"type"=>"",
		"branch"=>"",
		"corpName"=>"",
		"typeNm"=>"",
		"alias"=>"",
		"radio1"=>"multiple",
		"radio4"=>"2",
		"organizationUnitCode"=>"",
		"organizationUnitName"=>"",
		"dropList2"=>"",
		"accountHierarchyCompany"=>"",
		"radio2"=>"1",
		"isShowDate"=>"N",
		"corpId"=>$corpId,
		"corporateId"=>$corporateId
	);

	curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/front/balanceinquiry.do?action=showSingleAccountNoShowDate&accountPick=multiple&selectby=2&hierarchy=%20&accountType=%20&accountBranch=%20&menu=search&backFlag=search&firstFlag=Y&grouping=1");
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data_request_post));
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result2 = curl_exec($curl);

	if(strpos($result2,"Total Asset Amount")!==FALSE){
		$rekap_saldo = $result2;
	}

	//Go To Page Menu
	curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/common/login.do?action=menuRequest");
	curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
	curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);

	//Go To Page Request
	curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/front/transactioninquiry.do?action=transactionByDateRequest&menuCode=MNU_GCME_040200");
	curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
	curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);

	if($result!="" AND !strpos($result,"Record not found") AND !strpos($result,"Account No Not Found") AND !strpos($result,"Already Time Out") AND !strpos($result,"Record not found") AND !strpos($result,"en_US.MW")){
		for($x=$TGL;$x>=$TGL;$x--){
			foreach($noreks as $norek){
				$total_page[$norek] = 0;
				if(isset($_GET['day']) AND is_numeric($_GET['day'])){
					$x = $_GET['day'];
				}
				$page = 1;
				if(isset($_GET['page']) AND is_numeric($_GET['page'])){
					$page = $_GET['page'];
				}
				
				if($norek==''){
					continue;
				}
				
				$array_type = array(array('D','D'),array('S','S'));
				$result = '';
				$max_at = count($array_type)-1;
				for($at=0;$at<=$max_at;$at++){
					$accountType 		= $array_type[$at][0];
					$accountTypeCode 	= $array_type[$at][1];
					
					$results = '';
					//Go To Page Result Request
					$day1 = date('d', strtotime("$x days", strtotime(date('Y-m-d'))));
					$day2 = date('d', strtotime("$x days", strtotime(date('Y-m-d'))));
					$mon1 = date('n', strtotime("$x days", strtotime(date('Y-m-d'))));
					$mon2 = date('n', strtotime("$x days", strtotime(date('Y-m-d'))));
					$year1= date('Y', strtotime("$x days", strtotime(date('Y-m-d'))));
					$year2= date('Y', strtotime("$x days", strtotime(date('Y-m-d'))));
					
					
					$data_request = array(
						"action"=>"doCheckValidityAndShow",
						"day1"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"mon1"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"year1"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"day2"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"mon2"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"year2"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"accountNumber"=>$norek,
						"type"=>"show",
						"accountNumber"=>$norek,
						"accountType"=>$accountType,
						"frOrganizationUnitNm"=>"KCP Tangerang Graha Karno's",
						"currDisplay"=>"IDR",
						"day1"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"mon1"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"year1"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"day2"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"mon2"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"year2"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"trxFilter"=>"%"
					);
					
					$data_request_post = array(
						"transferDateDay1"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transferDateMonth1"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transferDateYear1"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transferDateDay2"=>date('d', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transferDateMonth2"=>date('n', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transferDateYear2"=>date('Y', strtotime("$x days", strtotime(date('Y-m-d')))),
						"transactionType"=>"%",
						"accountType"=>'S',
						"accountDisplay"=>$norek,
						"accountNumber"=>$norek,
						"accountNm"=>"AKSI CEPAT TANGGAP",
						"currDisplay"=>"IDR",
						"curr"=>"IDR",
						"frOrganizationUnit"=>"10117",
						"accountTypeCode"=>$accountTypeCode,
						"accountHierarchy"=>"",
						"customFile"=>"",
						"frOrganizationUnitNm"=>"",
						"screenState"=>"TRX_DATE",
						"accountHierarchy"=>"",
						"archiveFlag"=>"N",
						"checkDate"=>"Y",
						"timeLength"=>"31",
						"showTimeLength"=>"31"
					);

					curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/front/transactioninquiry.do?".http_build_query($data_request));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data_request_post));
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
					curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($curl);
					
					if($result!="" AND !strpos($result,"Record not found") AND !strpos($result,"Account No Not Found") AND !strpos($result,"Already Time Out") AND !strpos($result,"Record not found") AND !strpos($result,"en_US.MW")){
						$at = $max_at;
					}
				}
				
				if($result!="" AND !strpos($result,"Record not found") AND !strpos($result,"Account No Not Found") AND !strpos($result,"Already Time Out") AND !strpos($result,"Record not found") AND !strpos($result,"en_US.MW")){
					$results = $result;
					$total_page[$norek] = 1;
					$dom = new DOMDocument();
					$dom->loadHTML($result);
					$xp = new DOMXpath($dom);
					
					$totalPage = '';
					$nodes = $xp->query('//input[@name="totalPage"]');
					if(!is_null($nodes->item(0)))
					{				
						$node = $nodes->item(0);
						if(!is_null($node->getAttribute('value')))
						{
							$totalPage = preg_replace('/\D/', '',$node->getAttribute('value'));
						}
					}
					
					$accountType = '';
					$nodes2 = $xp->query('//input[@name="accountType"]');
					if(!is_null($nodes2->item(0))){				
						$node2 = $nodes2->item(0);
						if(!is_null($node2->getAttribute('value'))){
							$accountType = $node2->getAttribute('value');
						}
					}
					
					$accountTypeCode = '';
					$nodes3 = $xp->query('//input[@name="accountTypeCode"]');
					if(!is_null($nodes3->item(0))){				
						$node3 = $nodes3->item(0);
						if(!is_null($node3->getAttribute('value'))){
							$accountTypeCode = $node3->getAttribute('value');
						}
					}
					
					if($totalPage!='' AND is_numeric($totalPage) AND $totalPage>=1 AND $accountType!='' AND $accountTypeCode!=''){
						$total_page[$norek] = $totalPage;
					}
					
					if($results!=''){
						$no_line = 1;
						$no_urut = 1;
						$document2 = new DOMDocument();
						$document2->loadHTML($results);
						$inputs3 = $document2->getElementsByTagName("tr");
						
						if(isset($inputs3->length) AND $inputs3->length>0){
							for ($i3 = 0; $i3 < $inputs3->length; $i3++){
								$cols3 = $inputs3->item($i3)->getElementsbyTagName("td");
													
								$DateTime = '';
								$ValueDate = '';
								$Description = '';
								$ReferenceNo = '';
								$Debit = 0;
								$Credit = 0;
								$Saldo = 0;
								
								if(isset($cols3->length) AND $cols3->length>0){
									for ($j2 = 0; $j2 < $cols3->length; $j2++){
										if(isset($cols3->item($j2)->nodeValue)){
											$value3 = trim($cols3->item($j2)->nodeValue);
											if($j2==0){
												$DateTime = $value3;
											}
											elseif($j2==1){
												$ValueDate = $value3;
											}
											elseif($j2==2){
												$Description = $value3;
											}
											elseif($j2==3){
												$ReferenceNo = $value3;
											}
											elseif($j2==4){
												$Debit = $value3;
											}
											elseif($j2==5){
												$Credit = $value3;
											}
											elseif($j2==6){
												$Saldo = $value3;
											}
										}					
										$no_line++;
									}
								
									if($DateTime!='' AND $ValueDate!='' AND $Debit!='' AND $Credit!='' AND ($Debit=='0.00' OR $Credit=='0.00')){
										$data_respon[$norek][$no_urut]['DateTime'] 		= $DateTime;
										$data_respon[$norek][$no_urut]['ValueDate'] 	= $ValueDate;
										$data_respon[$norek][$no_urut]['Description'] 	= str_replace("'",'',$Description);
										$data_respon[$norek][$no_urut]['ReferenceNo'] 	= $ReferenceNo;
										$data_respon[$norek][$no_urut]['Debit'] 		= $Debit;
										$data_respon[$norek][$no_urut]['Credit'] 		= $Credit;
										$data_respon[$norek][$no_urut]['Saldo'] 		= $Saldo;
										
										$no_urut++;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

//execute logout
curl_setopt($curl, CURLOPT_URL, "https://mcm.bankmandiri.co.id/corp/common/login.do?action=logout");
curl_setopt($curl, CURLOPT_COOKIEJAR, COOKIE_FILE);
curl_setopt($curl, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_exec($curl);

//parsing field inquiry detail
if(isset($rekap_saldo)){
	$no_line = 1;
	$document2 = new DOMDocument('1.0', 'UTF-8');
	$internalErrors = libxml_use_internal_errors(true);
	
	$document2->loadHTML($rekap_saldo);
	libxml_use_internal_errors($internalErrors);
	$inputs3 = $document2->getElementsByTagName("tr");
		
	if(isset($inputs3->length) AND $inputs3->length>0){
		for ($i3 = 0; $i3 < $inputs3->length; $i3++){
			$cols3 = $inputs3->item($i3)->getElementsbyTagName("td");
	
			if(isset($cols3->length) AND $cols3->length>0){
				$data_col = array();
				$col = 1;
				for ($j2 = 0; $j2 < $cols3->length; $j2++){
					if(isset($cols3->item($j2)->nodeValue)){										
						$data_col[$col] = trim($cols3->item($j2)->nodeValue);
					}
					$col++;
				}
				
				if(!isset($tanggal) AND isset($data_col[1]) AND $data_col[1]=="Date"){
					$date = trim(str_replace(": ","",$data_col[2]));
					$arr_mont = array("SEPTEMBER"=>"09","OKTOBER"=>"10","NOVEMBER"=>"11","DECEMBER"=>"12");
					$tgl = substr($date,0,2);
					$month = $arr_mont[strtoupper(trim(substr($date,3,(strlen($date)-6))))];
					$thn = "20".substr($date,-2);
					$tanggal = $thn."-".$month."-".$tgl;
				}
				
				if(!isset($time) AND isset($data_col[1]) AND $data_col[1]=="Time"){
					$time = substr(trim(str_replace(": ","",$data_col[2])),0,8);
				}
				
				if(isset($tanggal) AND isset($time)){
					$date_time = $tanggal." ".$time;
				}
				else{
					continue;
				}
				
				if(isset($date_time) AND isset($data_col[1]) AND ($data_col[1]=="CURRENT" OR $data_col[1]=="SAVING")){
					$type = $data_col[1];
				}
				
				if(isset($type) AND isset($data_col[2]) AND strlen($data_col[2])>5 AND is_numeric($data_col[2])){
					$norek = $data_col[2];
					$saldo["time"] = str_replace(array(" ",".",":","-"),"",$date_time);
					$saldo["rekening"][$norek]["tanggal"] = $date_time;
					$saldo["rekening"][$norek]["cabang"] = $data_col[4];
					$saldo["rekening"][$norek]["rekening"] = $norek;
					$saldo["rekening"][$norek]["atasnama_rekening"] = $data_col[3];
					$saldo["rekening"][$norek]["Available_Balance"] = str_replace(",","",$data_col[5]);
					$saldo["rekening"][$norek]["Hold_Amount"] = str_replace(",","",$data_col[6]);
					$saldo["rekening"][$norek]["Current_Balance"] = str_replace(",","",$data_col[7]);
					$saldo["rekening"][$norek]["Ineffective_Balance"] = str_replace(",","",$data_col[8]);
					$saldo["rekening"][$norek]["Uncleared_Balance"] = "";
					if(isset($data_col[9])){
						$saldo["rekening"][$norek]["Uncleared_Balance"] = str_replace(",","",$data_col[9]);
					}
					$saldo["rekening"][$norek]["Status"] = "";
					if(isset($data_col[9])){
						$saldo["rekening"][$norek]["Status"] = $data_col[9];
					}
				}
				else{
					continue;
				}
			}
			$no_line++;
		}
	}
}
	
if(isset($data_respon) AND count($data_respon)){
	$data['status'] = TRUE;
	$data['total_page'] = $total_page;
	if(isset($saldo)){		
		$data['rekap_saldo'] = $saldo;
	}
	$data['data']	= $data_respon;
}
else{
	$data['status'] = FALSE;
}

//response request
echo json_encode($data);