<?php

class PlatformModel{
	public string $Id;
	public string $Name;
	public string $IconUrl;
	public bool $Deleted;
}

class PlatformData{
    public array $PlatformModels;

    private PlatformDbInterface $platformDbInterface;

    function __construct(PlatformDbInterface &$platformDbInterface) {
        $this->platformDbInterface = $platformDbInterface;
        $this->PlatformModels = $this->LoadPlatforms();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadPlatforms(): array
    {
        AddActionLog("LoadPlatforms");
        StartTimer("LoadPlatforms");

        $data = $this->platformDbInterface->SelectAll();

        $platformModels = Array();
        while($info = mysqli_fetch_array($data)){
            $platform = new PlatformModel();

            $platform->Id = intval($info[DB_COLUMN_PLATFORM_ID]);
            $platform->Name = $info[DB_COLUMN_PLATFORM_NAME];
            $platform->IconUrl = $info[DB_COLUMN_PLATFORM_ICON_URL];
            $platform->Deleted = intval($info[DB_COLUMN_PLATFORM_DELETED]);

            $platformModels[$platform->Id] = $platform;
        }

        StopTimer("LoadPlatforms");
        return $platformModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(): array
    {
        AddActionLog("PlatformData_GetAllPublicData");
        StartTimer("PlatformData_GetAllPublicData");
        
        $dataFromDatabase = MySQLDataToArray($this->platformDbInterface->SelectPublicData());

        foreach($dataFromDatabase as $i => $row){
            //private data overrides
        }

        StopTimer("PlatformData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>