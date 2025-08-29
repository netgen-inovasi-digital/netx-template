<?php

namespace App\Libraries;

class DynamicTemplateRenderer
{
    private $data;
    
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Render template dengan dynamic data
     */
    public function render($templateHtml, $sectionData = [])
    {
        // Parse template content
        $content = $this->parseTemplate($templateHtml, $sectionData);
        
        // Handle data sources (like layanan, team, etc)
        $content = $this->handleDataSources($content);
        
        return $content;
    }
    
    /**
     * Parse Handlebars-like template syntax
     */
    private function parseTemplate($template, $data)
    {
        // Replace simple variables: {{variable}}
        $template = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data) {
            $variable = trim($matches[1]);
            
            // Handle nested object access: {{object.property}}
            if (strpos($variable, '.') !== false) {
                $parts = explode('.', $variable);
                $value = $data;
                foreach ($parts as $part) {
                    $value = isset($value[$part]) ? $value[$part] : '';
                }
                return $value;
            }
            
            return $data[$variable] ?? '';
        }, $template);
        
        // Handle triple braces for HTML content: {{{variable}}}
        $template = preg_replace_callback('/\{\{\{([^}]+)\}\}\}/', function($matches) use ($data) {
            $variable = trim($matches[1]);
            return $data[$variable] ?? '';
        }, $template);
        
        // Handle conditionals: {{#if condition}}...{{/if}}
        $template = preg_replace_callback('/\{\{#if\s+([^}]+)\}\}(.*?)\{\{\/if\}\}/s', function($matches) use ($data) {
            $condition = trim($matches[1]);
            $content = $matches[2];
            
            // Evaluate condition
            if ($this->evaluateCondition($condition, $data)) {
                return $this->parseTemplate($content, $data);
            }
            return '';
        }, $template);
        
        return $template;
    }
    
    /**
     * Evaluate conditional expressions
     */
    private function evaluateCondition($condition, $data)
    {
        // Simple conditions like: variable, !variable, variable == 'value'
        if (strpos($condition, '==') !== false) {
            list($var, $value) = array_map('trim', explode('==', $condition));
            $var = str_replace(["'", '"'], '', $var);
            $value = str_replace(["'", '"'], '', $value);
            return isset($data[$var]) && $data[$var] == $value;
        }
        
        if (substr($condition, 0, 1) === '!') {
            $var = substr($condition, 1);
            return empty($data[$var]);
        }
        
        return !empty($data[$condition]);
    }
    
    /**
     * Handle data sources from database
     */
    private function handleDataSources($content)
    {
        // Find data-source attributes and replace with actual data
        $content = preg_replace_callback('/<([^>]+)data-source="([^"]+)"([^>]*)>/', function($matches) {
            $tag = $matches[1];
            $source = $matches[2];
            $attributes = $matches[3];
            
            // Parse additional attributes
            $limit = $this->extractAttribute($attributes, 'data-limit') ?: 10;
            $columns = $this->extractAttribute($attributes, 'data-columns') ?: 3;
            $layout = $this->extractAttribute($attributes, 'data-layout') ?: 'grid';
            
            // Load data from source
            $sourceData = $this->loadDataSource($source, $limit);
            
            // Generate HTML based on source type
            return $this->generateSourceHTML($source, $sourceData, $columns, $layout);
            
        }, $content);
        
        return $content;
    }
    
    /**
     * Extract attribute value from HTML string
     */
    private function extractAttribute($html, $attribute)
    {
        preg_match('/' . $attribute . '="([^"]*)"/', $html, $matches);
        return $matches[1] ?? null;
    }
    
    /**
     * Load data from database based on source type
     */
    private function loadDataSource($source, $limit = 10)
    {
        $model = new \App\Models\MyModel($source);
        
        switch ($source) {
            case 'layanan':
                return $model->getAllDataWhereLimit(['status' => 'Y'], 'urutan', 'asc', $limit);
                
            case 'team':
                return $model->getAllDataWhereLimit(['status' => 'Y'], 'urutan', 'asc', $limit);
                
            case 'berita':
                // Use MyModel for berita instead
                $beritaModel = new \App\Models\MyModel('berita');
                return $beritaModel->getAllDataWhereLimit(['status' => 'Tampil'], 'id_berita', 'desc', $limit);
                
            case 'mitra':
                return $model->getAllDataWhereLimit(['status' => 'Y'], 'urutan', 'asc', $limit);
                
            default:
                return [];
        }
    }
    
    /**
     * Generate HTML for different source types
     */
    private function generateSourceHTML($source, $data, $columns, $layout)
    {
        if (empty($data)) {
            return '<p class="text-center text-muted">Tidak ada data tersedia</p>';
        }
        
        $html = '';
        $colClass = 'col-lg-' . (12 / $columns);
        
        switch ($source) {
            case 'layanan':
                foreach ($data as $item) {
                    $html .= '<div class="' . $colClass . ' mb-4">
                        <div class="service-card bg-white p-3 rounded shadow-sm h-100 text-center">
                            <img src="' . base_url('uploads/' . $item->foto) . '" alt="' . esc($item->judul) . '" class="img-fluid mb-3" style="height: 80px; object-fit: contain;">
                            <h5 class="fw-bold">' . esc($item->judul) . '</h5>
                            <p class="text-muted small">' . esc(substr($item->deskripsi, 0, 100)) . '...</p>
                        </div>
                    </div>';
                }
                break;
                
            case 'team':
                foreach ($data as $item) {
                    $html .= '<div class="' . $colClass . ' mb-4">
                        <div class="team-card text-center">
                            <img src="' . base_url('uploads/' . $item->foto) . '" alt="' . esc($item->nama) . '" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <h5 class="fw-bold">' . esc($item->nama) . '</h5>
                            <p class="text-muted">' . esc($item->jabatan) . '</p>
                        </div>
                    </div>';
                }
                break;
                
            case 'berita':
                foreach ($data as $item) {
                    $html .= '<div class="' . $colClass . ' mb-4">
                        <div class="card h-100">
                            <img src="' . base_url('uploads/' . $item->gambar) . '" class="card-img-top" alt="' . esc($item->judul) . '" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">' . esc($item->judul) . '</h6>
                                <p class="card-text text-muted small">' . esc(substr(strip_tags($item->konten), 0, 100)) . '...</p>
                                <a href="' . base_url('berita/detail/' . $item->slug) . '" class="btn btn-sm btn-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>';
                }
                break;
                
            case 'mitra':
                foreach ($data as $item) {
                    $html .= '<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                        <div class="partner-item text-center p-2">
                            <img src="' . base_url('uploads/' . $item->foto) . '" alt="' . esc($item->nama) . '" class="img-fluid" style="max-height: 60px; object-fit: contain;">
                        </div>
                    </div>';
                }
                break;
                
            default:
                $html = '<p class="text-center text-muted">Source type tidak dikenali</p>';
        }
        
        return $html;
    }
}
