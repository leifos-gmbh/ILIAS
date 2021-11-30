<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageExporter
 */
class ilContentPageExporter extends ilXmlExporter implements ilContentPageObjectConstants
{
    /**
     * @var ilContentPageDataSet
     */
    protected $ds;

    /**
     * @var \ILIAS\Style\Content\DomainService
     */
    protected $content_style_domain;

    /**
     * @inheritdoc
     */
    public function init()
    {
        global $DIC;

        $this->ds = new ilContentPageDataSet();
        $this->ds->setDSPrefix('ds');
        $this->content_style_domain = $DIC->contentStyle()
                                          ->domain();
    }

    /**
     * @inheritdoc
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, '', true, true);
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            '5.4.0' => array(
                'namespace' => 'http://www.ilias.de/Modules/ContentPage/' . self::OBJ_TYPE . '/5_4',
                'xsd_file' => 'ilias_' . self::OBJ_TYPE . '_5_4.xsd',
                'uses_dataset' => true,
                'min' => '5.4.0',
                'max' => '',
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $pageObjectIds = [];
        $styleIds = [];

        foreach ($a_ids as $copaObjId) {
            $copa = ilObjectFactory::getInstanceByObjId($copaObjId, false);
            if (!$copa || !($copa instanceof ilObjContentPage)) {
                continue;
            }

            $copaPageObjIds = $copa->getPageObjIds();
            foreach ($copaPageObjIds as $copaPageObjId) {
                $pageObjectIds[] = self::OBJ_TYPE . ':' . $copaPageObjId;
            }

            $style_id = $this->content_style_domain
                ->styleForObjId($copa->getId())
                ->getStyleId();
            if ($style_id > 0) {
                $styleIds[$style_id] = $style_id;
            }
        }

        $deps = [];

        if (count($pageObjectIds) > 0) {
            $deps[] = [
                'component' => 'Services/COPage',
                'entity' => 'pg',
                'ids' => $pageObjectIds,
            ];
        }

        if (count($styleIds) > 0) {
            $deps[] = [
                'component' => 'Services/Style',
                'entity' => 'sty',
                'ids' => array_values($styleIds),
            ];
        }

        if (self::OBJ_TYPE === $a_entity) {
            $deps[] = [
                'component' => 'Services/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];
        }

        return $deps;
    }
}
