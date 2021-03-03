<?php
class ControllerExtensionModuleCategoryDisplayNik extends Controller {
	public function index($setting) {
	    static $module = 0;
		$this->load->language('extension/module/category_display_nik');

		if ($setting['display_type'] == 'carousel') {
            $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
            $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
            $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
        }

		$this->load->model('tool/image');
		$this->load->model('catalog/category');

		$data['categories'] = array();

		$data = $setting;

		foreach ($setting['displayed_category'] as $category) {
            $category_info = $this->model_catalog_category->getCategory($category['id']);

            if ($category_info) {

                $data['categories'][] = array(
                    'category_id' => $category['id'],
                    'name'        => $category_info['name'],
                    'thumb'       => !empty($category['image']) ? $this->model_tool_image->resize($category['image'], 40, 40) : '',
                    'href'        => $this->url->link('product/category', 'path=' .  ($category_info['parent_id'] != '0' ? $category_info['parent_id'] . '_' : '') . $category_info['category_id'])
                );
            }
        }

		$data['module'] = $module++;

		if ($data['categories']) {
			return $this->load->view('extension/module/category_display_nik', $data);
		}
	}
}