<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Blog export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogExporter extends ilXmlExporter
{
    protected $ds;

    /**
     * @var \ILIAS\Style\Content\DomainService
     */
    protected $content_style_domain;

    public function init()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ds = new ilBlogDataSet();
        $this->ds->setDSPrefix("ds");
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
    }
    
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $res = array();
        
        // postings
        $pg_ids = array();
        foreach ($a_ids as $id) {
            $pages = ilBlogPosting::getAllPostings($id);
            foreach (array_keys($pages) as $p) {
                $pg_ids[] = "blp:" . $p;
            }
        }
        if (sizeof($pg_ids)) {
            $res[] = array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids
            );
        }
        
        // style
        $style_ids = array();
        foreach ($a_ids as $id) {
            $style_id = $this->content_style_domain->styleForObjId((int) $id)->getStyleId();
            if ($style_id > 0) {
                $style_ids[] = $style_id;
            }
        }
        if (sizeof($style_ids)) {
            $res[] = array(
                "component" => "Services/Style",
                "entity" => "sty",
                "ids" => $style_ids
            );
        }

        // service settings
        $res[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);

        return $res;
    }
    
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
    }
    
    public function getValidSchemaVersions($a_entity)
    {
        return array(
                "4.3.0" => array(
                        "namespace" => "http://www.ilias.de/Modules/Blog/4_3",
                        "xsd_file" => "ilias_blog_4_3.xsd",
                        "uses_dataset" => true,
                        "min" => "4.3.0",
                        "max" => "4.9.9"),
                "5.0.0" => array(
                        "namespace" => "http://www.ilias.de/Modules/Blog/5_0",
                        "xsd_file" => "ilias_blog_5_0.xsd",
                        "uses_dataset" => true,
                        "min" => "5.0.0",
                        "max" => "")
            
        );
    }
}
