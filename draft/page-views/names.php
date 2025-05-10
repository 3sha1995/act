<?php
/**
 * Service: names
 * Created: 2025-04-27 16:39:39
 */

// Include database connection
require_once '../config/database.php';

// Get service details from database
$service = db_get_row("SELECT * FROM services WHERE id = ?", [2]);

// Get service requirements
$requirements = db_get_all("SELECT * FROM service_requirements WHERE service_id = ? ORDER BY sort_order", [2]);

// Get service steps
$steps = db_get_all("SELECT * FROM service_steps WHERE service_id = ? ORDER BY step_order", [2]);
?>

<!-- Service Template -->
<div class="container service-container">
    <h1><?php echo $service['name']; ?></h1>
    <div class="service-description">
        <p><?php echo $service['description'] ?? 'This is a template for the names service.'; ?></p>
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
                <p><strong>Fee:</strong> PHP <?php echo number_format($service['fee'], 2); ?></p>
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
