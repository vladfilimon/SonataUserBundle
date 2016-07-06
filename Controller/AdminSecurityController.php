<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityController extends SecurityController
{
    /**
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->get('session')->getFlashBag()->set('sonata_user_error', 'sonata_user_already_authenticated');
            $url = $this->generateUrl('sonata_admin_dashboard');

            return $this->redirect($url);
        }

        $response = parent::loginAction($request);

        if ($this->isGranted('ROLE_ADMIN')) {
            $refererUri = $request->server->get('HTTP_REFERER');

            return $this->redirect($refererUri && $refererUri != $request->getUri() ? $refererUri : $this->generateUrl('sonata_admin_dashboard'));
        }

        // TODO: Deprecated in 2.3, to be removed in 3.0
        try {
            $resetRoute = $this->generateUrl('sonata_user_admin_resetting_request');
        } catch (RouteNotFoundException $e) {
            @trigger_error('Using the route fos_user_resetting_request for admin password resetting is deprecated since version 2.3 and will be removed in 3.0. Use sonata_user_admin_resetting_request instead.', E_USER_DEPRECATED);
            $resetRoute = $this->generateUrl('fos_user_resetting_request');
        }

        return $this->render('SonataUserBundle:Admin:Security/login.html.'.$this->container->getParameter('fos_user.template.engine'), array(
                'last_username' => $lastUsername,
                'error' => $error,
                'csrf_token' => $csrfToken,
                'base_template' => $this->get('sonata.admin.pool')->getTemplate('layout'),
                'admin_pool' => $this->get('sonata.admin.pool'),
                'reset_route' => $resetRoute, // TODO: Deprecated in 2.3, to be removed in 3.0
            ));
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    /**
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        $template = sprintf('FOSUserBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        return $this->render($template, $data);
    }
}
