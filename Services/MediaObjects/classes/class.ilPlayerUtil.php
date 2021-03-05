<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Audio/Video Player Utility
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilPlayerUtil
{
    // begin patch videocast – Killing 22.07.2020
    public static function usePatch() : bool
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        if (strtolower($ctrl->getCmdClass()) == "ilobjmediacastgui" && $ctrl->getCmd() == "showContent") {
            $cast = new ilObjMediaCast((int) $_GET["ref_id"]);
            if ($cast->getViewMode() == ilObjMediaCast::VIEW_VCAST) {
                return true;
            } else {
                return false;
            }
        }

        $ref_id = (int) $_GET["ref_id"];
        if ($ref_id == 0 && $_GET["target"] != "") {
            $t = explode("_", $_GET["target"]);
            if (count($t) > 0) {
                $ref_id = (int) $t[count($t) - 1];
            }
        }
        if ($ref_id > 0) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            if (ilContainerPage::_exists("cont", $obj_id)) {
                $page = new ilContainerPage($obj_id);
                $content = $page->getXMLContent();
                if (is_int(strpos($content, 'Plugged PluginName="PCVideoCast"'))) {
                    return true;
                }
            }
        }


        return false;
    }
    // end patch videocast – Killing 22.07.2020

    /**
     * Get local path of jQuery file
     */
    public static function getLocalMediaElementJsPath()
    {
        // begin patch videocast – Killing 22.07.2020
        if (self::usePatch()) {
            return ["./libs/patch/mediaelement/build/mediaelement-and-player.min.js",
                    "./libs/patch/mediaelement/build/renderers/vimeo.min.js"
            ];
        }
        return ["./libs/bower/bower_components/mediaelement/build/mediaelement-and-player.min.js"];
        // end patch videocast – Killing 22.07.2020
    }

    /**
     * Get local path of jQuery file
     */
    public static function getLocalMediaElementCssPath()
    {
        // begin patch videocast – Killing 22.07.2020
        if (self::usePatch()) {
            return "./libs/patch/mediaelement/build/mediaelementplayer.min.css";
        } else {
            return "./libs/bower/bower_components/mediaelement/build/mediaelementplayer.min.css";
        }
        // end patch videocast – Killing 22.07.2020
    }

    /**
     * Init mediaelement.js scripts
     */
    public static function initMediaElementJs($a_tpl = null)
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        
        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }
        
        foreach (self::getJsFilePaths() as $js_path) {
            $a_tpl->addJavaScript($js_path);
        }
        foreach (self::getCssFilePaths() as $css_path) {
            $a_tpl->addCss($css_path);
        }
    }
    
    /**
     * Get css file paths
     *
     * @param
     * @return
     */
    public static function getCssFilePaths()
    {
        return array(self::getLocalMediaElementCssPath());
    }
    
    /**
     * Get js file paths
     *
     * @param
     * @return
     */
    public static function getJsFilePaths()
    {
        // begin patch videocast – Killing 22.07.2020
        return self::getLocalMediaElementJsPath();
        // end patch videocast – Killing 22.07.2020
    }
    

    /**
     * Get flash video player directory
     *
     * @return
     */
    public static function getFlashVideoPlayerDirectory()
    {
        // begin patch videocast – Killing 22.07.2020
        if (self::usePatch()) {
            return "libs/patch/mediaelement/build";
        } else {
            return "libs/bower/bower_components/mediaelement/build";
        }
        // end patch videocast – Killing 22.07.2020
    }
    
    
    /**
     * Get flash video player file name
     *
     * @return
     */
    public static function getFlashVideoPlayerFilename($a_fullpath = false)
    {
        $file = "flashmediaelement.swf";
        if ($a_fullpath) {
            return self::getFlashVideoPlayerDirectory() . "/" . $file;
        }
        return $file;
    }
    
    /**
     * Copy css files to target dir
     *
     * @param
     * @return
     */
    public static function copyPlayerFilesToTargetDirectory($a_target_dir)
    {
        ilUtil::rCopy(
            "./libs/bower/bower_components/mediaelement/build",
            $a_target_dir
        );
    }
}
