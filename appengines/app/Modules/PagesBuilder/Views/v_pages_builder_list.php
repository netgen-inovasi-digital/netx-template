<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <label class="card-title mb-0"><?php echo $title ?></label>
                <button id="add" class="btn btn-primary">
                    <i class="bi bi-plus-circle-dotted"></i> Tambah
                </button>
            </div>
            <div class="card-body">
                <table id="data-table" class="saytable border-top-bottom">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th show>Nama Halaman</th>
                            <th>URL</th>
                            <th>Sections</th>
                            <th>Status</th>
                            <th show>Tanggal</th>
                            <th show class="action text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize sayTable dengan konfigurasi untuk PagesBuilder
    table = createTable({
        apiUrl: '<?php echo site_url("pages-builder/datalist") ?>',
    });
    
    addAction();
    
    // Fungsi untuk membuka Page Builder
    function builderPage(event) {
        const id = event.target.closest('div').id;
        loadBuilderView(id);
    }
    
    async function loadBuilderView(pageId) {
        try {
            showLoading();
            
            // Load builder data
            const response = await fetch(`pages-builder/builder/${pageId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                // Hide current content
                document.querySelector('.card').style.display = 'none';
                
                // Create builder view
                const builderHtml = generateBuilderView(data);
                
                // Insert builder view
                const container = document.querySelector('.row .col-md-12');
                container.insertAdjacentHTML('afterbegin', builderHtml);
                
                // Initialize builder functionality
                initializeBuilder(data);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat builder');
        } finally {
            hideLoading();
        }
    }
    
    function generateBuilderView(data) {
        const { page, sections, templates } = data;
        
        return `
        <div id="builderView" class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">
                            🏗️ ${page.nama_page} Builder
                        </h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-link-45deg"></i> /${page.slug}
                            <span class="badge bg-${page.status === 'published' ? 'success' : 'warning'} ms-2">
                                ${page.status}
                            </span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-info" onclick="previewPage('${page.slug}')">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <button class="btn btn-success" onclick="showAddSectionModal()">
                            <i class="bi bi-plus-circle"></i> Tambah Section
                        </button>
                        <button class="btn btn-secondary" onclick="backToList()">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-layers"></i> Sections (${data.sectionsCount})
                </h6>
                
                ${sections && sections.length > 0 ? generateSectionsList(sections) : generateEmptyState()}
            </div>
        </div>
        
        ${generateModals(templates)}
        `;
    }
    
    function generateSectionsList(sections) {
        const sectionsHtml = sections.map(section => {
            const encId = btoa(section.id_layout); // Simple encoding for demo
            return `
            <div class="section-item border rounded p-3 mb-3" data-id="${encId}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-icon bg-primary text-white rounded p-2">
                            <i class="${section.icon || 'bi-square'}"></i>
                        </div>
                        <div>
                            <div class="fw-bold">${section.nama_section}</div>
                            <div class="text-muted small">${section.nama_template || section.kode}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" ${section.status === 'Y' ? 'checked' : ''} 
                                   onchange="toggleSection('${encId}', this)">
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="editSection('${encId}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSection('${encId}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            `;
        }).join('');
        
        return `<div id="sectionsList">${sectionsHtml}</div>`;
    }
    
    function generateEmptyState() {
        return `
        <div class="text-center py-5">
            <i class="bi bi-layout-text-window-reverse" style="font-size: 4rem; color: #dee2e6;"></i>
            <h4 class="mt-3 mb-2">Belum ada section</h4>
            <p class="mb-3">Mulai membangun halaman Anda dengan menambahkan section pertama</p>
            <button class="btn btn-primary" onclick="showAddSectionModal()">
                <i class="bi bi-plus-circle"></i> Tambah Section Pertama
            </button>
        </div>
        `;
    }
    
    function generateModals(templates) {
        const templatesHtml = templates.map(template => `
            <div class="template-card border rounded p-3 mb-2" style="cursor: pointer;" data-template="${template.id_template}">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-primary">
                        <i class="${template.icon || 'bi-square'}"></i>
                    </div>
                    <div>
                        <div class="fw-bold">${template.nama_template}</div>
                        <div class="text-muted small">${template.category || 'General'}</div>
                    </div>
                </div>
            </div>
        `).join('');
        
        return `
        <!-- Add Section Modal -->
        <div class="modal fade" id="addSectionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle"></i> Pilih Template Section
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Section (opsional)</label>
                            <input type="text" class="form-control" id="sectionName" placeholder="Nama section">
                        </div>
                        <div id="templatesContainer" style="max-height: 400px; overflow-y: auto;">
                            ${templatesHtml}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="confirmAddSection()">
                            <i class="bi bi-plus"></i> Tambah Section
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }
    
    function initializeBuilder(data) {
        window.currentPageId = data.pageId;
        window.selectedTemplate = null;
        
        // Template selection handler
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('border-primary'));
                this.classList.add('border-primary');
                window.selectedTemplate = this.dataset.template;
            });
        });
    }
    
    // Builder action functions
    function backToList() {
        document.getElementById('builderView')?.remove();
        document.querySelector('.card').style.display = 'block';
    }
    
    function previewPage(slug) {
        window.open(`/${slug}`, '_blank');
    }
    
    function showAddSectionModal() {
        window.selectedTemplate = null;
        document.getElementById('sectionName').value = '';
        document.querySelectorAll('.template-card').forEach(c => c.classList.remove('border-primary'));
        new bootstrap.Modal(document.getElementById('addSectionModal')).show();
    }
    
    async function confirmAddSection() {
        if (!window.selectedTemplate) {
            alert('Silakan pilih template section');
            return;
        }
        
        const formData = new FormData();
        formData.append('id_page', window.currentPageId);
        formData.append('id_template', window.selectedTemplate);
        formData.append('nama_section', document.getElementById('sectionName').value);
        
        try {
            const response = await fetch('pages-builder/add-section', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('addSectionModal')).hide();
                // Reload builder view
                const currentPageEncId = Object.keys(window.currentPageData || {})[0];
                if (currentPageEncId) {
                    loadBuilderView(currentPageEncId);
                }
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menambah section');
        }
    }
    
    async function toggleSection(sectionId, toggle) {
        const status = toggle.checked ? 'Y' : 'N';
        
        const formData = new FormData();
        formData.append('id', sectionId);
        formData.append('status', status);
        
        try {
            const response = await fetch('pages-builder/toggle-section', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status !== 'success') {
                // Revert toggle if failed
                toggle.checked = !toggle.checked;
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            toggle.checked = !toggle.checked;
            alert('Terjadi kesalahan saat mengubah status section');
        }
    }
    
    function editSection(sectionId) {
        alert('Edit Section feature will be implemented next');
    }
    
    async function deleteSection(sectionId) {
        if (!confirm('Apakah Anda yakin ingin menghapus section ini?')) return;
        
        const formData = new FormData();
        formData.append('id', sectionId);
        
        try {
            const response = await fetch('pages-builder/delete-section', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                // Remove section from view
                document.querySelector(`[data-id="${sectionId}"]`)?.remove();
                
                // Update sections count
                const countElement = document.querySelector('.bi-layers').parentNode;
                const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]);
                countElement.innerHTML = `<i class="bi bi-layers"></i> Sections (${currentCount - 1})`;
                
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus section');
        }
    }
</script>

<div class="modal fade" id="modalForm" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="margin: 2% auto">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Halaman Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <?php echo form_open('pages-builder/submit', array('id' => 'myform', 'novalidate' => '')) ?>
            <div class="modal-body">
                <input type="hidden" value="" name="id" />
                
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Nama Halaman *</label>
                    <div class="col">
                        <input name="nama_page" type="text" class="form-control" placeholder="Masukkan nama halaman" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">URL Slug *</label>
                    <div class="col">
                        <input name="slug" type="text" class="form-control" placeholder="url-halaman" required>
                        <small class="form-text text-muted">URL akan otomatis dibuat dari nama halaman</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Meta Title</label>
                    <div class="col">
                        <input name="meta_title" type="text" class="form-control" placeholder="Judul untuk SEO">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Meta Description</label>
                    <div class="col">
                        <textarea name="meta_description" class="form-control" rows="2" placeholder="Deskripsi untuk mesin pencari"></textarea>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Status</label>
                    <div class="col">
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <!-- <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Set as Homepage</label>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_homepage" value="Y" id="is_homepage">
                            <label class="form-check-label" for="is_homepage">
                                Jadikan halaman utama website
                            </label>
                        </div>
                    </div>
                </div> -->
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button class="btn btn-success" type="submit">
                    <i class="bi bi-check2-circle"></i> Simpan
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto generate slug from nama_page
document.querySelector('input[name="nama_page"]').addEventListener('input', function() {
    const namaPage = this.value;
    const slug = namaPage
        .toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')     // Replace spaces with hyphens
        .replace(/--+/g, '-')     // Replace multiple hyphens with single hyphen
        .trim();
    document.querySelector('input[name="slug"]').value = slug;
});
</script>
