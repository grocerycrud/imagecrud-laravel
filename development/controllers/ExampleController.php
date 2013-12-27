<?php

use ImageCrud\Core\ImageCrud;

class ExampleController extends BaseController {

    /**
     * The layout that should be used for responses.
     */
    protected $layout = 'layout';

    public function postExample1()
    {
        return $this->getExample1();
    }

    public function getExample1()
    {
        $image_crud = new ImageCRUD();

        $image_crud->set_primary_key_field('id');
        $image_crud->set_url_field('url');
        $image_crud->set_table('example_1')
            ->set_image_path('assets/uploads');

        $output = $image_crud->render();

        return $this->_example_output($output);
    }

    public function postExample2()
    {
        return $this->getExample2();
    }

    public function getExample2()
    {
        $image_crud = new ImageCRUD();

        $image_crud->set_primary_key_field('id');
        $image_crud->set_url_field('url');
        $image_crud->set_table('example_2')
            ->set_ordering_field('priority')
            ->set_image_path('assets/uploads');

        $output = $image_crud->render();

        return $this->_example_output($output);
    }

    public function postExample3()
    {
        return $this->getExample3();
    }

    public function getExample3()
    {
        $image_crud = new ImageCRUD();

        $image_crud->set_primary_key_field('id');
        $image_crud->set_url_field('url');
        $image_crud->set_table('example_3')
            ->set_relation_field('category_id')
            ->set_ordering_field('priority')
            ->set_image_path('assets/uploads');

        $output = $image_crud->render();

        return $this->_example_output($output);
    }

    public function postExample4()
    {
        return $this->getExample4();
    }

    public function getExample4()
    {
        $image_crud = new ImageCRUD();

        $image_crud->set_primary_key_field('id');
        $image_crud->set_url_field('url');
        $image_crud->set_title_field('title');
        $image_crud->set_table('example_4')
            ->set_ordering_field('priority')
            ->set_image_path('assets/uploads');

        $output = $image_crud->render();

        return $this->_example_output($output);
    }

    public function postExample5()
    {
        return $this->getExample5();
    }

    public function getExample5()
    {
        $image_crud = new ImageCRUD();

        $image_crud->unset_upload();
        $image_crud->unset_delete();

        $image_crud->set_primary_key_field('id');
        $image_crud->set_url_field('url');
        $image_crud->set_table('example_4')
            ->set_image_path('assets/uploads');

        $output = $image_crud->render();

        return $this->_example_output($output);
    }

    private function _example_output($output = null)
    {
        return View::make('example', $output);
    }

}