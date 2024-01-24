<?php
class ControllerExtensionModuleHeaderBanner extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/banner');
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = $this->language->get('text_add');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_banner_add'] = $this->language->get('button_banner_add');
		$data['button_remove'] = $this->language->get('button_remove');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['banner_image'])) {
			$data['error_banner_image'] = $this->error['banner_image'];
		} else {
			$data['error_banner_image'] = array();
		}

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . $url, true)
		);

		$data['action'] = $this->url->link('extension/module/header_banner/save', 'token=' . $this->session->data['token'] . $url, true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . $url, true);

		$data['name'] = $this->config->get('header_banner_name') ?: '';

		$data['status'] = $this->config->get('header_banner_status') !== null ? $this->config->get('header_banner_status') : true;

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		$data['banners'] = array();

		foreach ($this->config->get('header_banner_banner_image')  as $key => $value) {
			foreach ($value as $banner_image) {
				if (is_file(DIR_IMAGE . $banner_image['image'])) {
					$image = $banner_image['image'];
					$thumb = $banner_image['image'];
				} else {
					$image = '';
					$thumb = 'no_image.png';
				}
				
				$data['banners'][$key][] = array(
					'title'      => $banner_image['title'],
					'link'       => $banner_image['link'],
					'image'      => $image,
					'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
					'sort_order' => $banner_image['sort_order'],
				);
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/header_banner', $data));
	}

	public function save() {
		$this->load->language('design/banner');

		$this->document->setTitle($this->language->get('heading_title'));
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$this->load->model('setting/setting');
		        $this->model_setting_setting->editSetting('header_banner', $this->request->post);
			$this->model_setting_setting->getSetting('header_banner');
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'], true));
		}
		$this->index();
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'design/banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['header_banner_name']) < 3) || (utf8_strlen($this->request->post['header_banner_name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (isset($this->request->post['header_banner_banner_image'])) {
			foreach ($this->request->post['banner_image'] as $language_id => $value) {
				foreach ($value as $banner_image_id => $banner_image) {
					if ((utf8_strlen($banner_image['title']) < 2) || (utf8_strlen($banner_image['title']) > 64)) {
						$this->error['banner_image'][$language_id][$banner_image_id] = $this->language->get('error_title');
					}
				}
			}
		}

		return !$this->error;
	}
}
