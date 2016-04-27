<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class ControllerToolOpencart extends Controller
{
    private $error = array();
    private $ocPrefix;

    public function index()
    {
        $this->load->language('tool/opencart');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tool/opencart');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'tool/opencart')) {
            if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
                $content = file_get_contents($this->request->files['import']['tmp_name']);
            } else {
                $content = false;
            }

            if ($content) {
                $this->model_tool_opencart->restore($content);

                $this->session->data['success'] = $this->language->get('text_success');

                $this->response->redirect($this->url->link('tool/opencart', 'token=' . $this->session->data['token'], 'SSL'));
            } else {
                $this->error['warning'] = $this->language->get('error_empty');
            }
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_loading'] = $this->language->get('text_loading');

        $data['entry_upload']   = $this->language->get('entry_upload');
        $data['entry_progress'] = $this->language->get('entry_progress');

        $data['help_upload'] = $this->language->get('help_upload');

        $data['button_upload']   = $this->language->get('button_upload');
        $data['button_clear']    = $this->language->get('button_clear');
        $data['button_continue'] = $this->language->get('button_continue');

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/opencart', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['restore'] = $this->url->link('tool/opencart', 'token=' . $this->session->data['token'], 'SSL');

        $data['backup'] = $this->url->link('tool/opencart/backup', 'token=' . $this->session->data['token'], 'SSL');

        $data['token'] = $this->session->data['token'];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('tool/opencart.tpl', $data));
    }

    public function upload()
    {
        $this->load->language('tool/opencart');

        $path = 'temp-' . md5(mt_rand());

        if (!is_dir(DIR_UPLOAD . $path)) {
            $this->filesystem->mkdir(DIR_UPLOAD . $path);
        }

        if (strrchr($this->request->files['file']['name'], '.') == '.sql') {
            $file = DIR_UPLOAD . $path . '/install.sql';

            // If xml file copy it to the temporary directory
            move_uploaded_file($this->request->files['file']['tmp_name'], $file);

            if (file_exists($file)) {
                // Create Opencart Table Own DataBase
                $json['step'][] = array(
                    'text' => $this->language->get('text_create_dbtable'),
                    'url'  => str_replace('&amp;', '&', $this->url->link('tool/opencart/create', 'token=' . $this->session->data['token'], 'SSL')),
                    'path' => $path
                );

                // Migration Step 1
                $json['step'][] = array(
                    'text' => $this->language->get('text_create_migration'),
                    'url'  => str_replace('&amp;', '&', $this->url->link('tool/opencart/migrationStart', 'token=' . $this->session->data['token'], 'SSL')),
                    'path' => $path
                );

                // Migration Step 2
                $json['step'][] = array(
                    'text' => $this->language->get('text_create_migration'),
                    'url'  => str_replace('&amp;', '&', $this->url->link('tool/opencart/migrationEnd', 'token=' . $this->session->data['token'], 'SSL')),
                    'path' => $path
                );

                // Clear temporary files
                $json['step'][] = array(
                    'text' => $this->language->get('text_remove'),
                    'url'  => str_replace('&amp;', '&', $this->url->link('tool/opencart/remove', 'token=' . $this->session->data['token'], 'SSL')),
                    'path' => $path
                );
            } else {
                $json['error'] = $this->language->get('error_file');
            }
        } else {
            $json['error'] = $this->language->get('error_type');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function create()
    {
        $this->load->language('tool/opencart');
        $this->load->model('tool/opencart');

        $this->model_tool_opencart->createTables();

        $json = array();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function migrationStart()
    {
        $this->load->language('tool/opencart');
        $this->load->model('tool/opencart');

        $this->ocPrefix();
        $this->model_tool_opencart->importTables($this->ocPrefix);

        $json = array();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function migrationEnd()
    {
        $this->load->language('tool/opencart');
        $this->load->model('tool/opencart');

        $this->ocPrefix();
        $this->model_tool_opencart->migrateTables();

        $json = array();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function remove()
    {
        $this->load->language('tool/opencart');
        $this->load->model('tool/opencart');

        $json = array();

        if (!$this->user->hasPermission('modify', 'tool/opencart')) {
            $json['error'] = $this->language->get('error_permission');
        }

        $directory = DIR_UPLOAD . str_replace(array('../', '..\\', '..'), '', $this->request->post['path']);

        if (!is_dir($directory)) {
            $json['error'] = $this->language->get('error_directory');
        }

        if (!$json) {
            // Get a list of files ready to upload
            $files = array();

            $path = array($directory);

            while (count($path) != 0) {
                $next = array_shift($path);

                // We have to use scandir function because glob will not pick up dot files.
                foreach (array_diff(scandir($next), array('.', '..')) as $file) {
                    $file = $next . '/' . $file;

                    if (is_dir($file)) {
                        $path[] = $file;
                    }

                    $files[] = $file;
                }
            }

            sort($files);
            rsort($files);

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                } elseif (is_dir($file)) {
                    rmdir($file);
                }
            }

            if (file_exists($directory)) {
                rmdir($directory);
            }

            // Delete Database Opencart Tables
            $this->model_tool_opencart->deleteTables();

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ocPrefix()
    {
        $sqlFile = fopen(DIR_UPLOAD . $this->request->post['path'] . "/install.sql", "r") or die("Unable to open file!");

        $sql = fgets($sqlFile);
        $sql = trim(str_replace("TRUNCATE TABLE `", "", $sql));
        $sql = explode("_", $sql);

        $this->ocPrefix = $sql[0];
    }
}
