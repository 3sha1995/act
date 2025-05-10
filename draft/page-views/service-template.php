<?php
/**
 * WMSU Services - Service Template
 * 
 * This is a base template for all services created through the admin panel.
 * When a new service is added, a copy of this template is created with specific
 * data for that service.
 */

// Include the template views manager
require_once 'template_views.php';

// The service slug should be set based on the filename (without .php)
$current_file = basename(__FILE__, '.php');
$service_slug = $current_file;

// Get service data from template_views array
$service = get_template_view($service_slug);

// If service not found in template_views, show error
if (!$service) {
    include '../404.php';
    exit;
}

// Set page title
$page_title = $service['name'];

// Include header
include '../includes/header.php';

// Check if private service and user not logged in
if ($service['is_public'] == 0 && !isset($_SESSION['user_logged_in'])) {
    // Redirect to login
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// In a real application, get service details from database
// $service_details = $db->query("SELECT * FROM student_services WHERE id = ?", [$service['id']])->fetch();
// $requirements = $db->query("SELECT * FROM service_requirements WHERE service_id = ? ORDER BY display_order", [$service['id']])->fetchAll();
// $steps = $db->query("SELECT * FROM service_steps WHERE service_id = ? ORDER BY display_order", [$service['id']])->fetchAll();

// For demonstration, use sample data
$service_details = [
    'description' => 'This is a sample description for the ' . $service['name'] . ' service.',
    'contact_office' => 'Sample Office',
    'contact_number' => '(123) 456-7890',
    'fee' => 100.00,
    'fee_details' => 'Per transaction',
    'processing_time' => '2-3 working days'
];

$requirements = [
    ['text' => 'Requirement 1'],
    ['text' => 'Requirement 2'],
    ['text' => 'Requirement 3']
];

$steps = [
    [
        'title' => 'Submit Application',
        'description' => 'Visit the office and submit your application form along with the requirements.'
    ],
    [
        'title' => 'Payment',
        'description' => 'Pay the processing fee at the cashier\'s office.'
    ],
    [
        'title' => 'Receive Service',
        'description' => 'Return to collect your document or receive the service.'
    ]
];
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4"><?php echo htmlspecialchars($service['name']); ?></h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">About This Service</h5>
                    <p class="card-text"><?php echo htmlspecialchars($service_details['description']); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Requirements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requirements)): ?>
                        <p class="text-muted">No specific requirements listed.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($requirements as $requirement): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($requirement['text']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Process Steps</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($steps)): ?>
                        <p class="text-muted">No process steps defined.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($steps as $index => $step): ?>
                                <div class="timeline-item mb-4">
                                    <h6 class="font-weight-bold">Step <?php echo $index + 1; ?>: <?php echo htmlspecialchars($step['title']); ?></h6>
                                    <p><?php echo htmlspecialchars($step['description']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Service Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Status:</strong> <?php echo $service['is_public'] ? '<span class="badge badge-success">Public</span>' : '<span class="badge badge-secondary">Private</span>'; ?></li>
                        <li class="mb-2"><strong>Office:</strong> <?php echo htmlspecialchars($service_details['contact_office']); ?></li>
                        <li class="mb-2"><strong>Contact:</strong> <?php echo htmlspecialchars($service_details['contact_number']); ?></li>
                        <li class="mb-2"><strong>Fee:</strong> ₱<?php echo number_format($service_details['fee'], 2); ?></li>
                        <li class="mb-2"><strong>Fee Details:</strong> <?php echo htmlspecialchars($service_details['fee_details']); ?></li>
                        <li><strong>Processing Time:</strong> <?php echo htmlspecialchars($service_details['processing_time']); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p>If you have questions about this service, please contact:</p>
                    <p><i class="fas fa-envelope mr-2"></i> support@wmsu.edu.ph</p>
                    <p><i class="fas fa-phone mr-2"></i> (123) 456-7890</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 