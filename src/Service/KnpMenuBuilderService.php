<?php

namespace App\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class KnpMenuBuilderService
{

    public function __construct(
        private FactoryInterface              $factory,
        private AuthorizationCheckerInterface $authChecker,
        private TranslatorInterface $translator
    )
    {
    }

    public function createMainMenu()
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild($this->translator->trans('navigation.home'), ['route' => 'app_home']);
        $menu->addChild($this->translator->trans('navigation.teams'), ['route' => 'app_team_index']);
        $menu->addChild($this->translator->trans('navigation.about'), ['route' => 'app_about']);

        return $this->setAttributes($menu);
    }

    private function setAttributes($menu)
    {
        foreach ($menu as $item) {
            $item->setLinkAttribute('class', 'nav-link text-decoration-none text-white');
            if ($item->getAttribute('dropdown')) {
                $item->setChildrenAttribute('class', 'dropdown-menu bg-dark text-white');
                $item->setAttribute('class', 'nav-item dropdown bg-dark text-white');
                $item->setLinkAttribute('class', 'nav-link dropdown-toggle text-decoration-none text-white');
                $item->setLinkAttribute('data-bs-toggle', 'dropdown');
                $item->setLinkAttribute('role', 'button');
                $item->setLinkAttribute('aria-expanded', 'false');
                foreach ($item->getChildren() as $child) {
                    $child->setLinkAttribute('class', 'dropdown-item text-decoration-none text-white');
                    $child->setAttribute('class', 'dropdown-item');
                }
            } else {
                $item->setAttribute('class', 'nav-item');
            }
        }

        $menu->setChildrenAttribute('class', 'navbar-nav mb-2 mb-lg-0');
        return $menu;
    }

}
