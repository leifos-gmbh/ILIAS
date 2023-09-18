<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\MediaObjects\MediaType;

class MediaTypeManager
{
    protected const TYPE_VIDEO = "video";
    protected const TYPE_AUDIO = "audio";
    protected const TYPE_IMAGE = "image";
    protected const TYPE_OTHER = "other";
    protected const TYPES = [
        self::TYPE_VIDEO => [
            "video/vimeo" => [],
            "video/youtube" => [],
            "video/mp4" => ["mp4"],
            "video/webm" => ["webm"]
        ],
        self::TYPE_AUDIO => [
            "audio/mpeg" => ["mp3"]
        ],
        self::TYPE_IMAGE => [
            "image/png" => ["png"],
            "image/jpeg" => ["jpg", "jpeg"],
            "image/gif" => ["gif"],
            "image/webp" => ["webp"],
            "image/svg+xml" => ["svg"]
        ],
        self::TYPE_OTHER => [
            "text/html" => ["html", "htm"],
            "application/pdf" => ["pdf"]
        ]
    ];


    public function __construct()
    {
    }

    /**
     * This has been introduced for applets long time ago and been available for
     * all mime times for several years.
     */
    public function usesParameterProperty(string $mime): bool
    {
        return false;
    }

    /**
     * Check whether only autostart parameter should be supported (instead
     * of parameters input field)
     *
     * This should be the same behaviour as mp3/flv in page.xsl
     */
    public function usesAutoStartParameterOnly(
        string $location,
        string $mime
    ): bool {
        $lpath = pathinfo($location);
        $ext = $lpath["extension"] ?? "";

        if ($this->isVideo() || $this->isAudio()) {
            return true;
        }
        return false;
    }

    public function isImage(string $mime): bool
    {
        return in_array($mime, $this->getImageMimeTypes());
    }

    public function isAudio(string $mime): bool
    {
        return in_array($mime, $this->getAudioMimeTypes());
    }

    public function isVideo(string $mime): bool
    {
        return in_array($mime, $this->getVideoMimeTypes());
    }

    public function usesAltTextProperty(string $mime): bool
    {
        return $this->isImage($mime);
    }

    protected function mergeSuffixes(array ...$arr) : array
    {
        $suffixes = [];
        foreach ($arr as $type) {
            foreach ($type as $item){
                $suffixes = array_merge($suffixes, array_values($item));
            }
        }
        return $suffixes;
    }

    /**
     * @return string[]
     */
    public function getVideoMimeTypes(): array
    {
        return array_keys(self::TYPES[self::TYPE_VIDEO]);
    }

    /**
     * @return string[]
     */
    public function getVideoSuffixes(): array
    {
        return $this->mergeSuffixes(self::TYPES[self::TYPE_VIDEO]);
    }

    /**
     * @return string[]
     */
    public function getAudioMimeTypes(): array
    {
        return array_keys(self::TYPES[self::TYPE_AUDIO]);
    }

    /**
     * @return string[]
     */
    public function getAudioSuffixes(): array
    {
        return $this->mergeSuffixes(self::TYPES[self::TYPE_AUDIO]);
    }

    /**
     * @return string[]
     */
    public function getImageMimeTypes(): array
    {
        return array_keys(self::TYPES[self::TYPE_IMAGE]);
    }

    /**
     * @return string[]
     */
    public function getImageSuffixes(): array
    {
        return $this->mergeSuffixes(self::TYPES[self::TYPE_IMAGE]);
    }

    /**
     * @return string[]
     */
    public function getOtherMimeTypes(): array
    {
        return array_keys(self::TYPES[self::TYPE_OTHER]);
    }

    /**
     * @return string[]
     */
    public function getOtherSuffixes(): array
    {
        return $this->mergeSuffixes(self::TYPES[self::TYPE_OTHER]);
    }

}
