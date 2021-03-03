<?php
class ControllerExtensionModuleCategoryDisplayNik extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/category_display_nik');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
//            echo "<pre>";
//            print_r($this->request->post);
//            echo "</pre>";
		    $displayed_category_info = $this->request->post;
            $displayed_category_info['displayed_category'] = array();
            unset($displayed_category_info['products_categories']);

            foreach ($this->request->post['displayed_category']['id'] as $k => $cat_ids) {
                $displayed_category_info['displayed_category'][] = array(
                    'id'    => $this->request->post['displayed_category']['id'][$k],
                    'image' => !empty($this->request->post['displayed_category']['image'][$k]) ? $this->request->post['displayed_category']['image'][$k] : ''
                );
            }

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('category_display_nik', $displayed_category_info);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $displayed_category_info);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/category_display_nik', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/category_display_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/category_display_nik', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/category_display_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['display_heading'])) {
			$data['display_heading'] = $this->request->post['display_heading'];
		} elseif (!empty($module_info)) {
			$data['display_heading'] = $module_info['display_heading'];
		} else {
			$data['display_heading'] = '';
		}

        $this->load->model('tool/image');
        $data['img_placeholder'] = $this->model_tool_image->resize('no_image.png', 40, 40);

        $this->load->model('catalog/category');

        $data['categories'] = array();

        $categories = $this->model_catalog_category->getCategories();

        foreach ($categories as $category) {
            if ($category) {
                $category_description = $this->model_catalog_category->getCategoryDescriptions($category['category_id']);
                $data['categories'][] = array(
                    'category_id'          => $category['category_id'],
                    'name' => $category_description[$this->config->get('config_language_id')]['name'],
                    'parent_id'            => $category['parent_id']
                );
            }
        }

        $this->load->model('catalog/product');

        $data['products'] = array();

        if (!empty($this->request->post['displayed_category'])) {
            $displayed_categories = $this->request->post['displayed_category'];
        } elseif (!empty($module_info['displayed_category'])) {
            $displayed_categories = $module_info['displayed_category'];
        } else {
            $displayed_categories = array();
        }

        foreach ($displayed_categories as $category) {
            $category_info = $this->model_catalog_category->getCategory($category['id']);

            if ($category_info) {
                $data['displayed_categories'][] = array(
                    'category_id' => $category['id'],
                    'name'        => $category_info['name'],
                    'image'       => $category['image'],
                    'thumb'       => !empty($category['image']) ? $this->model_tool_image->resize($category['image'], 40, 40) : $this->model_tool_image->resize('no_image.png', 40, 40)
                );
            }

        }

		if (isset($this->request->post['display_type'])) {
			$data['display_type'] = $this->request->post['display_type'];
		} elseif (!empty($module_info)) {
			$data['display_type'] = $module_info['display_type'];
		} else {
			$data['display_type'] = 'carousel';
		}

        if (isset($this->request->post['all_categories_link'])) {
            $data['all_categories_link'] = $this->request->post['all_categories_link'];
        } elseif (!empty($module_info)) {
            $data['all_categories_link'] = $module_info['all_categories_link'];
        } else {
            $data['all_categories_link'] = '';
        }

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/category_display_nik', $data));
	}

	public function getProductsByCategory() {
        $json = array();

        if (isset($this->request->get['category_id'])) {
            $this->load->model('catalog/product');
            if ($this->request->get['category_id']) {
                // get by id
                $results = $this->model_catalog_product->getProductsByCategoryId($this->request->get['category_id']);
            } else {
                // get all
                $results = $this->model_catalog_product->getProducts();
            }

            foreach ($results as $result) {
                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name'       => $result['name'],
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/category_display_nik')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
