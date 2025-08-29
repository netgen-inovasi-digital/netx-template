<style>
    .notice-swiper .swiper-wrapper {
        padding-bottom: 30px;
        /* agar pagination tidak ketutup */
    }

    .notice-swiper .swiper-slide {
        height: auto;
    }

    /* Custom section styles */
    .hero-section {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        color: white;
    }
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6));
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-content {
        text-align: center;
        z-index: 2;
    }
    .service-card, .team-card {
        transition: transform 0.3s ease;
    }
    .service-card:hover, .team-card:hover {
        transform: translateY(-5px);
    }
</style>

<?php
// Load Dynamic Template Renderer
use App\Libraries\DynamicTemplateRenderer;
$renderer = new DynamicTemplateRenderer();
?>

<?php foreach ($getLayout as $layout): ?>
    <?php
    $kode = $layout->kode;
    $konten = json_decode($layout->konten_dinamis ?? '{}', true);
    
    // Check if this is a dynamic template from database
    if (isset($layout->id_template) && !empty($layout->id_template)):
        // Get template HTML from database
        $templateModel = new \App\Models\MyModel('section_templates');
        $template = $templateModel->getDataById('id_template', $layout->id_template);
        
        if ($template && $template->template_html):
            // Render dynamic template
            echo $renderer->render($template->template_html, $konten);
        else:
            echo '<div class="alert alert-warning">Template tidak ditemukan untuk section: ' . esc($layout->nama_section) . '</div>';
        endif;
        
    else:
        // Fallback to legacy static templates
        if ($kode == 'hero'): ?>
            <!-- LEGACY HERO SECTION -->
            <section id="hero" class="hero-section">
                <div class="swiper hero-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($getHero as $hero): ?>
                            <div class="swiper-slide hero-slide" style="background-image: url('<?= base_url('uploads/' . $hero->foto) ?>');">
                                <div class="hero-overlay">
                                    <div class="container">
                                        <div class="hero-content">
                                            <h2><?= esc($hero->judul) ?></h2>
                                            <p><?= esc($hero->deskripsi) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination hero-pagination"></div>
                </div>
            </section>

        <?php elseif ($kode == 'layanan'): ?>
            <!-- LEGACY LAYANAN SECTION -->
            <section id="services" class="services-section py-5 bg-light">
                <div class="container text-center">
                    <h2 class="section-title fw-bold mb-3"><?= esc($konten['judul'] ?? 'Layanan') ?></h2>
                    <h6 class="section-desc mb-5"><?= esc($konten['deskripsi'] ?? 'Deskripsi layanan...') ?></h6>

                    <div class="row justify-content-center g-4">
                        <?php foreach ($getLayanan as $layanan): ?>
                            <div class="col-sm-8 col-md-8 col-lg-4">
                                <a href="<?= $layanan->link != '' ?  base_url('/hal/' . $layanan->link . '') : '#' ?>" class="text-decoration-none">
                                    <div class="service-card bg-white p-3 rounded shadow-sm h-100">
                                        <img src="<?= base_url('uploads/' . $layanan->foto) ?>" alt="<?= esc($layanan->judul) ?>" class="img-fluid mb-3" style="height: 80px; object-fit: contain;">
                                        <h5 class="fw-bold"><?= esc($layanan->judul) ?></h5>
                                        <p class="text-muted small"><?= esc($layanan->deskripsi) ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </section>

        <?php elseif ($kode == 'team'): ?>
            <!-- LEGACY TEAM SECTION -->
            <section id="team" class="team-section py-5">
                <div class="container text-center">
                    <h2 class="section-title fw-bold mb-3"><?= esc($konten['judul'] ?? 'Tim Kami') ?></h2>
                    <h6 class="section-desc mb-5"><?= esc($konten['deskripsi'] ?? 'Meet our amazing team') ?></h6>

                    <div class="row justify-content-center g-4">
                        <?php foreach ($getTeam as $team): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="team-card text-center">
                                    <img src="<?= base_url('uploads/' . $team->foto) ?>" alt="<?= esc($team->nama) ?>" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                                    <h5 class="fw-bold"><?= esc($team->nama) ?></h5>
                                    <p class="text-muted"><?= esc($team->jabatan) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        <?php elseif ($kode == 'berita'): ?>
            <!-- LEGACY BERITA SECTION -->
            <section id="news" class="py-5 bg-light">
                <div class="container text-center">
                    <h2 class="fw-bold section-title mb-4"><?= esc($konten['judul'] ?? 'Berita & Artikel') ?></h2>
                    <h6 class="mb-4 section-desc"><?= esc($konten['deskripsi'] ?? 'Informasi terbaru dan artikel menarik') ?></h6>
                    <div class="swiper notice-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($getBerita as $berita): ?>
                                <div class="swiper-slide">
                                    <div class="card h-100">
                                        <img src="<?= base_url('uploads/' . $berita->gambar) ?>" class="card-img-top" alt="<?= esc($berita->judul) ?>" style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= esc($berita->judul) ?></h6>
                                            <p class="card-text text-muted small"><?= esc(substr(strip_tags($berita->konten), 0, 100)) ?>...</p>
                                            <a href="<?= base_url('berita/detail/' . $berita->slug) ?>" class="btn btn-sm btn-primary">Baca Selengkapnya</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </section>

        <?php elseif ($kode == 'mitra'): ?>
            <!-- LEGACY MITRA SECTION -->
            <section id="partner" class="py-5 bg-light">
                <div class="container text-center">
                    <h2 class="fw-bold section-title mb-4"><?= esc($konten['judul'] ?? 'Mitra dan Partner Kami') ?></h2>
                    <h6 class="mb-4 section-desc"><?= esc($konten['deskripsi'] ?? 'Kami bekerja sama dengan berbagai institusi terpercaya') ?></h6>
                    <div class="swiper partner-slider">
                        <div class="swiper-wrapper align-items-center">
                            <?php foreach ($getMitra as $mitra): ?>
                                <div class="swiper-slide">
                                    <div class="partner-item text-center p-3">
                                        <img src="<?= base_url('uploads/' . $mitra->foto) ?>" alt="<?= esc($mitra->nama) ?>" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ($kode == 'pengumuman'): ?>
            <!-- LEGACY PENGUMUMAN SECTION -->
            <section class="py-5">
                <div class="container">
                    <h2 class="fw-bold section-title mb-4 text-center"><?= esc($konten['judul'] ?? 'Pengumuman') ?></h2>
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <?php foreach ($getPengumuman as $pengumuman): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= esc($pengumuman->judul) ?></h5>
                                        <p class="card-text"><?= esc(substr(strip_tags($pengumuman->konten), 0, 200)) ?>...</p>
                                        <small class="text-muted"><?= date('d M Y', strtotime($pengumuman->tanggal)) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php else: ?>
            <!-- UNKNOWN SECTION TYPE -->
            <div class="alert alert-info">
                <strong>Section:</strong> <?= esc($layout->nama_section ?? $kode) ?> 
                <small>(Template belum didefinisikan)</small>
            </div>

        <?php endif;
    endif; ?>

<?php endforeach; ?>

<script>
// Initialize Swiper for hero
document.addEventListener('DOMContentLoaded', function() {
    // Hero Slider
    if (document.querySelector('.hero-slider')) {
        new Swiper('.hero-slider', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.hero-pagination',
                clickable: true,
            },
        });
    }
    
    // News/Notice Slider
    if (document.querySelector('.notice-swiper')) {
        new Swiper('.notice-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                },
            },
        });
    }
    
    // Partner Slider
    if (document.querySelector('.partner-slider')) {
        new Swiper('.partner-slider', {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
            },
            breakpoints: {
                768: {
                    slidesPerView: 4
                },
                992: {
                    slidesPerView: 6
                },
            },
        });
    }
});
</script>
