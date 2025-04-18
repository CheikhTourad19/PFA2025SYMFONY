<?php

namespace App\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?\Symfony\Component\HttpFoundation\Response
    {
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Redirection en fonction des rôles
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_admin_home'));
        }
        if (in_array('ROLE_MEDECIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_home_medecin'));
        }
        if (in_array('ROLE_PATIENT', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_home_patient'));
        }
        if (in_array('ROLE_PHARMACIE', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_home_pharmacie'));
        }
        if (in_array('ROLE_INFERMIER', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_home_infermier'));
        }

        // Redirection par défaut si aucun rôle ne correspond
        return new RedirectResponse($this->router->generate('app_home'));
    }
}