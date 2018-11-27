<?
class TableauSummenlaempchen extends IPSModule {
    public function Create(){
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.

        $this->RegisterPropertyInteger("TableauID", 0);
        $this->RegisterPropertyInteger("Refresh", 10);
        $this->RegisterPropertyString("Profile_N", "keine");
        $this->RegisterPropertyString("Profile_T", "einige");
        $this->RegisterPropertyString("Profile_V", "alle");
        $this->RegisterPropertyString("Profile_A", "aus");
        $this->RegisterPropertyString("Profile_B", "Blinken");
        $this->RegisterPropertyString("Profile_F", "Flackern");
        $this->RegisterPropertyString("Profile_E", "ein");

        if (IPS_VariableProfileExists("HAUSSs.Tableau")){
          IPS_DeleteVariableProfile("HAUSSs.Tableau");
        }
        IPS_CreateVariableProfile("HAUSSs.Tableau", 1);
        IPS_SetVariableProfileValues("HAUSSs.Tableau", 1, 7, 0);
        IPS_SetVariableProfileDigits("HAUSSs.Tableau", 0);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 1, $this->ReadPropertyString("Profile_N"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 2, $this->ReadPropertyString("Profile_T"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 3, $this->ReadPropertyString("Profile_V"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 4, $this->ReadPropertyString("Profile_A"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 5, $this->ReadPropertyString("Profile_B"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 6, $this->ReadPropertyString("Profile_F"), "", -1);
        IPS_SetVariableProfileAssociation("HAUSSs.Tableau", 7, $this->ReadPropertyString("Profile_E"), "", -1);

        $this->RegisterVariableInteger("Light13", "Summe Licht 1", "HAUSSs.Tableau", 1);
        $this->RegisterVariableInteger("Light14", "Summe Licht 2", "HAUSSs.Tableau", 2);
        $this->RegisterVariableInteger("Light15", "Summe Licht 3", "HAUSSs.Tableau", 3);
        $this->RegisterVariableInteger("Light16", "Summe Licht 4", "HAUSSs.Tableau", 4);
        $this->RegisterVariableInteger("Light1", "Tableau Licht 1", "HAUSSs.Tableau", 5);
        $this->RegisterVariableInteger("Light2", "Tableau Licht 2", "HAUSSs.Tableau", 6);
        $this->RegisterVariableInteger("Light3", "Tableau Licht 3", "HAUSSs.Tableau", 7);
        $this->RegisterVariableInteger("Light4", "Tableau Licht 4", "HAUSSs.Tableau", 8);
        $this->RegisterVariableInteger("Light5", "Tableau Licht 5", "HAUSSs.Tableau", 9);
        $this->RegisterVariableInteger("Light6", "Tableau Licht 6", "HAUSSs.Tableau", 10);
        $this->RegisterVariableInteger("Light7", "Tableau Licht 7", "HAUSSs.Tableau", 11);
        $this->RegisterVariableInteger("Light8", "Tableau Licht 8", "HAUSSs.Tableau", 12);
        $this->RegisterVariableInteger("Light9", "Tableau Licht 9","HAUSSs.Tableau", 13);
        $this->RegisterVariableInteger("Light10", "Tableau Licht 10","HAUSSs.Tableau", 14);
        $this->RegisterVariableInteger("Light11", "Tableau Licht 11", "HAUSSs.Tableau", 15);
        $this->RegisterVariableInteger("Light12", "Tableau Licht 12", "HAUSSs.Tableau", 16);

        $this->RegisterTimer("UpdateTableau", $this->ReadPropertyInteger("Refresh"), 'HAUSSs_UpdateTableau($_IPS[\'TARGET\']);');
      }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();

        $Tableau = $this->ReadPropertyInteger("TableauID");

        if ($Tableau != ""){
          $this->SetTimerInterval("UpdateTableau", $this->ReadPropertyInteger("Refresh") * 1000);
        }
        else{
          $this->SetTimerInterval("UpdateTableau", 0);
        }
    }
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        // $this->LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
    }
    public function RequestAction($Ident, $Value) {
        SetValue($this->GetIDForIdent($Ident), $Value);
    }
    public function UpdateTableau(){
        $Tableau = $this->ReadPropertyInteger("TableauID");
        LCN_RequestLights($Tableau);
  	    $children = IPS_GetChildrenIDs($Tableau);
        for ($i = 0; $i < sizeof($children); $i++){
          $child = $children[$i];
          $Object = IPS_GetObject($child);
          $Ident = $Object['ObjectIdent'];
          $Value = GetValue($child);
          $NewValue = $this->TransformValue($Value);
          $OldValue = GetValue($this->GetIDForIdent($Ident));
          $this->SendDebug("Tableau", "Ident: " . $Ident . " | OldValue: " . $OldValue . " | NewValue: " . $NewValue, 0);
          if ($OldValue != $NewValue){
            SetValue($this->GetIDForIdent($Ident), $NewValue);
            $this->SendDebug("Tableau", "Value changed --> Set", 0);
          }
        }
    }
    private function TransformValue(string $Value) {
      // Summen:
      // N = keine (LED an), T = einige, V = alle
      // LED:
      // A = aus, B = blinken, F = flackern, E = ein

      switch($Value){
        case "N":
          $Value = 1;
  		  break;

        case "T":
          $Value = 2;
	      break;

        case "V":
          $Value = 3;
        break;

        case "A":
          $Value = 4;
	      break;

        case "B":
          $Value = 5;
	      break;

        case "F":
          $Value = 6;
	      break;

        case "E":
          $Value = 7;
        break;
	    }
      return($Value);
    }
    protected function LogMessage($Sender, $Message){
        $this->SendDebug($Sender, $Message, 0);
    }
    private function CreateVariableProfile($ProfileName, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
        if (!IPS_VariableProfileExists($ProfileName)) {
            IPS_CreateVariableProfile($ProfileName, $ProfileType);
            IPS_SetVariableProfileText($ProfileName, "", $Suffix);
            IPS_SetVariableProfileValues($ProfileName, $MinValue, $MaxValue, $StepSize);
            IPS_SetVariableProfileDigits($ProfileName, $Digits);
            IPS_SetVariableProfileIcon($ProfileName, $Icon);
	      }
    }
}
?>
