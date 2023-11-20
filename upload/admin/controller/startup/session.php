<?php
/**
 * Class Session
 *
 * @package Admin\Controller\Startup
 */
class ControllerStartupSession extends Controller {
	/**
	 * @return void
	 */
    public function index(): void {
        // Session
        $session = new \Session($this->config->get('session_engine'), $this->registry);
        $this->registry->set('session', $session);

        // Update the session lifetime
        if ($this->config->get('config_session_expire')) {
            $this->config->set('session_expire', $this->config->get('config_session_expire'));
        }

        if (isset($this->request->cookie[$this->config->get('session_name')])) {
            $session_id = $this->request->cookie[$this->config->get('session_name')];
        } else {
            $session_id = '';
        }

        $session->start($session_id);

        // Require higher security for session cookies
        $option = [
            'expires'  => ($this->config->get('session_expire') ? time() + (int)$this->config->get('session_expire') : 0),
            'path'     => $this->config->get('session_path'),
            'secure'   => $this->request->server['HTTPS'],
            'httponly' => false,
            'SameSite' => $this->config->get('config_session_samesite')
        ];

        setcookie($this->config->get('session_name'), $session->getId(), $option);
    }
}
