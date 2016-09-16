<?php
@ini_set("display_errors","1");
@ini_set("display_startup_errors","1");

require_once("include/dbcommon.php");
header("Expires: Thu, 01 Jan 1970 00:00:01 GMT"); 

require_once("include/audit_info_variables.php");

$mode = postvalue("mode");

if(!isLogged())
{ 
	return;
}
if(!CheckSecurity(@$_SESSION["_".$strTableName."_OwnerID"],"Search"))
{
	return;
}

require_once("classes/searchclause.php");

$cipherer = new RunnerCipherer($strTableName);

require_once('include/xtempl.php');
$xt = new Xtempl();





$layout = new TLayout("detailspreview", "Coral1Coral1", "MobileCoral1");
$layout->version = 2;
$layout->blocks["bare"] = array();
$layout->containers["dcount"] = array();
$layout->container_properties["dcount"] = array(  );
$layout->containers["dcount"][] = array("name"=>"detailspreviewheader", 
	"block"=>"", "substyle"=>1  );

$layout->containers["dcount"][] = array("name"=>"detailspreviewdetailsfount", 
	"block"=>"", "substyle"=>1  );

$layout->containers["dcount"][] = array("name"=>"detailspreviewdispfirst", 
	"block"=>"display_first", "substyle"=>1  );

$layout->skins["dcount"] = "empty";

$layout->blocks["bare"][] = "dcount";
$layout->containers["detailspreviewgrid"] = array();
$layout->container_properties["detailspreviewgrid"] = array(  );
$layout->containers["detailspreviewgrid"][] = array("name"=>"detailspreviewfields", 
	"block"=>"details_data", "substyle"=>1  );

$layout->skins["detailspreviewgrid"] = "grid";

$layout->blocks["bare"][] = "detailspreviewgrid";
$page_layouts["audit_info_detailspreview"] = $layout;

$layout->skinsparams = array();
$layout->skinsparams["empty"] = array("button"=>"button1");
$layout->skinsparams["menu"] = array("button"=>"button1");
$layout->skinsparams["hmenu"] = array("button"=>"button1");
$layout->skinsparams["undermenu"] = array("button"=>"button1");
$layout->skinsparams["fields"] = array("button"=>"button1");
$layout->skinsparams["form"] = array("button"=>"button1");
$layout->skinsparams["1"] = array("button"=>"button1");
$layout->skinsparams["2"] = array("button"=>"button1");
$layout->skinsparams["3"] = array("button"=>"button1");



$recordsCounter = 0;

//	process masterkey value
$mastertable = postvalue("mastertable");
$masterKeys = my_json_decode(postvalue("masterKeys"));
$sessionPrefix = "_detailsPreview";
if($mastertable != "")
{
	$_SESSION[$sessionPrefix."_mastertable"]=$mastertable;
//	copy keys to session
	$i = 1;
	if(is_array($masterKeys) && count($masterKeys) > 0)
	{
		while(array_key_exists ("masterkey".$i, $masterKeys))
		{
			$_SESSION[$sessionPrefix."_masterkey".$i] = $masterKeys["masterkey".$i];
			$i++;
		}
	}
	if(isset($_SESSION[$sessionPrefix."_masterkey".$i]))
		unset($_SESSION[$sessionPrefix."_masterkey".$i]);
}
else
	$mastertable = $_SESSION[$sessionPrefix."_mastertable"];

$params = array();
$params['id'] = 1;
$params['xt'] = &$xt;
$params['tName'] = $strTableName;
$params['pageType'] = "detailspreview";
$pageObject = new DetailsPreview($params);

if($mastertable == "audit_form")
{
	$where = "";
		$formattedValue = make_db_value("fid",$_SESSION[$sessionPrefix."_masterkey1"]);
	if( $formattedValue == "null" )
		$where .= $pageObject->getFieldSQLDecrypt("fid") . " is null";
	else
		$where .= $pageObject->getFieldSQLDecrypt("fid") . "=" . $formattedValue;
}
if($mastertable == "audit_form_manual")
{
	$where = "";
		$formattedValue = make_db_value("fid",$_SESSION[$sessionPrefix."_masterkey1"]);
	if( $formattedValue == "null" )
		$where .= $pageObject->getFieldSQLDecrypt("fid") . " is null";
	else
		$where .= $pageObject->getFieldSQLDecrypt("fid") . "=" . $formattedValue;
}

$str = SecuritySQL("Search", $strTableName);
if(strlen($str))
	$where.=" and ".$str;
$strSQL = $gQuery->gSQLWhere($where);

$strSQL.=" ".$gstrOrderBy;

