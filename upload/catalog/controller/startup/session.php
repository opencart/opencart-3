<?php
class ControllerStartupSession extends Controller {
	public function index(): void {
		$session = new \Session($this->config->get('session_engine'), $this->registry);
		$this->registry->set('session', $session);
		
		if (isset($this->request->get['api_token']) && isset($this->request->get['route']) && substr($this->request->get['route'], 0, 4) == 'api/') {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, date_modified) < NOW()");
					
			// Make sure the IP is allowed
			$api_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (a.api_id = ai.api_id) WHERE a.status = '1' AND `as`.`session_id` = '" . $this->db->escape($this->request->get['api_token']) . "' AND ai.ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");
		 
			if ($api_query->num_rows) {
				$this->session->start($this->request->get['api_token']);
				
				// keep the session alive
				$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET `date_modified` = NOW() WHERE `api_session_id` = '" . (int)$api_query->row['api_session_id'] . "'");
			}
		} else {
			// Update the session lifetime
			if ($this->config->get('config_session_expire')) {
				$this->config->set('session_expire', $this->config->get('config_session_expire'));
			}
			
			if (isset($this->request->cookie[$this->config->get('session_name')])) {
				$session_id = $this->request->cookie[$this->config->get('session_name')];
			} else {
				$session_id = '';
			}
			
			$this->session->start($session_id);
			
			$option = array(
				'expires'  => time() + (int)$this->config->get('config_session_expire'),
				'path'     => !empty($this->request->server['PHP_SELF']) ? rtrim(dirname($this->request->server['PHP_SELF']), '/') . '/' : '/',
				'secure'   => $this->request->server['HTTPS'],
				'httponly' => false,
				'SameSite' => $this->config->get('config_session_samesite')
			);

			$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

			setcookie($this->config->get('session_name'), $session->getId(), $option);
		}
	}
}