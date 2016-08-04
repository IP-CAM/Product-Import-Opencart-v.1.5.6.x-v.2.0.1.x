<?php

class ControllerModuleImExp extends Controller
{
    public function index()
    {
        $this->load->language('module/im_exp');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/im_exp', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->document->addStyle('/admin/view/stylesheet/im_exp.css');
        $this->document->addScript('/admin/view/javascript/im_exp.js');
        $this->data['import_url'] = $this->url->link('module/im_exp/import', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['export_url'] = $this->url->link('module/im_exp/export', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['saveorder'] = $this->url->link('module/im_exp/saveorder', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['delorder'] = $this->url->link('module/im_exp/delorder', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['session'] = $this->session->data['token'];
        $this->load->model('tool/im_exp');
        $this->data['saveorderlist'] = $this->model_tool_im_exp->getorder();
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else if (isset($this->session->data['warning'])) {
            $this->data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        } else {
            $this->data['error_warning'] = '';
        }
        $this->template = 'module/im_exp.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function import()
    {
        $this->load->model('tool/im_exp');
        if ($this->model_tool_im_exp->load_data_in_db()) {
            $this->session->data['success'] = "Импорт выполнен успешно";
        } else {
            $this->session->data['warning'] = "Ошибка импорта";
        }

        $this->redirect($this->url->link('module/im_exp', 'token=' . $this->session->data['token'], 'SSL'));
    }

    public function export()
    {
        $this->load->model('tool/im_exp');
        $results = $this->model_tool_im_exp->upload_data_from_db();
        $file_path = str_replace('/admin', '', DIR_APPLICATION);
        $export = $file_path . '/csv/export.csv';
        $fp = fopen($export, 'w+');
        foreach ($results as $result) {
            $res = fputcsv($fp, $result, ';');
        }
        fclose($fp);
        $fp = fopen($export, 'r');
        $contents = fread($fp, filesize($export));
        //$contents=iconv("utf-8", "cp1251",$contents);

        //header('Content-Type: application/octet-stream; charset=cp1251');
        header('Content-Disposition: attachment; filename=export.csv');
        echo $contents;

        exit;
        //var_dump($results);
    }

    public function saveorder()
    {
        $this->load->model('tool/im_exp');
        $name = $this->request->post['tmpl_name'];
        if ($this->model_tool_im_exp->saveorder($name)) {
            // $this->session->data['success']="Шаблон успешно сохранен";
            echo "Шаблон успешно сохранен";
        } else {
            // $this->session->data['warning']="Ошибка сохранения наблона";
            echo "Ошибка сохранения";
        }
        // $this->redirect($this->url->link('module/im_exp', 'token=' . $this->session->data['token'], 'SSL'));
    }

    public function delorder()
    {
        $this->load->model('tool/im_exp');
        if ($this->model_tool_im_exp->delorder()) {
            // $this->session->data['success']="Шаблон успешно сохранен";
            echo "Шаблон успешно удален";
        } else {
            // $this->session->data['warning']="Ошибка сохранения наблона";
            echo "Ошибка удаления";
        }
        // $this->redirect($this->url->link('module/im_exp', 'token=' . $this->session->data['token'], 'SSL'));
    }

    public function install()
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/csv/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/csv/');
        }
        $this->load->model('tool/im_exp');
        $this->model_tool_im_exp->createDB();
    }

    public function uninstall()
    {
        $this->load->model('tool/im_exp');
        $this->model_tool_im_exp->deleteDB();
    }
}

?>