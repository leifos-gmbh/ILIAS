<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentModeManagerTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\Container\Content\ModeManager $manager;

    protected function setUp() : void
    {
        parent::setUp();
        $view_repo = new \ILIAS\Container\Content\ModeSessionRepository();
        $this->manager = new \ILIAS\Container\Content\ModeManager($view_repo);
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test admin view
     */
    public function testAdminView()
    {
        $manager = $this->manager;

        $manager->setAdminMode();

        $this->assertEquals(
            true,
            $manager->isAdminMode()
        );
        $this->assertEquals(
            false,
            $manager->isContentMode()
        );
    }

    /**
     * Test content view
     */
    public function testContentView()
    {
        $manager = $this->manager;

        $manager->setContentMode();

        $this->assertEquals(
            false,
            $manager->isAdminMode()
        );
        $this->assertEquals(
            true,
            $manager->isContentMode()
        );
    }
}
