<?php
class ControllerWebLanding extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('web/landing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/landing');

		$this->getList();
	}
	

	protected function getList() { 
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['category'])) {
			$category = $this->request->get['category'];
		} else {
			$category = 0;
		}

		$url = '';

		if (isset($this->request->get['category'])) {
			$url .= '&category=' . $this->request->get['category'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('web/landing/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('web/landing/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['landings'] = array();

		$filter_data = array(
			'category'  => $category,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$landing_total = $this->model_web_landing->getTotalLandings();

		$results = $this->model_web_landing->getLandings($filter_data);

		foreach ($results as $result) {
			$data['landings'][] = array(
				'landing_id' => $result['landing_id'],
				'title'            => $result['title'],
				'category_title'   => $result['category_title'],
				'category_filter'  => $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . '&category=' . $result['category_id'] . $url, true),
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link('web/landing/edit', 'user_token=' . $this->session->data['user_token'] . '&landing_id=' . $result['landing_id'] . $url, true)
			);
		}



		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['category'])) {
			$url .= '&category=' . $this->request->get['category'];
		}

		$data['sort_name'] = $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['category'])) {
			$url .= '&category=' . $this->request->get['category'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $landing_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($landing_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($landing_total - $this->config->get('config_limit_admin'))) ? $landing_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $landing_total, ceil($landing_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('web/landing_list', $data));
	}

	public function add() {
		$this->load->language('web/landing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/landing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_web_landing->addLanding($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}


	public function edit() {
		$this->load->language('web/landing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/landing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_web_landing->editLanding($this->request->get['landing_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('web/landing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/landing');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {

			foreach ($this->request->post['selected'] as $landing_id) {

				$this->model_web_landing->deleteLanding($landing_id);

			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}


	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['landing_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['landing_id'])) {
			$data['action'] = $this->url->link('web/landing/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('web/landing/edit', 'user_token=' . $this->session->data['user_token'] . '&landing_id=' . $this->request->get['landing_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('web/landing', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['landing_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$landing_info = $this->model_web_landing->getLanding($this->request->get['landing_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['landing_description'])) {
			$data['landing_description'] = $this->request->post['landing_description'];
		} elseif (isset($this->request->get['landing_id'])) {
			$data['landing_description'] = $this->model_web_landing->getLandingDescriptions($this->request->get['landing_id']);
		} else {
			$data['landing_description'] = array();
		}

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['landing_store'])) {
			$data['landing_store'] = $this->request->post['landing_store'];
		} elseif (isset($this->request->get['landing_id'])) {
			$data['landing_store'] = $this->model_web_landing->getLandingStores($this->request->get['landing_id']);
		} else {
			$data['landing_store'] = array(0);
		}

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($landing_info)) {
            $data['image'] = $landing_info['image'];
        } else {
            $data['image'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($landing_info) && is_file(DIR_IMAGE . $landing_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($landing_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

		if (isset($this->request->post['bottom'])) {
			$data['bottom'] = $this->request->post['bottom'];
		} elseif (!empty($landing_info)) {
			$data['bottom'] = $landing_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($landing_info)) {
			$data['status'] = $landing_info['status'];
		} else {
			$data['status'] = true;
		}

		// Categories
		$this->load->model('web/category');
		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (!empty($landing_info)) {
			$data['category_id'] = $landing_info['category_id'];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($this->request->post['category'])) {
			$data['category'] = $this->request->post['category'];
		} elseif (!empty($landing_info)) {
			$category_info = $this->model_web_category->getCategory($landing_info['category_id']);

			if ($category_info) {
				$data['category'] = $category_info['title'];
			} else {
				$data['category'] = '';
			}
		} else {
			$data['category'] = '';
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($landing_info)) {
			$data['sort_order'] = $landing_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['landing_seo_url'])) {
			$data['landing_seo_url'] = $this->request->post['landing_seo_url'];
		} elseif (isset($this->request->get['landing_id'])) {
			$data['landing_seo_url'] = $this->model_web_landing->getLandingSeoUrls($this->request->get['landing_id']);
		} else {
			$data['landing_seo_url'] = array();
		}
		
		if (isset($this->request->post['landing_layout'])) {
			$data['landing_layout'] = $this->request->post['landing_layout'];
		} elseif (isset($this->request->get['landing_id'])) {
			$data['landing_layout'] = $this->model_web_landing->getLandingLayouts($this->request->get['landing_id']);
		} else {
			$data['landing_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('web/landing_form', $data));
	}


	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'web/landing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['landing_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) ) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post['landing_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['landing_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['landing_id']) || ($seo_url['query'] != 'landing_id=' . $this->request->get['landing_id']))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		
		if (!$this->user->hasPermission('modify', 'web/landing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		return !$this->error;
	}


}
