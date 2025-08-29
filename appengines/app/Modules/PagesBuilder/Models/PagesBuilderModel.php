<?php

namespace Modules\PagesBuilder\Models;

use CodeIgniter\Model;

class PagesBuilderModel extends Model
{
    protected $table = 'page_builder';  // Tabel page_builder untuk pages dengan sections
    protected $primaryKey = 'id_page';
    protected $allowedFields = [
        'nama_page', 'slug', 'meta_keywords', 
        'meta_description', 'status', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all pages data
     */
    public function getAllPages($orderBy = 'created_at', $direction = 'DESC')
    {
        return $this->orderBy($orderBy, $direction)->findAll();
    }

    /**
     * Get page by ID
     */
    public function getPageById($id)
    {
        return $this->find($id);
    }

    /**
     * Get page by slug
     */
    public function getPageBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get sections for a specific page with template details
     */
    public function getPageSections($pageId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout l');
        
        return $builder
            ->select('l.*, st.nama_template, st.kode_template, st.icon, st.category, st.form_fields')
            ->join('section_templates st', 'l.id_template = st.id_template', 'left')
            ->where('l.id_page', $pageId)
            ->orderBy('l.urutan', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get all available section templates
     */
    public function getSectionTemplates($status = 'Y')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('section_templates');
        
        return $builder
            ->where('status', $status)
            ->orderBy('category', 'ASC')
            ->orderBy('nama_template', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get page with sections count
     */
    public function getPageWithSectionsCount($pageId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('page_builder p');
        
        return $builder
            ->select('p.*, COUNT(l.id_layout) as sections_count')
            ->join('layout l', 'p.id_page = l.id_page', 'left')
            ->where('p.id_page', $pageId)
            ->groupBy('p.id_page')
            ->get()
            ->getRow();
    }

    /**
     * Check if slug exists (for validation)
     */
    public function isSlugExists($slug, $excludeId = null)
    {
        $builder = $this->where('slug', $slug);
        
        if ($excludeId) {
            $builder->where('id_page !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get published pages only
     */
    public function getPublishedPages()
    {
        return $this->where('status', 'published')
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get homepage
     */
    public function getHomepage()
    {
        return $this->where('is_homepage', 'Y')->first();
    }

    /**
     * Set as homepage (will unset other homepage first)
     */
    public function setAsHomepage($pageId)
    {
        $db = \Config\Database::connect();
        
        // Start transaction
        $db->transStart();
        
        // Unset all homepage flags
        $db->table($this->table)->set('is_homepage', 'N')->update();
        
        // Set this page as homepage
        $db->table($this->table)
           ->where('id_page', $pageId)
           ->set('is_homepage', 'Y')
           ->update();
        
        $db->transComplete();
        
        return $db->transStatus();
    }

    /**
     * Add section to layout
     */
    public function addSection($data)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout');
        
        // Get template info
        $templates = $this->getSectionTemplates();
        $template = null;
        foreach ($templates as $t) {
            if ($t->id_template == $data['id_template']) {
                $template = $t;
                break;
            }
        }
        
        if (!$template) {
            return false;
        }

        // Get max urutan for this page
        $sections = $this->getPageSections($data['id_page']);
        $maxUrutan = 0;
        foreach ($sections as $section) {
            if ($section->urutan > $maxUrutan) {
                $maxUrutan = $section->urutan;
            }
        }
        $urutan = $maxUrutan + 1;

        $sectionData = [
            'id_page' => $data['id_page'],
            'id_template' => $data['id_template'],
            'kode' => $template->kode_template,
            'nama_section' => $data['nama_section'] ?: $template->nama_template,
            'konten_dinamis' => '{}',
            'section_config' => '{}',
            'status' => $data['status'] ?? 'Y',
            'urutan' => $urutan
        ];

        $result = $builder->insert($sectionData);
        
        if ($result) {
            return $db->insertID();
        }
        
        return false;
    }

    /**
     * Update section
     */
    public function updateSection($id, $data)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout');
        
        return $builder->where('id_layout', $id)->update($data);
    }

    /**
     * Delete section
     */
    public function deleteSection($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout');
        
        return $builder->where('id_layout', $id)->delete();
    }
    
    /**
     * Update section status
     */
    public function updateSectionStatus($id, $status)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout');
        
        return $builder->where('id_layout', $id)
                      ->update(['status' => $status]);
    }
    
    /**
     * Get next order for page sections
     */
    public function getNextOrder($pageId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout');
        
        $result = $builder->selectMax('urutan')
                         ->where('id_page', $pageId)
                         ->get()
                         ->getRow();
        
        return ($result->urutan ?? 0) + 1;
    }

    /**
     * Get section by ID
     */
    public function getSectionById($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('layout l');
        
        return $builder
            ->select('l.*, st.nama_template, st.kode_template, st.form_fields')
            ->join('section_templates st', 'l.id_template = st.id_template', 'left')
            ->where('l.id_layout', $id)
            ->get()
            ->getRow();
    }

    /**
     * Reorder sections
     */
    public function reorderSections($sections)
    {
        $db = \Config\Database::connect();
        
        $db->transStart();
        
        foreach ($sections as $index => $sectionId) {
            $db->table('layout')
               ->where('id_layout', $sectionId)
               ->update(['urutan' => $index + 1]);
        }
        
        $db->transComplete();
        
        return $db->transStatus();
    }

    /**
     * Get paginated pages for DataTables
     */
    public function getBuilderPages($start = 0, $length = 25, $searchValue = '')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('page_builder p');
        
        $builder->select('p.*, COUNT(l.id_layout) as sections_count')
                ->join('layout l', 'p.id_page = l.id_page', 'left')
                ->groupBy('p.id_page');
        
        if (!empty($searchValue)) {
            $builder->groupStart()
                    ->like('p.nama_page', $searchValue)
                    ->orLike('p.slug', $searchValue)
                    ->groupEnd();
        }
        
        $builder->orderBy('p.created_at', 'DESC')
                ->limit($length, $start);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get total count of builder pages
     */
    public function getBuilderPagesCount($searchValue = '')
    {
        $builder = $this->builder();
        
        if (!empty($searchValue)) {
            $builder->groupStart()
                    ->like('nama_page', $searchValue)
                    ->orLike('slug', $searchValue)
                    ->groupEnd();
        }
        
        return $builder->countAllResults();
    }
}
