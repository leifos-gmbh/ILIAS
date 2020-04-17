<?php

/**
 * Videocast plugin
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCVideoCastPlugin extends ilPageComponentPlugin
{
    /**
     * Get plugin name
     * @return string
     */
    function getPluginName()
    {
        return "PCVideoCast";
    }

    /**
     * Get plugin name
     * @return string
     */
    function isValidParentType($a_parent_type)
    {
        if (in_array($a_parent_type, array("cont"))) {
            return true;
        }
        return false;
    }

    /**
     * Get Javascript files
     */
    function getJavascriptFiles($a_mode)
    {
        return [];
    }

    /**
     * Get css files
     */
    function getCssFiles($a_mode)
    {
        return [];
    }

    /**
     * This function is called after repository (container) objects have been copied
     *
     * @inheritDoc
     */
    public function afterRepositoryCopy(array &$props, array $mapping, int $source_ref_id, string $plugin_version) {
        if (isset($mapping[(int) $props["mcst"]])) {
            $props["mcst"] = $mapping[(int) $props["mcst"]];
        }
    }

}