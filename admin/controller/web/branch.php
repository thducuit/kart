<?php
class ControllerWebBranch extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('web/branch');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/branch');

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
			'href' => $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('web/branch/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('web/branch/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['branchs'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$branch_total = $this->model_web_branch->getTotalBranchs();

		$results = $this->model_web_branch->getBranchs($filter_data);

		foreach ($results as $result) {
			$data['branchs'][] = array(
				'branch_id' => $result['branch_id'],
				'title'            => $result['title'],
				'sort_order'      => $result['sort_order'],
				'is_main'      => $result['is_main'],
				'edit'            => $this->url->link('web/branch/edit', 'user_token=' . $this->session->data['user_token'] . '&branch_id=' . $result['branch_id'] . $url, true)
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

		$data['sort_name'] = $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $branch_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($branch_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($branch_total - $this->config->get('config_limit_admin'))) ? $branch_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $branch_total, ceil($branch_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('web/branch_list', $data));
	}

	public function add() {
		$this->load->language('web/branch');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/branch');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_web_branch->addBranch($this->request->post);

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

			$this->response->redirect($this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}


	public function edit() {
		$this->load->language('web/branch');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/branch');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            
			$this->model_web_branch->editBranch($this->request->get['branch_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('web/branch');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('web/branch');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {

			foreach ($this->request->post['selected'] as $branch_id) {

				$this->model_web_branch->deleteBranch($branch_id);

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

			$this->response->redirect($this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}


	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['branch_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['branch_id'])) {
			$data['action'] = $this->url->link('web/branch/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('web/branch/edit', 'user_token=' . $this->session->data['user_token'] . '&branch_id=' . $this->request->get['branch_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('web/branch', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['branch_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$branch_info = $this->model_web_branch->getBranch($this->request->get['branch_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['branch_description'])) {
			$data['branch_description'] = $this->request->post['branch_description'];
		} elseif (isset($this->request->get['branch_id'])) {
			$data['branch_description'] = $this->model_web_branch->getBranchDescriptions($this->request->get['branch_id']);
		} else {
			$data['branch_description'] = array();
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

		if (isset($this->request->post['branch_store'])) {
			$data['branch_store'] = $this->request->post['branch_store'];
		} elseif (isset($this->request->get['branch_id'])) {
			$data['branch_store'] = $this->model_web_branch->getBranchStores($this->request->get['branch_id']);
		} else {
			$data['branch_store'] = array(0);
		}

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($branch_info)) {
            $data['image'] = $branch_info['image'];
        } else {
            $data['image'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($branch_info) && is_file(DIR_IMAGE . $branch_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($branch_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

		if (isset($this->request->post['bottom'])) {
			$data['bottom'] = $this->request->post['bottom'];
		} elseif (!empty($branch_info)) {
			$data['bottom'] = $branch_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

        // Categories
        $this->load->model('web/branch_category');
        if (isset($this->request->post['branch_category_id'])) {
            $data['branch_category_id'] = $this->request->post['branch_category_id'];
        } elseif (!empty($branch_info)) {
            $data['branch_category_id'] = $branch_info['branch_category_id'];
        } else {
            $data['branch_category_id'] = 0;
        }

        if (isset($this->request->post['branch_category'])) {
            $data['branch_category'] = $this->request->post['branch_category'];
        } elseif (!empty($branch_info)) {
            $category_info = $this->model_web_branch_category->getCategory($branch_info['branch_category_id']);

            if ($category_info) {
                $data['branch_category'] = $category_info['title'];
            } else {
                $data['branch_category'] = '';
            }
        } else {
            $data['branch_category'] = '';
        }
        
        if (isset($this->request->post['parking'])) {
			$data['parking'] = $this->request->post['parking'];
		} elseif (!empty($branch_info)) {
			$data['parking'] = $branch_info['parking'];
		} else {
			$data['parking'] = 0;
		}

		if (isset($this->request->post['is_main'])) {
			$data['is_main'] = $this->request->post['is_main'];
		} elseif (!empty($branch_info)) {
			$data['is_main'] = $branch_info['is_main'];
		} else {
			$data['is_main'] = 0;
		}
        
        if (isset($this->request->post['area'])) {
			$data['area'] = $this->request->post['area'];
		} elseif (!empty($branch_info)) {
			$data['area'] = $branch_info['area'];
		} else {
			$data['area'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($branch_info)) {
			$data['status'] = $branch_info['status'];
		} else {
			$data['status'] = true;
		}
        
        $this->load->model('tool/image');
        // Images
		if (isset($this->request->post['branch_image'])) {
			$branch_images = $this->request->post['branch_image'];
		} elseif (isset($this->request->get['branch_id'])) {
			$branch_images = $this->model_web_branch->getBranchImages($this->request->get['branch_id']);
		} else {
			$branch_images = array();
		}

		$data['branch_images'] = array();

		foreach ($branch_images as $branch_image) {
			if (is_file(DIR_IMAGE . $branch_image['image'])) {
				$image = $branch_image['image'];
				$thumb = $branch_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['branch_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $branch_image['sort_order']
			);
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($branch_info)) {
			$data['sort_order'] = $branch_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['branch_seo_url'])) {
			$data['branch_seo_url'] = $this->request->post['branch_seo_url'];
		} elseif (isset($this->request->get['branch_id'])) {
			$data['branch_seo_url'] = $this->model_web_branch->getBranchSeoUrls($this->request->get['branch_id']);
		} else {
			$data['branch_seo_url'] = array();
		}
		
		if (isset($this->request->post['branch_layout'])) {
			$data['branch_layout'] = $this->request->post['branch_layout'];
		} elseif (isset($this->request->get['branch_id'])) {
			$data['branch_layout'] = $this->model_web_branch->getBranchLayouts($this->request->get['branch_id']);
		} else {
			$data['branch_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('web/branch_form', $data));
	}


	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'web/branch')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['branch_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post['branch_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['branch_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['branch_id']) || ($seo_url['query'] != 'branch_id=' . $this->request->get['branch_id']))) {
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
		
		if (!$this->user->hasPermission('modify', 'web/branch')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		return !$this->error;
	}


}
