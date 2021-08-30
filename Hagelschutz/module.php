<?php

// Klassendefinition
class Hagelschutz extends IPSModule
{
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    * ABC_MeineErsteEigeneFunktion($id);
    *
    */
          
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        parent::Create();
            
        // Profile
		if(!IPS_VariableProfileExists("HailState")) {
			IPS_CreateVariableProfile("HailState", 0); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String 
			IPS_SetVariableProfileAssociation("HailState", true, $this->Translate("HailStateOn"), "", 0x00FF00); // String_WertName kann mit $$this->translate("ID") in locale.json übersetzten
			IPS_SetVariableProfileAssociation("HailState", false, $this->Translate("HailStateOff"), "", -1); // String_WertName kann mit $$this->translate("ID") in locale.json übersetzten
		}
		
		if(!IPS_VariableProfileExists("HailWarning")) { 
			IPS_CreateVariableProfile("HailWarning", 1); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
			IPS_SetVariableProfileAssociation("HailWarning", 0, $this->Translate("NoHail"), "", -1); // String_WertName kann mit $$this->translate("ID") in locale.json übersetzten 
			IPS_SetVariableProfileAssociation("HailWarning", 1, $this->Translate("Hail"), "", 0xFF0000); // String_WertName kann mit $$this->translate("ID") in locale.json übersetzten
			IPS_SetVariableProfileAssociation("HailWarning", 2, $this->Translate("TestHail"), "", 0x00FF00); // String_WertName kann mit $$this->translate("ID") in locale.json übersetzten
		}
            
		// Notwenige Variablen
		$this->RegisterVariableBoolean("STATE", "Status", "HailState", 1);
		SetValue($this->GetIDForIdent("STATE"), true);
		$this->RegisterVariableInteger("HAIL", "Hagelmeldung", "HailWarning", 2);
            
        // Eigenschaften speichern
		$this->RegisterPropertyString("deviceID", "");
		$this->RegisterPropertyInteger("hwTypeID", 0);
		
		// Timer Registrieren
		$this->RegisterTimer("GetRequest", 120000, 'BRELAG_GetHailRequest($_IPS[\'TARGET\']);');
		
    }
	
	public function RequestAction($Ident, $Value) { 
		switch ($Ident) { 
			case "STATE": 
				SetValue($this->GetIDForIdent($Ident), $Value); 
			break;
		} 
	}

	public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
        }

	public function GetHailRequest() {
		$deviceID = GetValueString($this->ReadPropertyString("deviceID"));
		$hwtypeID = GetValue($this->ReadPropertyInteger("hwTypeID"));
		$url = "https://www.meteo.netitservices.com/api/v0/devices/" . $deviceID . "/poll?hwtypeId=" . $hwtypeID;
		echo($url);
		$contents = file_get_contents($url);
		
		if($contents !== false) {
			$encoded = json_decode($contents, true);
			
			switch($encoded['currentState']) {
				case 0: // Kein Alarm
					SetValue($this->GetIDForIdent("HAIL"), 0);
				break;
				
				case 1: // Hagelalarm
					SetValue($this->GetIDForIdent("HAIL"), 1);
				break;
				
				case 2: // Testalarm
					SetValue($this->GetIDForIdent("HAIL"), 2);
				break; 
			}
		}
	}
	
}

30.08.2021, 21:03:15 | TimerPool            | Brelag Hagelschutz Meteo Schweiz (GetRequest): <br />
<b>Warning</b>:  Cannot auto-convert value for parameter VariableID in <b>/Library/Application Support/Symcon/modules/HagelschutzMeteoSchweiz/Hagelschutz/module.php</b> on line <b>60</b><br />
<br />
<b>Warning</b>:  Variable #203 existiert nicht in <b>/Library/Application Support/Symcon/modules/HagelschutzMeteoSchweiz/Hagelschutz/module.php</b> on line <b>61</b><br />
'https://www.meteo.netitservices.com/api/v0/devices/' .  . '/poll?hwtypeId=' . <br />
<b>Warning</b>:  file_get_contents('https://www.meteo.netitservices.com/api/v0/devices/' .  . '/poll?hwtypeId=' . ): failed to open stream: No such file or directory in <b>/Library/Application Support/Symcon/modules/HagelschutzMeteoSchweiz/Hagelschutz/module.php</b> on line <b>64</b><br />
