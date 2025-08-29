<?php

namespace Modules\Layout\Controllers;

use App\Controllers\BaseController;
use App\Models\MyModel;

class Layout extends BaseController
{
	private $table = 'layout';
	private $id = 'id_layout';
	protected $encrypter;

	public function __construct()
	{
		$this->encrypter = \Config\Services::encrypter();
	}

	public function index()
	{
		$model = new MyModel('layout');
		$data = [
			'title' => 'Data Urutan Tampilan',
			'getLayout' => $model->query("
				SELECT l.*, p.nama_page, st.nama_template, st.icon, st.category
				FROM layout l
				LEFT JOIN page_builder p ON l.id_page = p.id_page
				LEFT JOIN section_templates st ON l.id_template = st.id_template
				ORDER BY l.id_page ASC, l.urutan ASC
			")
		];
		return view('Modules\Layout\Views\v_layout', $data);
	}

	function edit($id)
	{
		$idenc = $id;
		$id = $this->encrypter->decrypt(hex2bin($id));
		$model = new MyModel($this->table);
		$get = $model->getDataById($this->id, $id);

		$konten = json_decode($get->konten_dinamis ?? '{}', true);

		$data[csrf_token()] = csrf_hash();
		$data['id']         = $idenc;
		$data['judul']      = $konten['judul'] ?? '';
		$data['deskripsi']  = $konten['deskripsi'] ?? '';

		return $this->response->setJSON($data);
	}

	function delete($id)
	{
		$id = $this->encrypter->decrypt(hex2bin($id));
		$model = new MyModel($this->table);
		$res = $model->deleteData($this->id, $id);
		if ($res) {
			$res = 'refresh';
			$link = 'layout';
		}
		return $this->response->setJSON(array(
			'res' => $res,
			'link' => $link ?? '',
			'xname' => csrf_token(),
			'xhash' => csrf_hash()
		));
	}

	public function submit()
	{
		$idenc = $this->request->getPost('id');
		$judul = $this->request->getPost('judul');
		$deskripsi = $this->request->getPost('deskripsi');
		// Masukkan ke dalam array lalu encode jadi JSON
		$konten = [
			'judul' => $judul,
			'deskripsi' => $deskripsi
		];

		$data = [
			'konten_dinamis' => json_encode($konten),
		];

		$model = new MyModel($this->table);
		if ($idenc == "") {
			$code = $this->request->getPost('code');
			$data['urutan'] = (int)$code + 1;
			$res = $model->insertData($data);
		} else {
			$id = $this->encrypter->decrypt(hex2bin($idenc));
			$res = $model->updateData($data, $this->id, $id);
		}

		if ($res) {
			$res = 'refresh';
			$link = 'layout';
		}
		return $this->response->setJSON(array('res' => $res, 'link' => $link ?? '', 'xname' => csrf_token(), 'xhash' => csrf_hash()));
	}

	function updated()
	{
		$data = [];
		$items = $this->request->getPost('items');
		foreach ($items as $item) {
			$data[] = [
				'id_layout'     => $this->encrypter->decrypt(hex2bin($item['id'])),
				'urutan'   => $item['code'],
			];
		}

		$model = new MyModel($this->table);
		$res = $model->updateDataBatch($data, $this->id);
		return $this->response->setJSON(array('res' => $res, 'xhash' => csrf_hash()));
	}

	function toggle()
	{
		$idenc = $this->request->getPost('id');
		$id = $this->encrypter->decrypt(hex2bin($idenc));
		$status = $this->request->getPost('status');
		$data = [
			'status' => $status,
		];

		$model = new MyModel('layout');
		$res = $model->updateData($data, $this->id, $id);
		return $this->response->setJSON(array('res' => $res, 'xhash' => csrf_hash()));
	}
}
