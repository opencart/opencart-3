<?php
/**
 * Class Login
 *
 * @package Admin\Controller\Startup
 */
class ControllerStartupLogin extends Controller {
	/**
	 * @return object|\Action|null
	 */
    public function index(): ?object {
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

        $ignore = [
            'common/login',
            'common/forgotten',
            'common/reset',
            'common/language'
        ];

        // User
        $this->registry->set('user', new \Cart\User($this->registry));

        if (!$this->user->isLogged() && !in_array($route, $ignore)) {
            return new \Action('common/login');
        }

        if (isset($this->request->get['route'])) {
            $ignore = [
                'common/login',
                'common/logout',
                'common/forgotten',
                'common/reset',
                'common/language',
                'error/not_found',
                'error/permission'
            ];

            if (!in_array($route, $ignore) && (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token']))) {
                return new \Action('common/login');
            }
        } else {
            if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
                return new \Action('common/login');
            }
        }

        return null;
    }
}
