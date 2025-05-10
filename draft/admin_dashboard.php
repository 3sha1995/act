<?php
require_once 'Database.php';
require_once 'helpers/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

// Fetch all services with their subservices and programs
$servicesQuery = "SELECT 
    s.service_id, s.service_name,
    ss.subservice_id, ss.subservice_name,
    p.program_id, p.program_name
FROM services s
LEFT JOIN subservices ss ON s.service_id = ss.service_id
LEFT JOIN programs p ON ss.subservice_id = p.subservice_id
ORDER BY s.service_id, ss.subservice_id, p.program_id";

$services = $pdo->query($servicesQuery)->fetchAll(PDO::FETCH_ASSOC);

// Organize the data into a hierarchical structure
$organizedServices = [];
foreach ($services as $row) {
    $serviceId = $row['service_id'];
    $subserviceId = $row['subservice_id'];
    $programId = $row['program_id'];

    if (!isset($organizedServices[$serviceId])) {
        $organizedServices[$serviceId] = [
            'service_name' => $row['service_name'],
            'subservices' => []
        ];
    }

    if ($subserviceId && !isset($organizedServices[$serviceId]['subservices'][$subserviceId])) {
        $organizedServices[$serviceId]['subservices'][$subserviceId] = [
            'subservice_name' => $row['subservice_name'],
            'programs' => []
        ];
    }

    if ($programId) {
        $organizedServices[$serviceId]['subservices'][$subserviceId]['programs'][$programId] = [
            'program_name' => $row['program_name']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMSU Services Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #495057;
        }
        .nav-link:hover {
            background-color: #e9ecef;
        }
        .submenu {
            display: none;
            padding-left: 1rem;
        }
        .submenu.show {
            display: block;
        }
        .search-box {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="search-box">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search programs...">
                </div>
                <nav class="nav flex-column">
                    <?php foreach ($organizedServices as $serviceId => $service): ?>
                        <div class="nav-item">
                            <a class="nav-link service-link" href="#" data-service-id="<?php echo $serviceId; ?>">
                                <i class="bi bi-chevron-right"></i>
                                <?php echo htmlspecialchars($service['service_name']); ?>
                            </a>
                            <div class="submenu" id="service-<?php echo $serviceId; ?>">
                                <?php foreach ($service['subservices'] as $subserviceId => $subservice): ?>
                                    <div class="nav-item">
                                        <a class="nav-link subservice-link" href="#" data-subservice-id="<?php echo $subserviceId; ?>">
                                            <i class="bi bi-chevron-right"></i>
                                            <?php echo htmlspecialchars($subservice['subservice_name']); ?>
                                        </a>
                                        <div class="submenu" id="subservice-<?php echo $subserviceId; ?>">
                                            <?php foreach ($subservice['programs'] as $programId => $program): ?>
                                                <a class="nav-link program-link" href="#" data-program-id="<?php echo $programId; ?>">
                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                            <a class="nav-link text-success" href="#" data-bs-toggle="modal" data-bs-target="#addProgramModal" data-subservice-id="<?php echo $subserviceId; ?>">
                                                <i class="bi bi-plus-circle"></i> Add Program
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a class="nav-link text-success" href="#" data-bs-toggle="modal" data-bs-target="#addSubserviceModal" data-service-id="<?php echo $serviceId; ?>">
                                    <i class="bi bi-plus-circle"></i> Add Subservice
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <a class="nav-link text-success" href="#" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="bi bi-plus-circle"></i> Add Service
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2>Welcome to WMSU Services Admin Dashboard</h2>
                <p>Select a service, subservice, or program from the sidebar to manage its content.</p>
            </div>
        </div>
    </div>

    <!-- Modals will be added here -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle submenus
            $('.service-link').click(function(e) {
                e.preventDefault();
                const serviceId = $(this).data('service-id');
                $(`#service-${serviceId}`).toggleClass('show');
                $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down');
            });

            $('.subservice-link').click(function(e) {
                e.preventDefault();
                const subserviceId = $(this).data('subservice-id');
                $(`#subservice-${subserviceId}`).toggleClass('show');
                $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down');
            });

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                $('.program-link').each(function() {
                    const programName = $(this).text().toLowerCase();
                    if (programName.includes(searchText)) {
                        $(this).show();
                        $(this).closest('.submenu').addClass('show');
                        $(this).closest('.submenu').prev().find('i').removeClass('bi-chevron-right').addClass('bi-chevron-down');
                        $(this).closest('.submenu').closest('.submenu').addClass('show');
                        $(this).closest('.submenu').closest('.submenu').prev().find('i').removeClass('bi-chevron-right').addClass('bi-chevron-down');
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>
</html> 