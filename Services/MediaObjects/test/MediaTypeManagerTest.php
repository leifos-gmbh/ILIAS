<?php

use PHPUnit\Framework\TestCase;

class MediaTypeManagerTest extends TestCase
{
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $types;

    protected function setUp(): void
    {
        parent::setUp();
        $this->types = new \ILIAS\MediaObjects\MediaType\MediaTypeManager();
    }

    protected function tearDown(): void
    {
    }

    public function testIsImage(): void
    {
        $this->assertEquals(
            true,
            $this->types->isImage("image/png")
        );
    }

    public function testIsVideo(): void
    {
        $this->assertEquals(
            true,
            $this->types->isVideo("video/webm")
        );
    }

    public function testIsAudio(): void
    {
        $this->assertEquals(
            true,
            $this->types->isAudio("audio/mpeg")
        );
    }

    public function testGetAudioSuffixes(): void
    {
        $this->assertEquals(
            ["mp3"],
            $this->types->getAudioSuffixes()
        );
    }

    public function testGetVideoSuffixes(): void
    {
        $this->assertEquals(
            ["mp4", "webm"],
            $this->types->getVideoSuffixes()
        );
    }

    public function testGetImageSuffixes(): void
    {
        $this->assertEquals(
            true,
            in_array("png", $this->types->getImageSuffixes())
        );
    }

    public function testGetOtherSuffixes(): void
    {
        $this->assertEquals(
            true,
            in_array("html", $this->types->getOtherSuffixes())
        );
    }

    public function testGetAudioMimeTypes(): void
    {
        $this->assertEquals(
            ["audio/mpeg"],
            $this->types->getAudioMimeTypes()
        );
    }

    public function testGetVideoMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("video/mp4", $this->types->getVideoMimeTypes())
        );
    }

    public function testGetImageMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("image/jpeg", $this->types->getImageMimeTypes())
        );
    }

    public function testGetOtherMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("text/html", $this->types->getOtherMimeTypes())
        );
    }

}
