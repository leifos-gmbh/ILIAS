<?php

// begin patch (whole file) videocast – Killing 22.07.2020

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaCast\Video;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoItem
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $time;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $preview_pic;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $playing_time;

    /**
     * @var int
     */
    protected $duration;

    /**
     * Constructor
     */
    public function __construct($id, $title, $time, $mime, $resource, $preview_pic, $description = "",
        $playing_time = "", $duration = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->time = $time;
        $this->mime = $mime;
        $this->resource = $resource;
        $this->preview_pic = $preview_pic;
        if ($this->preview_pic == "") {
            $this->preview_pic = \ilUtil::getImagePath("mcst_preview.svg");
        }
        $this->description = $description;
        $this->playing_time = $playing_time;
        $this->duration = $duration;
    }

    /**
     * Get id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get time
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Get mime
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get resource
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get preview pic
     * @return string
     */
    public function getPreviewPic()
    {
        return $this->preview_pic;
    }

    /**
     * Get playing time
     * @return string
     */
    public function getPlayingTime()
    {
        return $this->playing_time;
    }

    /**
     * Get duration
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

}