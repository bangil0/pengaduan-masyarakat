<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PengaduanController extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//Load Dependencies
		is_logged_in();
		if ( ! empty($this->session->userdata('level'))) :
			redirect('Auth/BlockedController');
		endif;
		$this->load->model('Pengaduan_m');
	}

	// List all your items
	public function index()
	{
		$data['title'] = 'Pengaduan';
		$masyarakat = $this->db->get_where('masyarakat',['username' => $this->session->userdata('username')])->row_array();
		$data['data_pengaduan'] = $this->Pengaduan_m->data_pengaduan_masyarakat_nik($masyarakat['nik'])->result_array();
		
		$this->form_validation->set_rules('isi_laporan','Isi Laporan Pengaduan','trim|required');
		$this->form_validation->set_rules('foto','Foto Pengaduan','trim');

		if ($this->form_validation->run() == FALSE) :
			$this->load->view('_part/backend_head', $data);
			$this->load->view('_part/backend_sidebar_v');
			$this->load->view('_part/backend_topbar_v');
			$this->load->view('masyarakat/pengaduan');
			$this->load->view('_part/backend_footer_v');
			$this->load->view('_part/backend_foot');
		else :
			$upload_foto = $this->upload_foto('foto'); // parameter nama foto
			if ($upload_foto == FALSE) :
				$this->session->set_flashdata('msg','<div class="alert alert-danger" role="alert">
					Upload foto pengaduan gagal, hanya png,jpg dan jpeg yang dapat di upload!
					</div>');

				redirect('Masyarakat/PengaduanController');
			else :

				$params = [
					'tgl_pengaduan'  	=> date('Y-m-d'),
					'nik'				=> $masyarakat['nik'],
					'isi_laporan'		=> htmlspecialchars($this->input->post('isi_laporan',true)),
					'foto'				=> $upload_foto,
					'status'			=> '0',
				];

				$resp = $this->Pengaduan_m->create($params);

				if ($resp) :
					$this->session->set_flashdata('msg','<div class="alert alert-primary" role="alert">
						Laporan berhasil dibuat
						</div>');

					redirect('Masyarakat/PengaduanController');
				else :
					$this->session->set_flashdata('msg','<div class="alert alert-danger" role="alert">
						Laporan gagal dibuat!
						</div>');

					redirect('Masyarakat/PengaduanController');
				endif;

			endif;
		endif;
	}

	private function upload_foto($foto)
	{
		$config['upload_path']          = './assets/uploads/';
		$config['allowed_types']        = 'jpeg|jpg|png';
		$config['max_size']             = 2048;
		$config['remove_spaces']        = TRUE;
		$config['detect_mime']        	= TRUE;
		$config['mod_mime_fix']        	= TRUE;
		$config['encrypt_name']        	= TRUE;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload($foto)) :
			return FALSE;
		else :
			return $this->upload->data('file_name');
		endif;
	}
}

/* End of file PengaduanController.php */
/* Location: ./application/controllers/Masyarakat/PengaduanController.php */
