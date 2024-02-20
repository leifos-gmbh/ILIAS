<?php

namespace leifos\CD;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;

/**
 * CD Main menu provider
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MainMenuProvider extends AbstractStaticMainMenuPluginProvider
{
    /**
     * Get plugin
     * @return \ilPlugin
     */
    protected function getPlugin() : \ilPlugin
    {
        global $DIC;
        return $DIC["ilPluginAdmin"]->getPluginObjectById($this->getPluginID());
    }

    protected function getIcon(string $path, string $title) : \ILIAS\UI\Component\Symbol\Icon\Custom
    {
        return $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath($path), $title);
    }

    /**
     * @inheritdoc
     */
    public function getStaticTopItems() : array
    {
        $dic = $this->dic;
        $setting = $dic->settings();
        $access = $dic->access();
        $tree = $dic->repositoryTree();
        $lng = $dic->language();
        $lng->loadLanguageModule("dash");
        $user_admin_centers = \ilCDPermWrapper::getAdminCenters();
        $is_anonym = (in_array($dic->user()->getId(), [ANONYMOUS_USER_ID, 0]));

        $nd = $tree->getNodeData(ROOT_FOLDER_ID);
        $root_title = $nd["title"];
        if ($root_title == "ILIAS") {
            $root_title = $lng->txt("repository");
        }

        if ($is_anonym) {
            return [];
        }

        // cd companies
        $comp_txt = "";
        $comp_link = "";
        if (is_array($user_admin_centers) && count($user_admin_centers) > 0) {
            $comp_txt = $this->getPlugin()->txt("companies");
            $comp_link = "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdCompanyGUI";
        } else {
            // participants list for kunde-per roles
            if (\ilCDPermWrapper::isPerRole()) {
                $comp_txt = $this->getPlugin()->txt("participants");
                $comp_link = "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdCompanyGUI";
            }
        }


        return  [

            // cd dashboard
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_dashboard"))
                ->withTitle($lng->txt("dash_dashboard"))->withAction("ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdDesktopGUI")->withAvailableCallable(function (
                ) : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () : bool {
                    return true;
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_dshs.svg",
                    $lng->txt("dash_dashboard")
                )),

            // cd material
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_material"))
                ->withTitle($this->getPlugin()->txt("material"))->withAction(
                    \ilLink::_getStaticLink($setting->get("cd_mat_ref_id"), 'cat', true)
                )->withAvailableCallable(function (
                ) : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use ($setting, $access) : bool {
                    return $setting->get("cd_mat_ref_id") > 0 && $access->checkAccess("read", "", $setting->get("cd_mat_ref_id"));
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_facs.svg",
                    $this->getPlugin()->txt("material")
                )),


            // cd repository
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_repository"))
                ->withTitle($root_title)
                ->withAction(
                    \ilLink::_getStaticLink(1, 'root', true)
                )->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $access,
                    $user_admin_centers
                ) : bool {
                    return ($access->checkAccess("visible", "", ROOT_FOLDER_ID)
                            && is_array($user_admin_centers) && count($user_admin_centers) > 0) ||
                        \ilCDPermWrapper::isAdmin();
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_crs.svg",
                    $root_title
                )),


            // cd companies
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_companies"))
                ->withTitle($comp_txt)
                ->withAction($comp_link)
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $comp_link,
                    $comp_txt
                ) : bool {
                    return ($comp_link != "");
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_usra.svg",
                    $comp_txt
                )),


            // cd trainer
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_trainers"))
                ->withTitle($this->getPlugin()->txt("trainers"))
                ->withAction("ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=ilCDTrainerGUI")
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $user_admin_centers
                ) : bool {
                    return (is_array($user_admin_centers) && count($user_admin_centers) > 0);
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_rolf.svg",
                    $this->getPlugin()->txt("trainers")
                )),

            // needs analysis
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_needs_analysis"))
                ->withTitle($this->getPlugin()->txt("needs_analysis_reiter"))
                ->withAction("./goto.php?target=needsanalysis")
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $comp_link,
                    $comp_txt
                ) : bool {
                    return true;
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_usra.svg",
                    $comp_txt
                )),

            // self evaluation
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_self_eval"))
                ->withTitle($this->getPlugin()->txt("self_evaluation"))
                ->withAction("./goto.php?target=selfeval")
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $comp_link,
                    $comp_txt
                ) : bool {
                    return true;
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_usra.svg",
                    $comp_txt
                )),

            // entry level test
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_entry_level"))
                ->withTitle($this->getPlugin()->txt("entry_level_test"))
                ->withAction("./goto.php?target=entrylevel")
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $comp_link,
                    $comp_txt
                ) : bool {
                    return true;
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_usra.svg",
                    $comp_txt
                )),

            // studio
            $dic->globalScreen()->mainBar()->link($dic->globalScreen()->identification()->plugin(
                $this->getPlugin()->getId(),
                $this
            )->identifier("cd_studio"))
                ->withTitle($this->getPlugin()->txt("learning_studio"))
                ->withAction("./goto.php?target=studio")
                ->withAvailableCallable(function () : bool {
                    return $this->getPlugin()->isActive();
                })->withVisibilityCallable(function () use (
                    $comp_link,
                    $comp_txt
                ) : bool {
                    return true;
                })
                ->withSymbol($this->getIcon(
                    "outlined/icon_usra.svg",
                    $comp_txt
                )),


        ];
    }


    /**
     * @inheritdoc
     */
    public function getStaticSubItems() : array
    {
        return [];
    }
}
