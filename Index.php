<?php 
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License");
 * Customised  By Anjaneya Reddy Challa(http://www.anjaneya.info)
 * Date: Feb 24th, 2019.
 ************************************************************************************/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
  if (empty($_POST["module_name"])) {
    $nameErr = "Module Name is required";
  } else {
    $name = trim($_POST["module_name"]);
    $Label = $name;
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      echo $nameErr = "Only letters and white space allowed"; 
      exit;
    }else{

        $MODULENAME = ucwords(strtolower(str_replace(' ', '', $name)));
        include_once 'vtlib/Vtiger/Module.php';
        $Vtiger_Utils_Log = true;
        $moduleInstance = Vtiger_Module::getInstance($MODULENAME);
        if($moduleInstance || file_exists('modules/'.$MODULENAME)) {
          echo "Module already present - choose a different name.";
        } else {

          $moduleInstance = new Vtiger_Module();
          $moduleInstance->name = $MODULENAME;
          $moduleInstance->parent= 'Tools';
          $moduleInstance->save();

          // Schema Setup
          $moduleInstance->initTables();

          // Field Setup
          $block = new Vtiger_Block();
          $block->label = 'LBL_'. strtoupper($moduleInstance->name) . '_INFORMATION';
          $moduleInstance->addBlock($block);

          $blockcf = new Vtiger_Block();
          $blockcf->label = 'LBL_CUSTOM_INFORMATION';
          $moduleInstance->addBlock($blockcf);

          $field2  = new Vtiger_Field();
          $field2->name = strtolower($MODULENAME);
          $field2->label= $Label;
          $field2->uitype= 2;
          $field2->column = $field2->name; 
          $field2->typeofdata = 'V~M';
          $block->addField($field2);

          $field3  = new Vtiger_Field();
          $field3->name = 'description';
          $field3->label= 'Description';
          $field3->uitype= 19;
          $field3->column = 'description';
          $field3->table = 'vtiger_crmentity';
          $blockcf->addField($field3);

          // Recommended common fields every Entity module should have (linked to core table)
          $mfield1 = new Vtiger_Field();
          $mfield1->name = 'assigned_user_id';
          $mfield1->label = 'Assigned To';
          $mfield1->table = 'vtiger_crmentity';
          $mfield1->column = 'smownerid';
          $mfield1->uitype = 53;
          $mfield1->typeofdata = 'V~M';
          $block->addField($mfield1);

          $mfield2 = new Vtiger_Field();
          $mfield2->name = 'createdtime';
          $mfield2->label= 'Created Time';
          $mfield2->table = 'vtiger_crmentity';
          $mfield2->column = 'createdtime';
          $mfield2->uitype = 70;
          $mfield2->typeofdata = 'DT~O';
          $mfield2->displaytype= 2;
          $block->addField($mfield2);

          $mfield3 = new Vtiger_Field();
          $mfield3->name = 'modifiedtime';
          $mfield3->label= 'Modified Time';
          $mfield3->table = 'vtiger_crmentity';
          $mfield3->column = 'modifiedtime';
          $mfield3->uitype = 70;
          $mfield3->typeofdata = 'DT~O';
          $mfield3->displaytype= 2;
          $block->addField($mfield3);

          // Filter Setup
          $filter1 = new Vtiger_Filter();
          $filter1->name = 'All';
          $filter1->isdefault = true;
          $moduleInstance->addFilter($filter1);
          $filter1->addField($field2, 1)->addField($field3, 2)->addField($mfield1, 3);

          // Sharing Access Setup
          $moduleInstance->setDefaultSharing();

          // Webservice Setup
          $moduleInstance->initWebservice();

          mkdir('modules/'.$MODULENAME);
          echo "OK\n";

          $coremodule = $MODULENAME;
   
          $anji_tablename = "vtiger_".strtolower($coremodule);
          $anji_tablename_cf = "vtiger_".strtolower($coremodule)."cf";
          $anji_fieldid = strtolower($coremodule)."id";
          $anji_field = strtolower($coremodule);
          $anji_field_label = $Label;
          $anji_ModuleName = $coremodule;
      
        $file_data = '<?php
          include_once "modules/Vtiger/CRMEntity.php";
          class '.$anji_ModuleName.' extends Vtiger_CRMEntity {
          var $table_name = "'.$anji_tablename.'";
          var $table_index= "'.$anji_fieldid.'";
          var $customFieldTable = Array("'.$anji_tablename_cf.'", "'.$anji_fieldid.'");
          var $tab_name = Array("vtiger_crmentity", "'.$anji_tablename.'", "'.$anji_tablename_cf.'");
          var $tab_name_index = Array(
                  "vtiger_crmentity" => "crmid",
                  "'.$anji_tablename.'" => "'.$anji_fieldid.'",
                  "'.$anji_tablename_cf.'"=>"'.$anji_fieldid.'");
          var $list_fields = Array (
                "'.$anji_field_label.'"  => Array("'.$anji_field.'"),
                "Assigned To" => Array("crmentity","smownerid")
          );
          var $list_fields_name = Array ( 
                "'.$anji_field_label.'"  => "'.$anji_field.'",
                "Assigned To" => "assigned_user_id",
          ); 
          var $search_fields_name = Array (
                "'.$anji_field_label.'"  => "'.$anji_field.'",
                "Assigned To" => "assigned_user_id",
          );
          var $def_detailview_recname = "'.$anji_field.'";
          var $mandatory_fields = Array("'.$anji_field.'","assigned_user_id");
          var $default_order_by = "'.$anji_field.'";
          var $default_sort_order="ASC";
        }';
      
        $module_path = "modules/".$coremodule;
        $my_file =$module_path."/".$coremodule.".php"; 
        $handle = fopen($my_file, "w") or die("Cannot open file:  ".$my_file);
        fwrite($handle, $file_data);
        echo "created Class File.\n";
      }  
    }
  }
} 

?>

<h2>Create New Module</h2>
 
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  Module Name: <input type="text" name="module_name" value="">
  <span class="error">* <?php echo $nameErr;?></span>
  <br><br> 
  <input type="submit" name="submit" value="Submit">  
</form>