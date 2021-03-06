<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}
        
        $this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			// Level 2
			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach ($children as $child) {
				$filter_data = array(
					'filter_category_id'  => $child['category_id'],
					'filter_sub_category' => true
				);
                
                $level3_children = $this->model_catalog_category->getCategories($child['category_id']);
                
                $level3 = array();
                
                foreach($level3_children as $level3_child) {
                    $level3[] = array(
                        'id'  => $level3_child['category_id'],
                        'name'  => $level3_child['name'],
					    'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $level3_child['category_id'])
                    );
                }
                
				$children_data[] = array(
					'id'  => $child['category_id'],
                    'name'  => $child['name'],
					'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
				    'children' => $level3   
                );
			}

			// Level 1
			$data['categories'][] = array(
                'id'        => $category['category_id'],
				'name'     => $category['name'],
				'children' => $children_data,
				'column'   => $category['column'] ? $category['column'] : 1,
				'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
			);	
		}

		return $this->load->view('common/search', $data);
	}
}