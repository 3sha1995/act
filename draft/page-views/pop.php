<?php
/**
 * Service: pop
 * Created: 2025-04-27 16:51:34
 */

// Load template_views.php which contains this service data
require_once 'template_views.php';

// Get service data
$service = get_template_view('pop');

// Check if service exists
if (!$service) {
    echo "Service not found!";
    exit;
}

// Default empty arrays for requirements and steps
$requirements = [];
$steps = [];

// If database connection is available, get more details
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    // Try to get requirements and steps from database
    if (function_exists('db_get_all')) {
        $requirements = db_get_all("SELECT * FROM service_requirements WHERE service_id = ? ORDER BY sort_order", [$service['id']]);
        $steps = db_get_all("SELECT * FROM service_steps WHERE service_id = ? ORDER BY step_order", [$service['id']]);
        // Get more service details if available
        $service_details = db_get_row("SELECT * FROM services WHERE id = ?", [$service['id']]);
        if ($service_details) {
            $service = array_merge($service, $service_details);
        }
    }
}
?>

<!-- Service Template -->
<div class="container service-container">
    <h1><?php echo $service['name']; ?></h1>
    <div class="service-description">
        <p><?php echo $service['description'] ?? 'This is a template for the pop service.'; ?></p>
    </div>

    <div class="service-details">
        <div class="row">
            <?php if (!empty($service['contact_office']) || !empty($service['contact_number'])): ?>
            <div class="col-md-6">
                <h4>Contact Information</h4>
                <?php if (!empty($service['contact_office'])): ?>
                <p><strong>Office:</strong> <?php echo $service['contact_office']; ?></p>
                <?php endif; ?>
                <?php if (!empty($service['contact_number'])): ?>
                <p><strong>Contact Number:</strong> <?php echo $service['contact_number']; ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($service['fee']) || !empty($service['fee_details'])): ?>
            <div class="col-md-6">
                <h4>Fee Information</h4>
                <p><strong>Fee:</strong> PHP <?php echo number_format($service['fee'] ?? 0, 2); ?></p>
                <?php if (!empty($service['fee_details'])): ?>
                <p><strong>Details:</strong> <?php echo $service['fee_details']; ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($requirements)): ?>
    <div class="service-requirements mt-4">
        <h3>Requirements</h3>
        <ul class="list-group">
            <?php foreach($requirements as $req): ?>
            <li class="list-group-item"><?php echo $req['requirement']; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if(!empty($steps)): ?>
    <div class="service-process mt-4">
        <h3>Process</h3>
        <ol class="list-group list-group-numbered">
            <?php foreach($steps as $step): ?>
            <li class="list-group-item"><?php echo $step['description']; ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>

    <?php if(!empty($service['notes'])): ?>
    <div class="service-notes mt-4">
        <h3>Additional Notes</h3>
        <div class="alert alert-info">
            <?php echo $service['notes']; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
