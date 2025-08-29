<?php

if (!isset($routes)) {
    $routes = \Config\Services::routes(true);
}

// Pages Builder routes (Advanced page builder with sections)
$routes->group('pages-builder', ['namespace' => 'Modules\PagesBuilder\Controllers'], function ($subroutes) {
    // Main pages management - Routes spesifik dulu
    $subroutes->get('/', 'PagesBuilder::index');
    $subroutes->get('(:any)', 'PagesBuilder::$1');

        // Page CRUD operations
    $subroutes->post('submit', 'PagesBuilder::submit');
    $subroutes->post('delete', 'PagesBuilder::delete');  // POST delete untuk form
    $subroutes->post('edit', 'PagesBuilder::edit');
    
    $subroutes->get('builder/(:any)', 'PagesBuilder::builder/$1');
    $subroutes->get('edit-section/(:any)', 'PagesBuilder::editSection/$1');

    
    // Section management operations
    $subroutes->post('add-section', 'PagesBuilder::addSection');
    $subroutes->post('save-section', 'PagesBuilder::saveSection');
    $subroutes->post('delete-section', 'PagesBuilder::deleteSection');
    $subroutes->post('toggle-section', 'PagesBuilder::toggleSection');
    $subroutes->post('reorder-sections', 'PagesBuilder::reorderSections');
});
