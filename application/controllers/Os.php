<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Os extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model('os_model');
        $this->data['menuOs'] = 'OS';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /* =========================================================
     * GERENCIAR OS
     * ========================================================= */
    public function gerenciar()
    {
        $this->load->library('pagination');
        $this->load->model('mapos_model');

        $where_array = [];

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $inputDe = $this->input->get('data');
        $inputAte = $this->input->get('data2');

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = $status;
        }
        if ($inputDe) {
            $de = explode('/', $inputDe);
            $de = $de[2] . '-' . $de[1] . '-' . $de[0];
            $where_array['de'] = $de;
        }
        if ($inputAte) {
            $ate = explode('/', $inputAte);
            $ate = $ate[2] . '-' . $ate[1] . '-' . $ate[0];
            $where_array['ate'] = $ate;
        }

        $this->data['configuration']['base_url'] = site_url('os/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->os_model->count('os');
        if (count($where_array) > 0) {
            $this->data['configuration']['suffix'] =
                "?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
            $this->data['configuration']['first_url'] =
                base_url("index.php/os/gerenciar") .
                "\?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->os_model->getOs(
            'os',
            'os.*,
            COALESCE((SELECT SUM(produtos_os.preco * produtos_os.quantidade ) FROM produtos_os WHERE produtos_os.os_id = os.idOs), 0) totalProdutos,
            COALESCE((SELECT SUM(servicos_os.preco * servicos_os.quantidade ) FROM servicos_os WHERE servicos_os.os_id = os.idOs), 0) totalServicos',
            $where_array,
            $this->data['configuration']['per_page'],
            $this->uri->segment(3)
        );

        $this->data['texto_de_notificacao'] = $this->data['configuration']['notifica_whats'];
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['view'] = 'os/os';

        return $this->layout();
    }

    /* =========================================================
     *  ADICIONAR OS
     * ========================================================= */
    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataInicial = $this->input->post('dataInicial');
            $dataFinal = $this->input->post('dataFinal');
            $termoGarantiaId = $this->input->post('termoGarantia');

            try {
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

                if ($dataFinal) {
                    $dataFinal = explode('/', $dataFinal);
                    $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
                } else {
                    $dataFinal = date('Y/m/d');
                }

                $termoGarantiaId = (!$termoGarantiaId == null || !$termoGarantiaId == '')
                    ? $this->input->post('garantias_id')
                    : null;
            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
                $dataFinal = date('Y/m/d');
            }

            $data = [
                'dataInicial'     => $dataInicial,
                'clientes_id'     => $this->input->post('clientes_id'),
                'usuarios_id'     => $this->input->post('usuarios_id'),
                'dataFinal'       => $dataFinal,
                'garantia'        => set_value('garantia'),
                'garantias_id'    => $termoGarantiaId,
                'descricaoProduto'=> $this->input->post('descricaoProduto'),
                'defeito'         => $this->input->post('defeito'),
                'status'          => set_value('status'),
                'observacoes'     => $this->input->post('observacoes'),
                'laudoTecnico'    => $this->input->post('laudoTecnico'),
                'checklist'       => $this->input->post('checklist'),
                'senha_celular'   => $this->input->post('senha_celular'),
                'faturado'        => 0,
            ];

            if (is_numeric($id = $this->os_model->add('os', $data, true))) {
                $this->load->model('mapos_model');
                $this->load->model('usuarios_model');

                $idOs = $id;
                $os = $this->os_model->getById($idOs);
                $emitente = $this->mapos_model->getEmitente();
                $tecnico = $this->usuarios_model->getById($os->usuarios_id);

                // Envio de notificação
                if ($this->data['configuration']['os_notification'] != 'nenhum'
                    && $this->data['configuration']['email_automatico'] == 1) {
                    $remetentes = [];
                    switch ($this->data['configuration']['os_notification']) {
                        case 'todos':
                            array_push($remetentes, $os->email, $tecnico->email, $emitente->email);
                            break;
                        case 'cliente':
                            array_push($remetentes, $os->email);
                            break;
                        case 'tecnico':
                            array_push($remetentes, $tecnico->email);
                            break;
                        case 'emitente':
                            array_push($remetentes, $emitente->email);
                            break;
                        default:
                            array_push($remetentes, $os->email);
                            break;
                    }
                    $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Criada');
                }

                $this->session->set_flashdata('success', 'OS adicionada com sucesso!');
                log_info('Adicionou uma OS. ID: ' . $id);
                redirect(site_url('os/editar/') . $id);
            } else {
                $this->data['custom_error'] = '<div class="alert">Ocorreu um erro.</div>';
            }
        }

        $this->data['view'] = 'os/adicionarOs';
        return $this->layout();
    }

    /* =========================================================
     *  EDITAR OS
     * ========================================================= */
    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro inválido.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $this->data['texto_de_notificacao'] = $this->data['configuration']['notifica_whats'];

        $this->data['editavel'] = $this->os_model->isEditable($this->input->post('idOs'));
        if (!$this->data['editavel']) {
            $this->session->set_flashdata('error', 'Esta OS não pode ser alterada. Abra uma nova OS.');
            redirect(site_url('os'));
        }

        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $dataInicial = $this->input->post('dataInicial');
            $dataFinal   = $this->input->post('dataFinal');
            $termoGarantiaId = $this->input->post('garantias_id') ?: null;

            try {
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

                $dataFinal = explode('/', $dataFinal);
                $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
            }

            $data = [
                'dataInicial'   => $dataInicial,
                'dataFinal'     => $dataFinal,
                'garantia'      => $this->input->post('garantia'),
                'garantias_id'  => $termoGarantiaId,
                'descricaoProduto' => $this->input->post('descricaoProduto'),
                'defeito'       => $this->input->post('defeito'),
                'status'        => $this->input->post('status'),
                'observacoes'   => $this->input->post('observacoes'),
                'laudoTecnico'  => $this->input->post('laudoTecnico'),
                'checklist'     => $this->input->post('checklist'),
                'senha_celular' => $this->input->post('senha_celular'),
                'usuarios_id'   => $this->input->post('usuarios_id'),
                'clientes_id'   => $this->input->post('clientes_id'),
            ];

            $os = $this->os_model->getById($this->input->post('idOs'));

            if (strtolower($this->input->post('status')) == 'cancelado' && strtolower($os->status) != 'cancelado') {
                $this->devolucaoEstoque($this->input->post('idOs'));
            }

            if (strtolower($os->status) == 'cancelado' && strtolower($this->input->post('status')) != 'cancelado') {
                $this->debitarEstoque($this->input->post('idOs'));
            }

            if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
                $this->load->model('mapos_model');
                $this->load->model('usuarios_model');

                $idOs = $this->input->post('idOs');
                $os = $this->os_model->getById($idOs);
                $emitente = $this->mapos_model->getEmitente();
                $tecnico = $this->usuarios_model->getById($os->usuarios_id);

                if ($this->data['configuration']['os_notification'] != 'nenhum'
                    && $this->data['configuration']['email_automatico'] == 1) {
                    $remetentes = [];
                    switch ($this->data['configuration']['os_notification']) {
                        case 'todos':
                            array_push($remetentes, $os->email, $tecnico->email, $emitente->email);
                            break;
                        case 'cliente':
                            array_push($remetentes, $os->email);
                            break;
                        case 'tecnico':
                            array_push($remetentes, $tecnico->email);
                            break;
                        case 'emitente':
                            array_push($remetentes, $emitente->email);
                            break;
                        default:
                            array_push($remetentes, $os->email);
                            break;
                    }
                    $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Editada');
                }

                $this->session->set_flashdata('success', 'OS editada com sucesso!');
                log_info('Alterou uma OS. ID: ' . $idOs);
                redirect(site_url('os/editar/') . $idOs);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['anexos']   = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['anotacoes']= $this->os_model->getAnotacoes($this->uri->segment(3));

        if ($return = $this->os_model->valorTotalOS($this->uri->segment(3))) {
            $this->data['totalServico'] = $return['totalServico'];
            $this->data['totalProdutos'] = $return['totalProdutos'];
        }

        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['view'] = 'os/editarOs';
        return $this->layout();
    }
}
