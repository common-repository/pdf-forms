<?php
namespace PdfFormsLoader\Facades;

use PdfFormsLoader\Core\{CustomRequest, Views};

class MetaBoxesFacade
{
    protected $slug;
    protected $fields;
    protected $fieldsName = [];
    protected $title;
    protected $postType;
    protected $context;
    protected $priority;
    protected $args;

    public function __construct($params)
    {
        $this->slug = $params['slug'];
        $this->title = $params['title'];
        $this->postType = $params['postType'];
        $this->context = $params['context'];
        $this->priority = $params['priority'];
        $this->fields = $params['fields'];
        $this->setFieldsNames();
        $this->args = $params;

        return $this;
    }

    public static function make($params) {
        $box = new MetaBoxesFacade($params);
        add_action('add_meta_boxes', [&$box, 'addMetaBox']);
        add_action( 'save_post', [&$box, 'metaSave'] );
    }

    public function addMetaBox() {
        add_meta_box(
            $this->slug,
            $this->title,
            [&$this, 'render'],
            $this->postType,
            $this->context
            //, $this->priority
        );
    }

    function metaSave($postId) {
        if (empty(CustomRequest::instance()->post('post_type'))) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( $this->postType == CustomRequest::instance()->post('post_type') ) {
            if ( !current_user_can( 'edit_page', $postId ) )
                return;
        } else {
            if ( !current_user_can( 'edit_post', $postId ) )
                return;
        }

        foreach($this->getFieldsNames() as $name) {
            if (isset($_POST[$name])) {
                $value = $_POST[$name];
                update_post_meta( $postId, $name, $value );
            }
        }
    }

    protected function getCurrentValue($slug) {
        if (CustomRequest::instance()->get()->has('post')) {
            return get_post_meta(CustomRequest::instance()->get('post'), $slug, true);
        }
        return '';
    }

    protected function setFieldsNames() {
        foreach($this->fields as $field) {
            $this->fieldsName[] = $this->getFieldName($field['name']);
        }
    }

    protected function getFieldName($name) {
        return $this->slug . '_' . $name;
    }

    protected function getFieldsNames() {
        return $this->fieldsName;
    }

    public function render() {
        $fields = [];
        foreach($this->fields as $field) {
            $uiType = 'PdfFormsLoader\\Core\Ui\\' . ucfirst($field['type']);
            $field['name'] = $this->getFieldName($field['name']);
            $field['value'] = $this->getCurrentValue($field['name']);
            $ui = new $uiType($field);
            $fields[] = $ui->output();
        }

        echo Views::render(
            'meta-boxes/box.php',
            [
                'fields'  => $fields,
            ]
        );;
    }
}
