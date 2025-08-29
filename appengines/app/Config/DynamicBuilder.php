<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Dynamic Section Builder Configuration
 * 
 * Konfigurasi untuk customize nama tabel dan pengaturan page builder
 */
class DynamicBuilder extends BaseConfig
{
    /**
     * Nama tabel untuk Pages Builder
     * Ubah jika sudah ada tabel 'pages' di database Anda
     * 
     * Opsi nama tabel yang bisa digunakan:
     * - page_builder (default)
     * - site_pages
     * - cms_pages  
     * - dynamic_pages
     * - web_pages
     */
    public $pages_table = 'page_builder';
    
    /**
     * Nama tabel untuk Layout (biasanya tidak perlu diubah)
     */
    public $layout_table = 'layout';
    
    /**
     * Nama tabel untuk Section Templates
     */
    public $templates_table = 'section_templates';
    
    /**
     * Pengaturaan Default
     */
    public $defaults = [
        'homepage_slug' => 'homepage',
        'max_sections_per_page' => 20,
        'default_section_status' => 'Y',
        'auto_create_homepage' => true,
    ];
    
    /**
     * Upload Configuration
     */
    public $uploads = [
        'max_file_size' => 2048, // KB
        'allowed_types' => 'jpg|jpeg|png|gif|webp',
        'upload_path' => FCPATH . 'uploads/',
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [800, 600],
            'large' => [1200, 900]
        ]
    ];
    
    /**
     * Security Settings
     */
    public $security = [
        'allowed_html_tags' => '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><div><span><blockquote>',
        'csrf_protection' => true,
        'xss_clean' => true,
    ];
    
    /**
     * UI Settings
     */
    public $ui = [
        'items_per_page' => 12,
        'drag_animation_speed' => 150,
        'modal_backdrop' => 'static',
        'theme' => 'bootstrap5'
    ];
}
