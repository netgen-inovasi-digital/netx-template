<?php

namespace Modules\PagesBuilder\Controllers;

use App\Controllers\BaseController;
use Modules\PagesBuilder\Models\PagesBuilderModel;

class PagesBuilder extends BaseController
{
    protected $pagesBuilderModel;
    protected $encrypter;

    public function __construct()
    {
        $this->pagesBuilderModel = new PagesBuilderModel();
        $this->encrypter = \Config\Services::encrypter();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Halaman',
        ];
        return view('Modules\PagesBuilder\Views\v_pages_builder_list', $data);
    }

    public function dataList()
    {
        $draw = $this->request->getGet('draw') ?? 1;
        $start = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 25;
        $searchValue = $this->request->getGet('search')['value'] ?? '';
        
        $pages = $this->pagesBuilderModel->getBuilderPages($start, $length, $searchValue);
        $totalRecords = $this->pagesBuilderModel->getBuilderPagesCount();
        $filteredRecords = $this->pagesBuilderModel->getBuilderPagesCount($searchValue);
        
        $data = [];
        foreach ($pages as $page) {
            $id = bin2hex($this->encrypter->encrypt($page['id_page']));
            
            $data[] = [
                $page['nama_page'],
                '/' . $page['slug'],
                '<span class="badge bg-info">' . ($page['sections_count'] ?? 0) . ' sections</span>',
                $page['status'] == 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-warning">Draft</span>',
                date('d/m/Y', strtotime($page['created_at'])),
                $this->aksi($id)
            ];
        }
        
        return $this->response->setJSON([
            "items" => $data
        ]);
    }

    function aksi($id)
    {
        return '<div id="'.$id.'" class="float-end">
            <span class="text-primary btn-action" title="Page Builder" onclick="builderPage(event)">
                <i class="bi bi-layout-text-sidebar-reverse"></i></span>
            <label class="divider">|</label>
            <span class="text-secondary btn-action" title="Ubah" onclick="editItem(event)">
                <i class="bi bi-pencil-square"></i></span> 
            <label class="divider">|</label>
            <span class="text-danger btn-action" title="Hapus" onclick="deleteItem(event)">
                <i class="bi bi-trash"></i></span>
        </div>';
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Halaman Baru',
            'mode' => 'create',
            'content' => 'Modules\PagesBuilder\Views\v_pages_builder_form'
        ];
        return view('Modules\PagesBuilder\Views\v_pages_builder_form', $data);
    }

    public function edit($id)
    {
        $idenc = $id;
        $id = $this->encrypter->decrypt(hex2bin($id));
        $page = $this->pagesBuilderModel->getPageById($id);
        
        if (!$page) {
            return $this->response->setJSON([
                'res' => false,
                'message' => 'Halaman tidak ditemukan',
                'xname' => csrf_token(),
                'xhash' => csrf_hash()
            ]);
        }

        $data = [
            csrf_token() => csrf_hash(),
            'id' => $idenc,
            'nama_page' => $page['nama_page'],
            'slug' => $page['slug'],
            'meta_title' => $page['meta_keywords'] ?? '',
            'meta_description' => $page['meta_description'] ?? '',
            'status' => $page['status'],
            'is_homepage' => $page['is_homepage'] ?? 'N'
        ];
        
        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        $id = $this->encrypter->decrypt(hex2bin($id));
        
        // Check if homepage
        $page = $this->pagesBuilderModel->getPageById($id);
        
        if ($page && isset($page['is_homepage']) && $page['is_homepage'] === 'Y') {
            return $this->response->setJSON([
                'res' => false,
                'message' => 'Homepage tidak dapat dihapus',
                'xname' => csrf_token(),
                'xhash' => csrf_hash()
            ]);
        }

        // Delete page and its sections (CASCADE will handle sections)
        $res = $this->pagesBuilderModel->delete($id);

        return $this->response->setJSON([
            'res' => $res,
            'xname' => csrf_token(),
            'xhash' => csrf_hash()
        ]);
    }

    public function builder($id)
    {
        $id = $this->encrypter->decrypt(hex2bin($id));
        $page = $this->pagesBuilderModel->getPageById($id);

        if (!$page) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Page not found'
            ]);
        }

        // Get sections for this page using model
        $sections = $this->pagesBuilderModel->getPageSections($id);

        // Get available section templates using model
        $templates = $this->pagesBuilderModel->getSectionTemplates();
        
        // Format data untuk JavaScript
        $data = [
            'status' => 'success',
            'page' => $page,
            'sections' => $sections,
            'templates' => $templates,
            'pageId' => $page['id_page'],
            'pageTitle' => $page['nama_page'],
            'pageSlug' => $page['slug'],
            'pageStatus' => $page['status'],
            'sectionsCount' => is_array($sections) ? count($sections) : (is_object($sections) ? count((array)$sections) : 0)
        ];
        
        return $this->response->setJSON($data);
    }

    public function submit()
    {
        $idenc = $this->request->getPost('id');
        $data = [
            'nama_page' => $this->request->getPost('nama_page'),
            'slug' => $this->request->getPost('slug'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'meta_title' => $this->request->getPost('meta_title'),
            'meta_description' => $this->request->getPost('meta_description'),
            'status' => $this->request->getPost('status'),
            'is_homepage' => $this->request->getPost('is_homepage') ?? 'N'
        ];

        if (empty($idenc)) {
            // Create new page
            $res = $this->pagesBuilderModel->insert($data);
        } else {
            // Update existing page
            $id = $this->encrypter->decrypt(hex2bin($idenc));
            $res = $this->pagesBuilderModel->update($id, $data);
        }

        return $this->response->setJSON([
            'res' => $res ? true : false,
            'xname' => csrf_token(),
            'xhash' => csrf_hash()
        ]);
    }

    public function reorderSections()
    {
        $sections = $this->request->getPost('sections');
        
        $sectionIds = [];
        foreach ($sections as $sectionId) {
            $id = $this->encrypter->decrypt(hex2bin($sectionId));
            $sectionIds[] = $id;
        }

        $res = $this->pagesBuilderModel->reorderSections($sectionIds);

        return $this->response->setJSON([
            'status' => $res ? 'success' : 'error',
            'message' => $res ? 'Urutan section berhasil diperbarui' : 'Gagal memperbarui urutan section'
        ]);
    }

    public function deletePage()
    {
        $idenc = $this->request->getPost('id');
        $id = $this->encrypter->decrypt(hex2bin($idenc));
        
        // Check if homepage
        $page = $this->pagesBuilderModel->getPageById($id);
        
        if ($page && isset($page['is_homepage']) && $page['is_homepage'] === 'Y') {
            return $this->response->setJSON([
                'res' => false,
                'message' => 'Homepage tidak dapat dihapus',
                'xname' => csrf_token(),
                'xhash' => csrf_hash()
            ]);
        }

        // Delete page and its sections (CASCADE will handle sections)
        $res = $this->pagesBuilderModel->delete($id);

        return $this->response->setJSON([
            'res' => $res,
            'xname' => csrf_token(),
            'xhash' => csrf_hash()
        ]);
    }
    
    public function addSection()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'id_page' => 'required',
            'id_template' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validation->getErrors()
            ]);
        }
        
        $data = [
            'id_page' => $this->request->getPost('id_page'),
            'id_template' => $this->request->getPost('id_template'),
            'nama_section' => $this->request->getPost('nama_section') ?: null,
            'status' => 'Y'
        ];
        
        try {
            $result = $this->pagesBuilderModel->addSection($data);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Section berhasil ditambahkan'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'PagesBuilder addSection error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambah section'
            ]);
        }
    }
    
    public function toggleSection()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'id' => 'required',
            'status' => 'required|in_list[Y,N]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validation->getErrors()
            ]);
        }
        
        try {
            $id = $this->encrypter->decrypt(hex2bin($this->request->getPost('id')));
            $status = $this->request->getPost('status');
            
            $result = $this->pagesBuilderModel->updateSectionStatus($id, $status);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Status section berhasil diubah'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'PagesBuilder toggleSection error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengubah status'
            ]);
        }
    }
    
    public function deleteSectionAjax()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'id' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid'
            ]);
        }
        
        try {
            $id = $this->encrypter->decrypt(hex2bin($this->request->getPost('id')));
            
            $result = $this->pagesBuilderModel->deleteSection($id);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Section berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'PagesBuilder deleteSection error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus section'
            ]);
        }
    }
}
