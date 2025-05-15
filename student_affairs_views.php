<?php
// Database connection
require_once __DIR__ . '/../cms/db_connection.php';

try {
    $pdo = getPDOConnection();
    
    // Fetch about content
    $stmt = $pdo->prepare("SELECT * FROM af_page WHERE id = 1");
    $stmt->execute();
    $aboutContent = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format image path if needed
    if ($aboutContent && strpos($aboutContent['image_path'], 'uploads/') === 0) {
        $aboutContent['image_path'] = '../' . $aboutContent['image_path'];
    }

    // Fetch section title
    $stmt = $pdo->prepare("SELECT section_title FROM af_page_officer LIMIT 1");
    $stmt->execute();
    $titleResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $sectionTitle = $titleResult ? $titleResult['section_title'] : 'Meet Our Officers';

    // Fetch Mission & Vision content
    $mvContent = [];
    
    // Fetch main MV section
    $stmt = $pdo->prepare("SELECT * FROM af_page_mv WHERE id = 1");
    $stmt->execute();
    $mvContent['mv'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch Mission section
    $stmt = $pdo->prepare("SELECT * FROM af_page_mission WHERE id = 1");
    $stmt->execute();
    $mvContent['mission'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch Vision section
    $stmt = $pdo->prepare("SELECT * FROM af_page_vision WHERE id = 1");
    $stmt->execute();
    $mvContent['vision'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch Objectives section
    $stmt = $pdo->prepare("SELECT * FROM af_page_obj WHERE id = 1");
    $stmt->execute();
    $objectivesContent = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch Functions section
    $stmt = $pdo->prepare("SELECT * FROM af_page_funct WHERE id = 1");
    $stmt->execute();
    $functionsContent = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $aboutContent = null;
    $sectionTitle = 'Meet Our Officers';
    $mvContent = null;
    $objectivesContent = null;
    $functionsContent = null;
}

// Helper function to handle image paths
function getImagePath($path) {
    if (empty($path)) {
        return '../imgs/cte.jpg';
    }
    return strpos($path, 'uploads/') === 0 ? '../' . $path : $path;
}

// Only display if content exists and is visible
if ($aboutContent && $aboutContent['is_visible']):
  
?>
<!-- New About Section -->
<section class="student_affairs_about_section">
    <div class="student_affairs_about_container">
   
    <div class="student_affairs_about_header scroll-fade">
    <div class="student_affairs_about_ontop_title"><?php echo htmlspecialchars($aboutContent['ontop_title']); ?></div>
    <h2 class="student_affairs_about_title"><?php echo htmlspecialchars($aboutContent['main_title']); ?></h2>
    <div class="student_affair_about_divider"></div>
    </div>
   
     
        <div class="student_affairs_about_content scroll-fade">
            <div class="student_affairs_about_image">
                <img src="<?php echo htmlspecialchars($aboutContent['image_path']); ?>" alt="WMSU Health Center">
            </div>
            
            <div class="student_affairs_about_text">
                <div class="student_affairs_about_description">
                    <?php echo $aboutContent['description']; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<?php endif;

// Fetch officers from database
try {
    // First check section visibility
    $stmt = $pdo->query("SELECT section_visible FROM af_page_officer LIMIT 1");
    $sectionVisibility = $stmt->fetch(PDO::FETCH_ASSOC);
    $isSectionVisible = $sectionVisibility ? $sectionVisibility['section_visible'] : 1;

    // Only fetch officers if section is visible
    $officers = [];
    if ($isSectionVisible) {
        $stmt = $pdo->query("SELECT * FROM af_page_officer WHERE is_visible = 1 ORDER BY id DESC");
        $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching officers: " . $e->getMessage());
    $officers = [];
    $isSectionVisible = 0;
}
?>

<script>
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.2
    });

    document.querySelectorAll('.scroll-fade').forEach(el => {
        observer.observe(el);
    });
</script>


<section class="student_affair_mv_wrapper">
    <?php if ($mvContent && ($mvContent['mv']['is_visible'] ?? 1)): ?>
        <h2 class="student_affair_mv_main_title"><?= htmlspecialchars($mvContent['mv']['section_title'] ?? 'MISSION AND VISION') ?></h2>
        <div class="student_affair_mv_divider"></div>
    <section class="student_affair_mv_container">
        <?php if ($mvContent['mission']['is_visible'] ?? 1): ?>
        <div class="student_affair_mv_box_mission" onclick="expandSection(this, 'mission')">
            <img src="<?= htmlspecialchars(getImagePath($mvContent['mission']['image_url'])) ?>" alt="WMSU Mission">
            <div class="student_affair_mv_overlay_mission"></div>
            <div class="student_affair_mv_content">
                <h2 class="student_affair_mv_title"><?= htmlspecialchars($mvContent['mission']['section_title'] ?? 'MISSION') ?></h2>
                <div class="student_affair_mv_show_more">
                    <span class="show_more_text"><?= htmlspecialchars($mvContent['mission']['show_more_text'] ?? 'SHOW MORE') ?></span>
                </div>
                <div class="student_affair_mv_full_content">
                    <?= $mvContent['mission']['description'] ?? 'Mission content not available.' ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($mvContent['vision']['is_visible'] ?? 1): ?>
        <div class="student_affair_mv_box_vision" onclick="expandSection(this, 'vision')">
            <img src="<?= htmlspecialchars(getImagePath($mvContent['vision']['image_url'])) ?>" alt="WMSU Vision">
            <div class="student_affair_mv_overlay_vision"></div>
            <div class="student_affair_mv_content">
                <h2 class="student_affair_mv_title"><?= htmlspecialchars($mvContent['vision']['section_title'] ?? 'VISION') ?></h2>
                <div class="student_affair_mv_show_more">
                    <span class="show_more_text"><?= htmlspecialchars($mvContent['vision']['show_more_text'] ?? 'SHOW MORE') ?></span>
                </div>
                <div class="student_affair_mv_full_content">
                    <?= $mvContent['vision']['description'] ?? 'Vision content not available.' ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</section>

<!-- Objectives Section -->
<section class="student_affairs_objectives_section">
    <?php if ($objectivesContent && $objectivesContent['is_visible']): ?>
    <div class="student_affairs_objectives_container">
        <h2 class="student_affairs_objectives_title"><?= htmlspecialchars($objectivesContent['section_title']) ?></h2>
        <div class="student_affairs_objectives_divider"></div>
        
        <div class="student_affairs_objectives_content">
        <p class="student_affairs_objectives_description">  <?= $objectivesContent['description'] ?> 
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- Functions Section -->
<section class="student_affairs_functions_section">
    <?php if ($functionsContent && $functionsContent['is_visible']): ?>
    <div class="student_affairs_functions_container">
        <h2 class="student_affairs_functions_title"><?= htmlspecialchars($functionsContent['section_title']) ?></h2>
        <div class="student_affairs_functions_divider"></div>
        
        <div class="student_affairs_functions_content">
            <?= $functionsContent['description'] ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php
// Fetch services content
try {
    // Fetch main section content
    $stmt = $pdo->query("SELECT * FROM af_page_services_main WHERE id = 1");
    $servicesMain = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch all visible services
    $stmt = $pdo->query("SELECT * FROM af_page_services WHERE is_visible = 1 ORDER BY created_at DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
    $servicesMain = null;
    $services = [];
}
?>

<section class="student_affairs_services_section" style="display: <?= ($servicesMain && $servicesMain['is_visible']) ? 'block' : 'none' ?>">
    <div class="student_affairs_services_container">
        <div class="student_affairs_services_header">
            <h2 class="student_affairs_services_title"><?= htmlspecialchars($servicesMain['section_title'] ?? 'Our Services') ?></h2>
            <div class="student_affairs_services_divider"></div>
            <p class="student_affairs_services_description"><?= $servicesMain['section_description'] ?? 'We provide a range of services to support our university community.' ?></p>
        </div>
        <div class="student_affairs_services_grid">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <div class="student_affairs_service_card">
                        <div class="student_affairs_service_icon">
                            <?php if (strpos($service['icon_class'], 'http') === 0 || strpos($service['icon_class'], '../') === 0 || strpos($service['icon_class'], 'uploads/') === 0): ?>
                                <img src="<?= htmlspecialchars($service['icon_class']) ?>" alt="<?= htmlspecialchars($service['service_title']) ?>">
                            <?php else: ?>
                                <i class="<?= htmlspecialchars($service['icon_class']) ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div class="student_affairs_service_content">
                            <h3 class="student_affairs_service_title"><?= htmlspecialchars($service['service_title']) ?></h3>
                            <p class="student_affairs_service_description"><?= $service['service_description'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-services-message">No services available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Fetch activities content
try {
    // Get section settings
    $stmt = $pdo->query("SELECT * FROM af_page_activities_settings WHERE id = 1");
    $activitiesSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get visible activities ordered by date
    $stmt = $pdo->query("SELECT * FROM af_page_activities WHERE is_visible = 1 ORDER BY event_date DESC");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching activities: " . $e->getMessage());
    $activitiesSettings = null;
    $activities = [];
}
?>

<!-- Activities Section -->
<section class="student_affairs_activities_section">
    <?php if ($activitiesSettings && $activitiesSettings['is_visible']): ?>
    <div class="student_affairs_activities_container">
        <h2 class="student_affairs_activities_title"><?= htmlspecialchars($activitiesSettings['section_title'] ?? 'ACTIVITIES CALENDAR') ?></h2>
        <div class="student_affairs_activities_divider"></div>
        
        <div class="student_affairs_activities_timeline">
            <?php if (!empty($activities)): ?>
                <?php foreach ($activities as $event): ?>
                    <?php
                    $eventDate = new DateTime($event['event_date']);
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    
                    $status = '';
                    $statusClass = '';
                    
                    if ($eventDate->format('Y-m-d') === $today->format('Y-m-d')) {
                        $status = 'Today';
                        $statusClass = 'today';
                    } elseif ($eventDate < $today) {
                        $status = 'Recent';
                        $statusClass = 'recent';
                    } else {
                        $status = 'Upcoming';
                        $statusClass = 'upcoming';
                    }
                    ?>
                    <div class="student_affairs_event" data-date="<?= $event['event_date'] ?>">
                        <div class="student_affairs_event_date">
                            <span class="student_affairs_event_month"><?= $eventDate->format('M') ?></span>
                            <span class="student_affairs_event_day"><?= $eventDate->format('d') ?></span>
                        </div>
                        <div class="student_affairs_event_content">
                            <?php if ($event['event_image']): ?>
                            <div class="student_affairs_event_image">
                                <img src="<?= str_starts_with($event['event_image'], 'http') ? $event['event_image'] : '../' . $event['event_image'] ?>" 
                                     alt="<?= htmlspecialchars($event['event_title']) ?>"
                                     onerror="this.src='../imgs/cte.jpg';">
                            </div>
                            <?php endif; ?>
                            <div class="student_affairs_event_details">
                                <div class="event_status_badge <?= $statusClass ?>"><?= $status ?></div>
                                <h3><?= htmlspecialchars($event['event_title']) ?></h3>
                                <div class="student_affairs_event_meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['event_location']) ?></span>
                                    <span><i class="fas fa-clock"></i> <?= htmlspecialchars($event['event_time']) ?></span>
                                </div>
                                <div class="student_affairs_event_description">
                                    <?= $event['event_description'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-events-message">
                    <p>No activities scheduled at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<section class="student_affairs_facilities_section">
    <div class="student_affairs_facilities_container">
        <div class="student_affairs_facilities_header">
            <h2 class="student_affairs_facilities_title">OUR FACILITIES</h2>
            <div class="student_affairs_facilities_divider"></div>
            <p class="student_affairs_facilities_description">Explore our comprehensive range of campus facilities designed to support your academic journey and personal growth.</p>
        </div>
    
        
        <div class="student_affairs_facilities_grid">
            <!-- Facility 1 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Student Lounge">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Student Lounge</h3>
                    <p class="facility_description">A comfortable space for students to relax, socialize, and collaborate between classes. Features comfortable seating, charging stations, and refreshment area.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 7:00 AM - 9:00 PM</li>
                            <li><span>Saturday:</span> 9:00 AM - 6:00 PM</li>
                            <li><span>Sunday:</span> Closed</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 2 -->
            <div class="facility_card" data-category="study">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Study Center">
                    <div class="facility_overlay">
                      
                        
                    </div>
                </div>
                <div class="facility_content">
                   
                    <h3 class="facility_title">Study Center</h3>
                    <p class="facility_description">Dedicated space for quiet study and academic focus. Includes individual study carrels, group study rooms, and reference materials.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Sunday:</span> 24 Hours</li>
                            <li><span>Holiday Hours:</span> 8:00 AM - 10:00 PM</li>
                        </ul>
                    </div>
                   
                </div>
            </div>
            
            <!-- Facility 3 -->
            <div class="facility_card" data-category="wellness">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Counseling Center">
                    <div class="facility_overlay">
                    
                       
                    </div>
                </div>
                <div class="facility_content">
                  
                    <h3 class="facility_title">Counseling Center</h3>
                    <p class="facility_description">Professional counseling services in a confidential, supportive environment. Offers individual counseling, group therapy, and wellness resources.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 5:00 PM</li>
                            <li><span>By Appointment:</span> Schedule Online</li>
                            <li><span>Crisis Line:</span> 24/7 Support</li>
                        </ul>
                    </div>
                  
                    
                </div>
            </div>
            
            <!-- Facility 4 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Student Organizations Hub">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Student Organizations Hub</h3>
                    <p class="facility_description">Central space for student organizations to meet, plan events, and collaborate. Includes meeting rooms, storage space, and event planning resources.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 7:00 PM</li>
                            <li><span>Saturday:</span> 9:00 AM - 5:00 PM</li>
                            <li><span>Sunday:</span> Closed</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 5 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Career Development Center">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Career Development Center</h3>
                    <p class="facility_description">Resources for career exploration, job searching, and professional development. Includes interview rooms, resume help desk, and career counseling offices.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 5:00 PM</li>
                            <li><span>Saturday:</span> By Appointment</li>
                            <li><span>Extended Hours:</span> During Career Fair Week</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 6 -->
            <div class="facility_card" data-category="wellness">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Health Services Clinic">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Health Services Clinic</h3>
                    <p class="facility_description">Provides basic healthcare services, medical consultations, and health education. Equipped with examination rooms, pharmacy, and treatment areas.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 7:00 AM - 6:00 PM</li>
                            <li><span>Saturday:</span> 8:00 AM - 12:00 PM</li>
                            <li><span>Emergency Services:</span> 24/7</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>

        
    </div>
</section>

<section class="student_affairs_clinic_info_section">
    <div class="student_affairs_clinic_info_container">
        <div class="student_affairs_clinic_info_header">
            <h2 class="student_affairs_clinic_info_title">Health Services Information</h2>
            <div class="student_affairs_clinic_info_divider"></div>
            <p class="student_affairs_clinic_info_description">Learn about our health services application process and access important forms and resources.</p>
        </div>
        
        <div class="student_affairs_clinic_info_content">
            <!-- Left Side: Application Process -->
            <div class="clinic_process_container">
                <h3 class="clinic_process_title">How to Apply for Our Services</h3>
                <div class="clinic_process_steps">
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Check Eligibility</h4>
                            <p>Ensure you meet the necessary requirements before applying for the service.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Complete the Application Form</h4>
                            <p>Fill out the required application form with accurate details.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Submit Required Documents</h4>
                            <p>Provide the necessary documents to verify your application.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Wait for Confirmation</h4>
                            <p>Our team will review your application and notify you of the result.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Downloadable Resources -->
            <div class="clinic_downloads_container">
                <h3 class="clinic_downloads_title">Downloadable Forms</h3>
                
                <div class="clinic_downloads_list">
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Application Form</h4>
                            <p>Download the main application form</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Requirements Checklist</h4>
                            <p>List of required documents</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Medical Certificate</h4>
                            <p>Medical clearance form</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Waiver Form</h4>
                            <p>Liability waiver document</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Insurance Form</h4>
                            <p>Health insurance information form</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



    <?php if ($isSectionVisible): ?>
    <section class="student_affairs_officer_section">
    <h2 class="student_affairs_officer_title"><?= htmlspecialchars($sectionTitle) ?></h2>
    <div class="student_affairs_officer_grid">
        <?php foreach ($officers as $officer): ?>
        <div class="student_affairs_officer_card">
            <div class="student_affairs_officer_image_wrapper">
                <img src="<?= htmlspecialchars($officer['image_url']) ?>" 
                     alt="<?= htmlspecialchars($officer['name']) ?>" 
                     class="student_affairs_officer_image">
            </div>
            <div class="student_affairs_officer_info">
                <h3 class="student_affairs_officer_name"><?= htmlspecialchars($officer['name']) ?></h3>
                <p class="student_affairs_officer_position"><?= htmlspecialchars($officer['position']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

    
<section class="student_affairs_logo_section">
    <div class="student_affairs_logo_container">
        <img src="../imgs/salogo1.png" alt="Health Services Logo" class="student_affairs_logo">
        <h2>Health Services</h2>
        <p>Western Mindanao State University</p>
    </div>
</section>

<section class="student_affairs_contact_section">
<?php
try {
    // Get section settings from the settings table
    $stmt = $pdo->query("SELECT section_title, section_visible FROM af_page_contact_settings WHERE id = 1");
    $sectionSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sectionSettings && $sectionSettings['section_visible']):
?>
    <div class="student_affairs_contact_container">
        <h2 class="student_affairs_contact_header"><?= htmlspecialchars($sectionSettings['section_title']) ?></h2>
        <div class="contact_grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM af_page_contact WHERE is_visible = 1 ORDER BY contact_type");
            while ($contact = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="contact_info">
                <div class="contact_item contact_<?= $contact['contact_type'] ?>">
                    <img src="<?= htmlspecialchars($contact['icon_path']) ?>" alt="<?= ucfirst($contact['contact_type']) ?> Icon" width="24" height="24">
                    <div class="contact_text">
                        <h3><?= htmlspecialchars($contact['label']) ?></h3>
                        <?php if ($contact['contact_type'] === 'facebook'): ?>
                            <a href="<?= htmlspecialchars($contact['value']) ?>" target="_blank" class="contact_link">
                                <?= htmlspecialchars($contact['display_text']) ?>
                            </a>
                        <?php else: ?>
                            <p class="contact_value"><?= htmlspecialchars($contact['value']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php 
    endif;
} catch (PDOException $e) {
    error_log("Error in contact section: " . $e->getMessage());
}
?>
</section>



    <script  src="../js/student_affairs.js"></script>
    <script>
       
    function expandSection(element, section) {
        const container = document.querySelector('.student_affair_mv_container');
        const boxes = document.querySelectorAll('.student_affair_mv_box_mission, .student_affair_mv_box_vision');
        
        boxes.forEach(box => {
            if (box === element) {
                box.classList.toggle('expanded');
                if (box.classList.contains('expanded')) {
                    container.classList.add('has-expanded');
                } else {
                    container.classList.remove('has-expanded');
                }
            } else {
                box.classList.remove('expanded');
            }
        });
    }
    function toggleServiceInfo(serviceId) {
    const box = document.querySelector(`#${serviceId}-info`).parentElement;
    const allBoxes = document.querySelectorAll('.student_affairs_services_box');

    // Close all other boxes before opening the current one
    allBoxes.forEach(otherBox => {
        if (otherBox !== box && otherBox.classList.contains('active')) {
            otherBox.classList.remove('active');
            otherBox.querySelector('.student_affairs_services_toggle').textContent = '+';
        }
    });

    // Toggle current box
    box.classList.toggle('active');
    const toggle = box.querySelector('.student_affairs_services_toggle');
    toggle.textContent = box.classList.contains('active') ? '×' : '+';
}

    </script>

    <!-- Add Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="../js/student_affairs.js"></script>
    <script>
    function expandSection(element, section) {
        const container = document.querySelector('.student_affair_mv_container');
        const boxes = document.querySelectorAll('.student_affair_mv_box_mission, .student_affair_mv_box_vision');
        
        boxes.forEach(box => {
            if (box === element) {
                box.classList.toggle('expanded');
                if (box.classList.contains('expanded')) {
                    container.classList.add('has-expanded');
                } else {
                    container.classList.remove('has-expanded');
                }
            } else {
                box.classList.remove('expanded');
            }
        });
    }
    
    function toggleServiceInfo(serviceId) {
        const box = document.querySelector(`#${serviceId}-info`).parentElement;
        const allBoxes = document.querySelectorAll('.student_affairs_services_box');

        // Close all other boxes before opening the current one
        allBoxes.forEach(otherBox => {
            if (otherBox !== box && otherBox.classList.contains('active')) {
                otherBox.classList.remove('active');
                otherBox.querySelector('.student_affairs_services_toggle').textContent = '+';
            }
        });

        // Toggle current box
        box.classList.toggle('active');
        const toggle = box.querySelector('.student_affairs_services_toggle');
        toggle.textContent = box.classList.contains('active') ? '×' : '+';
    }
    
    // Add animation for service cards
    document.addEventListener('DOMContentLoaded', function() {
        // Animate service cards
        const serviceCards = document.querySelectorAll('.student_affairs_service_card');
        
        function animateServiceCards() {
            serviceCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.classList.add('animated');
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Initial check for service cards
        animateServiceCards();
        
        // Combined scroll event listener
        window.addEventListener('scroll', function() {
            animateServiceCards();
        });
    });
    </script>

<script>
    // Add animation for service cards
    document.addEventListener('DOMContentLoaded', function() {
        // Animate service cards
        const serviceCards = document.querySelectorAll('.student_affairs_service_card');
        
        function animateServiceCards() {
            serviceCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.classList.add('animated');
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Animate timeline events
        const timelineEvents = document.querySelectorAll('.student_affairs_event');
        
        function animateTimelineEvents() {
            timelineEvents.forEach((event, index) => {
                const eventPosition = event.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(eventPosition < screenPosition) {
                    setTimeout(() => {
                        event.style.opacity = '1';
                        event.style.transform = 'translateY(0)';
                    }, index * 200); // Stagger animation
                }
            });
        }
        
        // Initial check for service cards and timeline events
        animateServiceCards();
        animateTimelineEvents();
        
        // Combined scroll event listener
        window.addEventListener('scroll', function() {
            animateServiceCards();
            animateTimelineEvents();
        });
    });

<script>
    // Background image slideshow
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.student_affairs_about_bg_image');
        let currentIndex = 0;
        
        function nextImage() {
            // Remove active class from current image
            images[currentIndex].classList.remove('active');
            
            // Move to next image
            currentIndex = (currentIndex + 1) % images.length;
            
            // Add active class to new current image
            images[currentIndex].classList.add('active');
        }
        
        // Change image every 5 seconds
        setInterval(nextImage, 5000);
        
        // Add animation for activity cards
        const activityCards = document.querySelectorAll('.student_affairs_activities_card');
        
        function animateActivityCards() {
            activityCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.style.opacity = "1";
                        card.style.transform = "translateY(0)";
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Set initial opacity and transform for cards
        activityCards.forEach(card => {
            card.style.opacity = "0";
            card.style.transform = "translateY(30px)";
            card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        });
        
        // Run animation check on load and scroll
        animateActivityCards();
        window.addEventListener('scroll', animateActivityCards);
    });
</script>

    <!-- Add Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
    // Animate masonry items on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const masonryItems = document.querySelectorAll('.student_affairs_masonry_item');
        
        // Add initial invisible class
        masonryItems.forEach(item => {
            item.style.opacity = "0";
            item.style.transform = "translateY(20px)";
            item.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        });
        
        function animateMasonryItems() {
            masonryItems.forEach((item, index) => {
                const itemTop = item.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (itemTop < windowHeight * 0.85) {
                    setTimeout(() => {
                        item.style.opacity = "1";
                        item.style.transform = "translateY(0)";
                    }, index * 150); // Stagger the animation
                }
            });
        }
        
        // Run once on load
        setTimeout(animateMasonryItems, 300);
        
        // Run on scroll
        window.addEventListener('scroll', animateMasonryItems);
    });
    </script>

<script>
    // Immediately set up and animate timeline events
    (function() {
        // Function to check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8
            );
        }
        
        // Animate events function
        function animateEvents() {
            const events = document.querySelectorAll('.student_affairs_event');
            
            events.forEach((event, index) => {
                if (isInViewport(event)) {
                    setTimeout(() => {
                        event.classList.add('visible');
                    }, index * 200);
                }
            });
        }
        
        // Categorize dates function
        function addStatusBadges() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const events = document.querySelectorAll('.student_affairs_event');
            
            events.forEach(event => {
                const dateString = event.getAttribute('data-date');
                if (!dateString) return;
                
                const itemDate = new Date(dateString);
                itemDate.setHours(0, 0, 0, 0);
                
                let status = '';
                let statusClass = '';
                
                if (itemDate.getTime() === today.getTime()) {
                    status = 'Today';
                    statusClass = 'today';
                } else if (itemDate < today) {
                    status = 'Recent';
                    statusClass = 'recent';
                } else {
                    status = 'Upcoming';
                    statusClass = 'upcoming';
                }
                
                // Only add badge if it doesn't exist
                if (!event.querySelector('.event_status_badge')) {
                    const badge = document.createElement('div');
                    badge.className = `event_status_badge ${statusClass}`;
                    badge.textContent = status;
                    
                    const detailsElement = event.querySelector('.student_affairs_event_details');
                    if (detailsElement) {
                        detailsElement.insertBefore(badge, detailsElement.firstChild);
                    }
                }
            });
        }
        
        // Add status badges on load
        document.addEventListener('DOMContentLoaded', function() {
            addStatusBadges();
            animateEvents();
            
            // Set up scroll listener
            window.addEventListener('scroll', animateEvents);
        });
        
        // Run immediately in case DOM is already loaded
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(function() {
                addStatusBadges();
                animateEvents();
            }, 1);
        }
    })();
</script>