$rowcount = $gQuery->gSQLRowCount($where, $pageObject->connection);
$xt->assign("row_count",$rowcount);
if($rowcount) 
{
	$xt->assign("details_data",true);

	$display_count = 10;
	if($mode == "inline")
		$display_count*=2;
		
	if($rowcount>$display_count+2)
	{
		$xt->assign("display_first",true);
		$xt->assign("display_count",$display_count);
	}
	else
		$display_count = $rowcount;

	$rowinfo = array();
	
	require_once getabspath('classes/controls/ViewControlsContainer.php');
	$pSet = new ProjectSettings($strTableName, PAGE_LIST);
	$viewContainer = new ViewControlsContainer($pSet, PAGE_LIST);
	$viewContainer->isDetailsPreview = true;

	$b = true;
	$qResult = $pageObject->connection->query( $strSQL );
	$data = $cipherer->DecryptFetchedArray( $qResult->fetchAssoc() );
	while($data && $recordsCounter<$display_count) {
		$recordsCounter++;
		$row = array();
		$keylink = "";
		$keylink.="&key1=".runner_htmlspecialchars(rawurlencode(@$data["inid"]));
	
	
	//	finding - HTML
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("finding", $data, $keylink);
			$row["finding_value"] = $value;
			$format = $pSet->getViewFormat("finding");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("finding")))
				$class = ' rnr-field-number';
			$row["finding_class"] = $class;
	//	refyID - 
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("refyID", $data, $keylink);
			$row["refyID_value"] = $value;
			$format = $pSet->getViewFormat("refyID");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("refyID")))
				$class = ' rnr-field-number';
			$row["refyID_class"] = $class;
	//	suggest - HTML
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("suggest", $data, $keylink);
			$row["suggest_value"] = $value;
			$format = $pSet->getViewFormat("suggest");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("suggest")))
				$class = ' rnr-field-number';
			$row["suggest_class"] = $class;
	//	answer - 
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("answer", $data, $keylink);
			$row["answer_value"] = $value;
			$format = $pSet->getViewFormat("answer");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("answer")))
				$class = ' rnr-field-number';
			$row["answer_class"] = $class;
	//	answer2 - 
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("answer2", $data, $keylink);
			$row["answer2_value"] = $value;
			$format = $pSet->getViewFormat("answer2");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("answer2")))
				$class = ' rnr-field-number';
			$row["answer2_class"] = $class;
	//	answer3 - 
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("answer3", $data, $keylink);
			$row["answer3_value"] = $value;
			$format = $pSet->getViewFormat("answer3");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("answer3")))
				$class = ' rnr-field-number';
			$row["answer3_class"] = $class;
	//	attachment - Document Download
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("attachment", $data, $keylink);
			$row["attachment_value"] = $value;
			$format = $pSet->getViewFormat("attachment");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("attachment")))
				$class = ' rnr-field-number';
			$row["attachment_class"] = $class;
	//	reply_attachment - Document Download
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("reply_attachment", $data, $keylink);
			$row["reply_attachment_value"] = $value;
			$format = $pSet->getViewFormat("reply_attachment");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("reply_attachment")))
				$class = ' rnr-field-number';
			$row["reply_attachment_class"] = $class;
	//	reply_attachment2 - Document Download
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("reply_attachment2", $data, $keylink);
			$row["reply_attachment2_value"] = $value;
			$format = $pSet->getViewFormat("reply_attachment2");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("reply_attachment2")))
				$class = ' rnr-field-number';
			$row["reply_attachment2_class"] = $class;
	//	reply_attachment3 - Document Download
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("reply_attachment3", $data, $keylink);
			$row["reply_attachment3_value"] = $value;
			$format = $pSet->getViewFormat("reply_attachment3");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("reply_attachment3")))
				$class = ' rnr-field-number';
			$row["reply_attachment3_class"] = $class;
	//	noteAmend - HTML
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("noteAmend", $data, $keylink);
			$row["noteAmend_value"] = $value;
			$format = $pSet->getViewFormat("noteAmend");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("noteAmend")))
				$class = ' rnr-field-number';
			$row["noteAmend_class"] = $class;
	//	noteAmend2 - HTML
			$viewContainer->recId = $recordsCounter;
		    $value = $viewContainer->showDBValue("noteAmend2", $data, $keylink);
			$row["noteAmend2_value"] = $value;
			$format = $pSet->getViewFormat("noteAmend2");
			$class = "rnr-field-text";
			if($format==FORMAT_FILE) 
				$class = ' rnr-field-file'; 
			if($format==FORMAT_AUDIO)
				$class = ' rnr-field-audio';
			if($format==FORMAT_CHECKBOX)
				$class = ' rnr-field-checkbox';
			if($format==FORMAT_NUMBER || IsNumberType($pSet->getFieldType("noteAmend2")))
				$class = ' rnr-field-number';
			$row["noteAmend2_class"] = $class;
		$rowinfo[] = $row;
		if ($b) {
			$rowinfo2[] = $row;
			$b = false;
		}
		$data = $cipherer->DecryptFetchedArray( $qResult->fetchAssoc() );
	}
	$xt->assign_loopsection("details_row",$rowinfo);
	$xt->assign_loopsection("details_row_header",$rowinfo2); // assign class for header
}
$returnJSON = array("success" => true);
$xt->load_template(GetTemplateName("audit_info", "detailspreview"));
$returnJSON["body"] = $xt->fetch_loaded();

if($mode!="inline")
{
	$returnJSON["counter"] = postvalue("counter");
	$layout = GetPageLayout(GoodFieldName($strTableName), 'detailspreview');
	if($layout)
	{
		foreach($layout->getCSSFiles(isRTL(), isMobile()) as $css)
		{
			$returnJSON['CSSFiles'][] = $css;
		}
	}	
}	

echo printJSON($returnJSON);
exit();
?>