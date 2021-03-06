<?php

class xaseItemAccess{
    public static function hasReadAccess(xaseSettings $xase_settings = null, xaseItem $xaseItem = null){
        $access = new ilObjAssistedExerciseAccess();
        return $access->hasReadAccess() || (self::isOwnerOfItem($xase_settings, $xaseItem));
    }

    public static function hasWriteAccess(xaseSettings $xase_settings = null, xaseItem $xaseItem = null){
        $access = new ilObjAssistedExerciseAccess();
        return $access->hasWriteAccess() || (self::isOwnerOfItem($xase_settings, $xaseItem));
    }

    public static function hasDeleteAccess(xaseSettings $xase_settings = null, xaseItem $xaseItem = null){
        $access = new ilObjAssistedExerciseAccess();
        return $access->hasDeleteAccess() || (self::isOwnerOfItem($xase_settings, $xaseItem));
    }

    private static function isOwnerOfItem(xaseSettings $xase_settings, xaseItem $xase_item)
    {
        if($xase_settings === null || $xase_item === null){
            return false;
        }

        global $DIC;
        return $xase_settings === xaseItemTableGUI::M2 && $xase_item->getUserId() === $DIC->user()->getId();
    }
}

