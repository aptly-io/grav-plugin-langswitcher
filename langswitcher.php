<?php
namespace Grav\Plugin;

use Grav\Common\Language\LanguageCodes;
use Grav\Common\Page\Page;
use \Grav\Common\Plugin;

class LangSwitcherPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $this->enable([
            'onTwigInitialized'   => ['onTwigInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /** Add the native_name function */
    public function onTwigInitialized()
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('native_name', function($key) {
                return LanguageCodes::getNativeName($key);
            })
        );
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Set needed variables to display Langswitcher.
     */
    public function onTwigSiteVariables()
    {
        $data = new \stdClass;
        $data->page_route = $this->grav['page']->rawRoute();

        /** @var Page $page */
        $page = $this->grav['page'];

        if ($page->home()) {
            $data->page_route = '';
        }


        $data->current = $this->grav['language']->getLanguage();
        $data->languages = $this->grav['language']->getLanguages();

        // list of languages that the page is translated in
        $data->translations = $this->grav['page']->translatedLanguages();
        if (!array_key_exists ($data->current , $data->translations)) {
            // fall back to the default language if page has a translatio for
            // the current selected language
            $data->current = $this->grav['language']->getDefault();
        }

        $this->grav['twig']->twig_vars['langswitcher'] = $data;

        if ($this->config->get('plugins.langswitcher.built_in_css')) {
            $this->grav['assets']->add('plugin://langswitcher/css/langswitcher.css');
        }
    }

    public function getNativeName($code) {

    }
}
