<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>

<style>
    .page-builder {
        min-height: 100vh;
        background: #f8f9fa;
    }
    .sections-list {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .section-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 8px;
        background: white;
        transition: all 0.2s ease;
        cursor: move;
    }
    .section-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    .section-item.dragging {
        opacity: 0.5;
        transform: rotate(2deg);
    }
    .section-header {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .section-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .section-icon.header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; }
    .section-icon.content { background: linear-gradient(45deg, #f093fb, #f5576c); color: white; }
    .section-icon.advanced { background: linear-gradient(45deg, #4facfe, #00f2fe); color: white; }
    .section-icon.general { background: linear-gradient(45deg, #43e97b, #38f9d7); color: white; }
    
    .section-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .drag-handle {
        cursor: move;
        color: #6c757d;
        font-size: 1.2rem;
    }
    .toggle-switch {
        position: relative;
        width: 44px;
        height: 24px;
        background: #ccc;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .toggle-switch.active {
        background: #28a745;
    }
    .toggle-switch::before {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: transform 0.2s;
    }
    .toggle-switch.active::before {
        transform: translateX(20px);
    }
    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        max-height: 60vh;
        overflow-y: auto;
    }
    .template-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }
    .template-card:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.15);
    }
    .template-card.selected {
        border-color: #007bff;
        background: #f8f9ff;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .form-builder {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>

<div class="page-builder">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm">
                    <div>
                        <h3 class="fw-bold mb-1">
                            🏗️ <?= esc($page['nama_page']) ?> Builder
                        </h3>
                        <p class="text-muted mb-0">
                            <i class="bi bi-link-45deg"></i> <?= base_url($page['slug']) ?>
                            <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'warning' ?> ms-2">
                                <?= ucfirst($page['status']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url($page['slug']) ?>" target="_blank" class="btn btn-outline-info">
                            <i class="bi bi-eye"></i> Preview
                        </a>
                        <button class="btn btn-success" id="addSectionBtn">
                            <i class="bi bi-plus-circle"></i> Tambah Section
                        </button>
                        <a href="<?= base_url('pages') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-12">
                <div class="sections-list p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-layers"></i> Sections (<?= is_array($sections) ? count($sections) : (is_object($sections) ? count((array)$sections) : 0) ?>)
                    </h5>
                    
                    <?php if (empty($sections)): ?>
                        <div class="empty-state">
                            <i class="bi bi-layout-text-window-reverse" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="mt-3 mb-2">Belum ada section</h4>
                            <p class="mb-3">Mulai membangun halaman Anda dengan menambahkan section pertama</p>
                            <button class="btn btn-primary" id="addFirstSection">
                                <i class="bi bi-plus-circle"></i> Tambah Section Pertama
                            </button>
                        </div>
                    <?php else: ?>
                        <div id="sectionsList" class="sortable">
                            <?php foreach ($sections as $section): ?>
                                <?php $encId = bin2hex(\Config\Services::encrypter()->encrypt($section->id_layout)); ?>
                                <div class="section-item" data-id="<?= $encId ?>">
                                    <div class="section-header">
                                        <div class="section-info">
                                            <div class="section-icon <?= $section->category ?? 'general' ?>">
                                                <i class="<?= $section->icon ?? 'bi-square' ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= esc($section->nama_section) ?></div>
                                                <div class="text-muted small">
                                                    <?= esc($section->nama_template ?? $section->kode) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="section-actions">
                                            <div class="toggle-switch <?= $section->status === 'Y' ? 'active' : '' ?>" 
                                                 onclick="toggleSection('<?= $encId ?>', this)">
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editSection('<?= $encId ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteSection('<?= $encId ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <div class="drag-handle">
                                                <i class="bi bi-grip-vertical"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Pilih Template Section
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="sectionName" 
                           placeholder="Nama section (opsional)">
                </div>
                <div class="templates-grid">
                    <?php foreach ($templates as $template): ?>
                        <div class="template-card" data-template="<?= $template->id_template ?>">
                            <div class="d-flex align-items-center mb-2">
                                <div class="section-icon <?= $template->category ?> me-3">
                                    <i class="<?= $template->icon ?>"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= esc($template->nama_template) ?></div>
                                    <div class="text-muted small"><?= esc($template->category) ?></div>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                <?= esc($template->deskripsi) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmAddSection">
                    <i class="bi bi-plus"></i> Tambah Section
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Edit Section
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSectionForm">
                    <div class="mb-3">
                        <label class="form-label">Nama Section</label>
                        <input type="text" class="form-control" id="editSectionName" required>
                    </div>
                    <div id="dynamicFormFields" class="form-builder">
                        <!-- Dynamic fields will be loaded here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">
                    <i class="bi bi-check"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Global variables
let selectedTemplate = null;
let editingSectionId = null;
const pageId = <?= $page['id_page'] ?>;

// Initialize sortable
if (document.getElementById('sectionsList')) {
    new Sortable(document.getElementById('sectionsList'), {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function(evt) {
            updateSectionOrder();
        }
    });
}

// Event listeners
document.getElementById('addSectionBtn')?.addEventListener('click', showAddSectionModal);
document.getElementById('addFirstSection')?.addEventListener('click', showAddSectionModal);
document.getElementById('confirmAddSection')?.addEventListener('click', addSection);
document.getElementById('saveSectionBtn')?.addEventListener('click', saveSection);

// Template selection
document.querySelectorAll('.template-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedTemplate = this.dataset.template;
    });
});

function showAddSectionModal() {
    selectedTemplate = null;
    document.getElementById('sectionName').value = '';
    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
    new bootstrap.Modal(document.getElementById('addSectionModal')).show();
}

async function addSection() {
    if (!selectedTemplate) {
        alert('Silakan pilih template section');
        return;
    }

    const formData = new FormData();
    formData.append('id_page', pageId);
    formData.append('id_template', selectedTemplate);
    formData.append('nama_section', document.getElementById('sectionName').value);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    try {
        const response = await fetch('<?= base_url('pages-builder/add-section') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah section');
    }
}

async function editSection(sectionId) {
    editingSectionId = sectionId;
    
    try {
        const response = await fetch(`<?= base_url('pages-builder/edit-section') ?>/${sectionId}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const section = data.data;
            document.getElementById('editSectionName').value = section.nama_section;
            
            // Build dynamic form
            buildDynamicForm(section.form_fields, section.konten_dinamis);
            
            new bootstrap.Modal(document.getElementById('editSectionModal')).show();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data section');
    }
}

function buildDynamicForm(formFields, currentData) {
    const container = document.getElementById('dynamicFormFields');
    container.innerHTML = '';
    
    formFields.forEach(field => {
        const div = document.createElement('div');
        div.className = 'mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = field.label;
        if (field.required) label.innerHTML += ' <span class="text-danger">*</span>';
        
        let input;
        const currentValue = currentData[field.name] || field.default || '';
        
        switch (field.type) {
            case 'text':
                input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.value = currentValue;
                break;
                
            case 'textarea':
                input = document.createElement('textarea');
                input.className = 'form-control';
                input.rows = 3;
                input.value = currentValue;
                break;
                
            case 'wysiwyg':
                input = document.createElement('textarea');
                input.className = 'form-control wysiwyg-editor';
                input.rows = 5;
                input.value = currentValue;
                break;
                
            case 'select':
                input = document.createElement('select');
                input.className = 'form-select';
                field.options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value === currentValue) opt.selected = true;
                    input.appendChild(opt);
                });
                break;
                
            case 'number':
                input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control';
                input.value = currentValue;
                break;
                
            case 'checkbox':
                input = document.createElement('div');
                input.className = 'form-check';
                input.innerHTML = `
                    <input class="form-check-input" type="checkbox" ${currentValue ? 'checked' : ''}>
                    <label class="form-check-label">${field.label}</label>
                `;
                break;
                
            case 'file':
                input = document.createElement('input');
                input.type = 'file';
                input.className = 'form-control';
                if (field.accept) input.accept = field.accept;
                if (currentValue) {
                    const preview = document.createElement('div');
                    preview.className = 'mt-2';
                    preview.innerHTML = `<small class="text-muted">File saat ini: ${currentValue}</small>`;
                    div.appendChild(preview);
                }
                break;
                
            case 'code':
                input = document.createElement('textarea');
                input.className = 'form-control font-monospace';
                input.rows = 6;
                input.value = currentValue;
                input.style.fontSize = '0.9em';
                break;
                
            default:
                input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.value = currentValue;
        }
        
        input.name = field.name;
        if (field.required) input.required = true;
        
        div.appendChild(label);
        div.appendChild(input);
        container.appendChild(div);
    });
}

async function saveSection() {
    const form = document.getElementById('editSectionForm');
    const formData = new FormData();
    
    formData.append('id', editingSectionId);
    formData.append('nama_section', document.getElementById('editSectionName').value);
    
    // Collect form data
    const dynamicData = {};
    form.querySelectorAll('input, textarea, select').forEach(input => {
        if (input.name && input.name !== 'nama_section') {
            if (input.type === 'checkbox') {
                dynamicData[input.name] = input.checked;
            } else if (input.type === 'file' && input.files.length > 0) {
                // Handle file upload separately
                formData.append(input.name, input.files[0]);
            } else {
                dynamicData[input.name] = input.value;
            }
        }
    });
    
    formData.append('form_data', JSON.stringify(dynamicData));
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    try {
        const response = await fetch('<?= base_url('pages-builder/save-section') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan section');
    }
}

async function toggleSection(sectionId, toggle) {
    const status = toggle.classList.contains('active') ? 'N' : 'Y';
    
    const formData = new FormData();
    formData.append('id', sectionId);
    formData.append('status', status);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    try {
        const response = await fetch('<?= base_url('pages-builder/toggle-section') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            toggle.classList.toggle('active');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengubah status section');
    }
}

async function deleteSection(sectionId) {
    if (!confirm('Apakah Anda yakin ingin menghapus section ini?')) return;
    
    const formData = new FormData();
    formData.append('id', sectionId);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    try {
        const response = await fetch('<?= base_url('pages-builder/delete-section') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus section');
    }
}

async function updateSectionOrder() {
    const sections = Array.from(document.querySelectorAll('.section-item')).map(item => item.dataset.id);
    
    const formData = new FormData();
    formData.append('sections', JSON.stringify(sections));
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    try {
        await fetch('<?= base_url('pages-builder/reorder-sections') ?>', {
            method: 'POST',
            body: formData
        });
    } catch (error) {
        console.error('Error updating order:', error);
    }
}
</script>

<?= $this->endSection(); ?>
