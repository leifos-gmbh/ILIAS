<?php

// begin patch (whole file) videocast – Killing 22.07.2020

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaCast\Video;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoSequence
{
    /**
     * @var \ilObjMediaCast
     */
    protected $media_cast;

    /**
     * @var VideoItem[]
     */
    protected $videos;

    /**
     * Constructor
     */
    public function __construct(\ilObjMediaCast $cast)
    {
        $this->media_cast = $cast;
        $this->init();
    }

    /**
     * Init
     */
    protected function init()
    {
        $videos = [];
        include_once("./Modules/MediaCast/Video/class.VideoItem.php");
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            $title = $item["title"];
            $time = $item["playtime"];
            $preview_pic = "";
            if ($mob->getVideoPreviewPic() != "") {
                $preview_pic = $mob->getVideoPreviewPic();
            }

            $mime = '';
            $resource = '';

            if (is_object($med)) {
                if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                    $resource = $med->getLocation();
                } else {
                    $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                    $resource = $path_to_file;
                }
                $mime = $med->getFormat();
            }
            if (in_array($mime, ["video/mp4", "video/vimeo"])) {
                if (!is_int(strpos($resource, "?"))) {
                    $resource.= "?controls=0";
                }
            }
            if (in_array($mime, ["video/mp4", "video/vimeo", "video/youtube"])) {
                $videos[] = new VideoItem(
                    $item["mob_id"],
                    $title,
                    $time,
                    $mime,
                    $resource,
                    $preview_pic,
                    $item["content"],
                    $item["playtime"],
                    $med->getDuration()
                );
            }
        }
        $this->videos = $videos;
    }

    /**
     * Get videos
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Get first
     * @return VideoItem|null
     */
    public function getFirst()
    {
        if (count($this->videos) > 0) {
            return $this->videos[0];
        }
        return null;
    }

}